/* resources/js/menu_album.js */
(function(){
  const $  = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

  /* =================== Ajuste top por header =================== */
  const header = document.getElementById('header') || document.querySelector('.header');
  if (header) {
    const setTop = () => {
      const h = Math.max(56, Math.round(header.getBoundingClientRect().height));
      document.documentElement.style.setProperty('--header-h', h + 'px');
    };
    setTop();
    window.addEventListener('resize', setTop, {passive:true});
    if (window.ResizeObserver){ new ResizeObserver(setTop).observe(header); }
  }

  const owner = (document.querySelector('.ma-page')?.dataset.owner === '1');

  /* =================== Cola / Player =================== */
  const bc = (()=>{ try { return new BroadcastChannel('aura-player'); } catch { return null; } })();
  function addToQueue(list){
    if (window.AuraQueue?.addToEnd) return window.AuraQueue.addToEnd(list);
    try { bc && bc.postMessage({ type:'QUEUE_ADD', list }); } catch {}
  }
  function playSong(s){
    if (window.AuraQueue?.externalPlay) return window.AuraQueue.externalPlay(s);
    if (window.AuraPlayer?.play) return window.AuraPlayer.play({ id:s.id, src:s.audio, title:s.title, artist:s.artist, cover:s.cover });
    try { bc && bc.postMessage({ type:'PLAY_REQUEST', song:s }); } catch {}
  }

  /* =================== Dataset canciones =================== */
  const dsEl = $('#albumSongsData');
  const songsData = dsEl ? (JSON.parse(dsEl.dataset.songs || '[]')) : [];

  /* =================== Acciones generales izquierda =================== */
  $('#btnAddQueue')?.addEventListener('click', (e)=>{ e.preventDefault(); if (songsData.length) addToQueue(songsData); });
  $('#btnLikeAll')?.addEventListener('click', likeAllSongs);

  async function likeAllSongs(e){
    e?.preventDefault?.();
    const tbl = $('#songsTable'); if (!tbl) return;
    const toggleTpl = tbl.dataset.likeToggle || '/api/canciones/0/like/toggle';
    for (const row of $$('.row', tbl)) {
      const id = row.dataset.id;
      const url = toggleTpl.replace(/0(?!.*0)/, id);
      try {
        const fd = new FormData(); fd.append('_token', CSRF);
        const res = await fetch(url, { method:'POST', body: fd, credentials:'same-origin',
          headers:{ 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' } });
        if (res.status === 401) { alert('Inicia sesión para dar Me gusta.'); break; }
      } catch {}
    }
  }

  /* =================== CHANGE TRACKER =================== */
  const Change = {
    albumTitle: null,
    albumCoverFile: null,
    songTitles: new Map(),      // id -> {old, next}
    coverPropagate: false,
    get count(){
      return (this.albumTitle?1:0) + (this.albumCoverFile?1:0) + this.songTitles.size;
    },
    list(){
      const items = [];
      if (this.albumTitle) items.push(`Álbum: “${this.albumTitle.old}” → “${this.albumTitle.next}”`);
      if (this.albumCoverFile) items.push(`Nueva portada de álbum (se aplicará a todas las canciones)`);
      this.songTitles.forEach((v, id)=> items.push(`Canción #${id}: “${v.old}” → “${v.next}”`));
      return items;
    },
    clear(){ this.albumTitle=null; this.albumCoverFile=null; this.songTitles.clear(); this.coverPropagate=false; }
  };

  /* ====== Modal guard ====== */
  let pendingHref = null;
  const modal = $('#changesModal');
  const listEl = $('#changesList');
  const btnSaveAndLeave = $('#btnSaveAndLeave');
  const btnDiscard = $('#btnDiscard');

  function showModal(){
    listEl.innerHTML = Change.list().map(t=>`<li>${escapeHtml(t)}</li>`).join('') || '<li>No hay cambios.</li>';
    modal.classList.add('show');
  }
  function hideModal(){ modal.classList.remove('show'); }
  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  // interceptar enlaces que cambian de sección
  document.addEventListener('click', (e)=>{
    const a = e.target.closest('a.guard-link');
    if (!a) return;
    if (Change.count === 0) return; // no hay cambios
    e.preventDefault();
    pendingHref = a.href;
    showModal();
  });

  // beforeunload (cerrar pestaña/recargar)
  window.addEventListener('beforeunload', (e)=>{
    if (Change.count > 0) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  btnDiscard?.addEventListener('click', ()=>{
    pendingHref = null;
    hideModal();
  });

  btnSaveAndLeave?.addEventListener('click', async ()=>{
    btnSaveAndLeave.disabled = true;
    const ok = await saveAllChanges();
    btnSaveAndLeave.disabled = false;
    if (ok){
      Change.clear();
      hideModal();
      if (pendingHref) location.href = pendingHref;
    }
  });

  /* =================== Ediciones de álbum (solo dueño) =================== */
  if (owner){
    const panel = $('#albumPanel');
    if (panel){
      const updateUrl = panel.dataset.updateUrl || '';
      const titleInline = $('#albumTitleInline');
      const titleInput = $('#albumTitleInput');
      const titleState = $('#albumTitleState');
      const coverInput = $('#albumCoverInput');
      const coverImg = $('#albumCoverImg');

      // marcar cambio de título con estilo
      titleInput?.addEventListener('input', ()=>{
        const old = titleInput.dataset.original || titleInput.getAttribute('value') || '';
        const next = titleInput.value.trim();
        titleInline?.classList.toggle('is-dirty', next !== old);
        if (next !== old) Change.albumTitle = { old, next };
        else Change.albumTitle = null;
      });

      $('#btnSaveTitle')?.addEventListener('click', async ()=>{
        if (!Change.albumTitle) return;
        titleInline?.classList.add('saving');
        titleState.innerHTML = '<span class="spin" aria-hidden="true"></span>';
        const ok = await updateAlbum({ url:updateUrl, title:Change.albumTitle.next });
        if (ok){
          titleInput.setAttribute('value', Change.albumTitle.next);
          titleInput.dataset.original = Change.albumTitle.next;
          Change.albumTitle = null;
          titleInline?.classList.remove('is-dirty','saving');
          titleInline?.classList.add('saved');
          titleState.innerHTML = '<i class="fa-solid fa-circle-check ok" title="Guardado"></i>';
          setTimeout(()=>{ titleInline?.classList.remove('saved'); titleState.innerHTML=''; }, 1200);
        }else{
          titleInline?.classList.remove('saving');
          titleState.innerHTML = '<i class="fa-solid fa-triangle-exclamation err" title="Error"></i>';
          setTimeout(()=>{ titleState.innerHTML=''; }, 1600);
        }
      });

      $('#btnChangeCover')?.addEventListener('click', ()=> coverInput?.click());
      coverInput?.addEventListener('change', ()=>{
        const file = coverInput.files?.[0]; if (!file) return;
        Change.albumCoverFile = file;
        Change.coverPropagate = true;
        // Vista previa
        const blob = URL.createObjectURL(file);
        coverImg && (coverImg.src = blob);
        // Previsualiza en cada fila
        $$('.row').forEach(row=>{
          const img = $('.thumb', row);
          if (img) img.src = blob;
        });
      });
    }
  }

  /* =================== Tabla de canciones =================== */
  const tbl = $('#songsTable');
  if (tbl){
    const likeToggleTpl = tbl.dataset.likeToggle || '/api/canciones/0/like/toggle';
    const deleteTpl     = tbl.dataset.songDelete || '/cancion/0';
    const updateTpl     = tbl.dataset.songUpdate || '/canciones/0';

    function rowToSong(row){
      return {
        id: Number(row.dataset.id),
        title: row.dataset.title,
        artist: row.dataset.artist,
        cover: row.dataset.cover,
        audio: row.dataset.audio,
        duration: Number(row.dataset.duration)||0
      };
    }

    // bind de filas
    $$('.row', tbl).forEach(row=>{
      const id = Number(row.dataset.id);
      const input = $('.song-title', row);
      const inline = $('.inline-edit', row);
      const state = $('.song-state', row);
      const saveBtn = $('.save-song', row);

      $('.play-mini', row)?.addEventListener('click', (e)=>{ e.preventDefault(); e.stopPropagation(); playSong(rowToSong(row)); });
      $('.add-queue', row)?.addEventListener('click', (e)=>{ e.preventDefault(); e.stopPropagation(); addToQueue([rowToSong(row)]); });

      // like individual
      $('.like-song', row)?.addEventListener('click', async (e)=>{
        e.preventDefault(); e.stopPropagation();
        const url = likeToggleTpl.replace(/0(?!.*0)/, id);
        try{
          const fd = new FormData(); fd.append('_token', CSRF);
          const res = await fetch(url, { method:'POST', body: fd, credentials:'same-origin',
            headers:{ 'X-Requested-With':'XMLHttpRequest','Accept':'application/json' }});
          if (res.status === 401) return alert('Inicia sesión para dar Me gusta.');
          const i = $('.like-song i', row); i?.classList.toggle('fa-solid'); i?.classList.toggle('fa-regular');
        }catch{}
      });

      // detectar cambio de nombre (estilo + tracker)
      if (owner){
        input?.addEventListener('input', ()=>{
          const old = (input.dataset.original || row.dataset.title || '').trim();
          const next = input.value.trim();
          const changed = (next !== old);
          inline?.classList.toggle('is-dirty', changed);
          if (changed) Change.songTitles.set(id, {old, next});
          else Change.songTitles.delete(id);
        });

        // guardar esta canción con animación
        saveBtn?.addEventListener('click', async (e)=>{
          e.preventDefault(); e.stopPropagation();
          if (!Change.songTitles.has(id)) return;
          const change = Change.songTitles.get(id);

          row.classList.remove('error','saved');
          row.classList.add('saving');
          saveBtn.disabled = true;
          const oldIcon = saveBtn.innerHTML;
          saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

          const ok = await updateSong({ url: updateTpl.replace(/0(?!.*0)/, id), title: change.next });
          row.classList.remove('saving');
          if (ok){
            row.classList.add('saved');
            state.innerHTML = '<i class="fa-solid fa-circle-check ok" title="Guardado"></i>';
            row.dataset.title = change.next;
            input.dataset.original = change.next;
            inline?.classList.remove('is-dirty');
            setTimeout(()=>{ row.classList.remove('saved'); state.innerHTML=''; }, 1200);
          }else{
            row.classList.add('error');
            state.innerHTML = '<i class="fa-solid fa-triangle-exclamation err" title="Error"></i>';
            setTimeout(()=>{ row.classList.remove('error'); state.innerHTML=''; }, 1400);
          }
          saveBtn.disabled = false;
          saveBtn.innerHTML = oldIcon;
          if (ok) Change.songTitles.delete(id);
        });

        // eliminar canción
        $('.delete-song', row)?.addEventListener('click', async (e)=>{
          e.preventDefault(); e.stopPropagation();
          if (!confirm('¿Eliminar esta canción?')) return;
          const url = deleteTpl.replace(/0(?!.*0)/, id);
          try{
            const fd = new FormData(); fd.append('_token', CSRF); fd.append('_method', 'DELETE');
            const res = await fetch(url, { method:'POST', body: fd, credentials:'same-origin' });
            if (!res.ok) throw new Error('HTTP '+res.status);
            row.remove();
            Change.songTitles.delete(id);
          }catch{
            row.classList.add('error');
            setTimeout(()=>row.classList.remove('error'), 1200);
            alert('No se pudo eliminar la canción.');
          }
        });
      }
    });
  }

  /* =================== Guardar TODO (para modal) =================== */
  async function saveAllChanges(){
    try{
      const panel = $('#albumPanel');
      const albumUrl = panel?.dataset.updateUrl || '';

      // 1) Guardar título del álbum
      if (Change.albumTitle){
        setAlbumState('saving');
        const ok = await updateAlbum({ url: albumUrl, title: Change.albumTitle.next });
        setAlbumState(ok ? 'saved' : 'idle', ok ? 'ok' : 'err');
        if (!ok) throw new Error('No se pudo guardar el título del álbum.');
        $('#albumTitleInput')?.setAttribute('value', Change.albumTitle.next);
        $('#albumTitleInput')?.setAttribute('data-original', Change.albumTitle.next);
        $('#albumTitleInline')?.classList.remove('is-dirty');
        Change.albumTitle = null;
      }

      // 2) Subir nueva portada del álbum
      let albumCoverPath = null;
      if (Change.albumCoverFile){
        setAlbumState('saving');
        const res = await updateAlbum({ url: albumUrl, coverFile: Change.albumCoverFile });
        albumCoverPath = res.cover_path || null;
        setAlbumState(res.ok ? 'saved' : 'idle', res.ok ? 'ok' : 'err');
        if (!res.ok) throw new Error('No se pudo actualizar la portada del álbum.');
      }

      // 3) Guardar títulos de canciones con animación
      const tbl = $('#songsTable');
      const updateTpl = tbl?.dataset.songUpdate || '/canciones/0';
      for (const [id, change] of Change.songTitles.entries()){
        const row = $(`.row[data-id="${CSS.escape(String(id))}"]`);
        const inline = $('.inline-edit', row);
        const state = $('.song-state', row);
        const saveBtn = $('.save-song', row);

        row?.classList.remove('error','saved');
        row?.classList.add('saving');
        if (saveBtn){ saveBtn.disabled = true; saveBtn.dataset._icon = saveBtn.innerHTML; saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>'; }

        const ok = await updateSong({ url: updateTpl.replace(/0(?!.*0)/, id), title: change.next });

        row?.classList.remove('saving');
        if (ok){
          row?.classList.add('saved');
          state && (state.innerHTML = '<i class="fa-solid fa-circle-check ok" title="Guardado"></i>');
          row.dataset.title = change.next;
          const input = $('.song-title', row);
          if (input){ input.dataset.original = change.next; }
          inline?.classList.remove('is-dirty');
          setTimeout(()=>{ row?.classList.remove('saved'); if (state) state.innerHTML=''; }, 1200);
        }else{
          row?.classList.add('error');
          state && (state.innerHTML = '<i class="fa-solid fa-triangle-exclamation err" title="Error"></i>');
          setTimeout(()=>{ row?.classList.remove('error'); if (state) state.innerHTML=''; }, 1400);
          throw new Error('No se pudo guardar el título de la canción #'+id);
        }

        if (saveBtn){ saveBtn.disabled = false; saveBtn.innerHTML = saveBtn.dataset._icon || '<i class="fa-solid fa-floppy-disk"></i>'; }
      }

      // 4) Propagar portada a canciones si corresponde
      if (Change.coverPropagate && (Change.albumCoverFile || albumCoverPath)){
        const coverFile = Change.albumCoverFile || null;
        const coverPath = albumCoverPath || null;
        const rows = $$('.row');
        const tbl2 = $('#songsTable');
        const updateTpl2 = tbl2?.dataset.songUpdate || '/canciones/0';

        for (const row of rows){
          const id = row.dataset.id;
          // animación de "saving"
          row.classList.remove('error','saved'); row.classList.add('saving');

          let ok = await updateSong({ url: updateTpl2.replace(/0(?!.*0)/, id), coverFromAlbum: true, coverPath });
          if (!ok && coverFile){
            ok = await updateSong({ url: updateTpl2.replace(/0(?!.*0)/, id), coverFile });
          }

          row.classList.remove('saving');
          if (ok){
            row.classList.add('saved');
            setTimeout(()=> row.classList.remove('saved'), 900);
          }else{
            row.classList.add('error');
            setTimeout(()=> row.classList.remove('error'), 1200);
            throw new Error('No se pudo propagar portada a la canción #'+id);
          }
        }
      }

      return true;
    }catch(err){
      alert(err.message || 'No se pudieron guardar los cambios.');
      return false;
    }
  }

  function setAlbumState(state, icon){
    const inline = $('#albumTitleInline');
    const stateEl = $('#albumTitleState');
    inline?.classList.remove('saved','saving');
    if (state==='saving') inline?.classList.add('saving');
    if (state==='saved') inline?.classList.add('saved');
    if (stateEl){
      if (icon==='ok') stateEl.innerHTML = '<i class="fa-solid fa-circle-check ok" title="Guardado"></i>';
      else if (icon==='err') stateEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation err" title="Error"></i>';
      else if (state==='saving') stateEl.innerHTML = '<span class="spin" aria-hidden="true"></span>';
      else stateEl.innerHTML = '';
    }
    if (state==='saved') setTimeout(()=>{ inline?.classList.remove('saved'); setAlbumState('idle'); }, 1200);
  }

  /* =================== Helpers de requests =================== */
  async function updateAlbum({ url, title=null, coverFile=null }){
    if (!url || /\/0$/.test(url)) return { ok:false };
    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('_method', 'PATCH');
    if (title!==null) fd.append('title', title);
    if (coverFile)    fd.append('cover', coverFile);

    const res = await fetch(url, { method:'POST', body: fd, credentials:'same-origin',
      headers:{ 'X-Requested-With':'XMLHttpRequest','Accept':'application/json' }});
    let j=null; try{ j = await res.clone().json(); }catch{}
    return { ok: res.ok, cover_path: j?.cover_path || j?.data?.cover_path || null };
  }

  async function updateSong({ url, title=null, coverFile=null, coverFromAlbum=false, coverPath=null }){
    if (!url || /\/0$/.test(url)) return false;
    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('_method', 'PATCH');
    if (title!==null) fd.append('title', title);
    if (coverFile)    fd.append('cover', coverFile);
    if (coverFromAlbum) fd.append('cover_from_album', '1');
    if (coverPath) fd.append('cover_path', coverPath);

    const res = await fetch(url, { method:'POST', body: fd, credentials:'same-origin',
      headers:{ 'X-Requested-With':'XMLHttpRequest','Accept':'application/json' }});
    return res.ok;
  }
})();
