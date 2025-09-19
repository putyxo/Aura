export default function initMenuAlbum() {
console.log("Inicializando scripts de Menu Album...");
const $  = (s, r=document) => r.querySelector(s);
const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  const main    = $('.ma-page');
  const isOwner = (main?.dataset.owner === '1');

  /* ========== PLAY / QUEUE ========== */
  function addToQueue(items){
    if (window.AuraQueue?.addMany) { window.AuraQueue.addMany(items); return; }
    window.dispatchEvent(new CustomEvent('aura:addToQueue', { detail:{ songs: items }}));
  }
  function playOne(meta){
    if (!meta?.src) return;
    if (window.AuraQueue?.externalPlay) { window.AuraQueue.externalPlay(meta); return; }
    if (window.AuraPlayer?.play) { window.AuraPlayer.play(meta); return; }
    const a = new Audio(meta.src); a.play().catch(()=>{});
  }

  // click en botón mini-play
  document.addEventListener('click', e => {
    const playBtn = e.target.closest('.ma-play-mini');
    if (!playBtn) return;
    const row = playBtn.closest('tr.ma-row'); if (!row) return;
    playOne({
      id: row.dataset.id, src: row.dataset.audio,
      title: row.dataset.title, artist: row.dataset.artist, cover: row.dataset.cover
    });
  });

  // Añadir álbum entero a la cola
  $('#btnAddQueue')?.addEventListener('click', () => {
    const songs = $$('#songsTable tbody tr.ma-row').map(r => ({
      id: r.dataset.id, src: r.dataset.audio, title: r.dataset.title,
      artist: r.dataset.artist, cover: r.dataset.cover
    }));
    addToQueue(songs);
  });

  // Añadir una fila a la cola
  document.addEventListener('click', e => {
    const addBtn = e.target.closest('.add-queue');
    if (!addBtn) return;
    const row = addBtn.closest('tr.ma-row'); if (!row) return;
    addToQueue([{ id: row.dataset.id, src: row.dataset.audio, title: row.dataset.title, artist: row.dataset.artist, cover: row.dataset.cover }]);
  });

  /* ========== Fila completa clicable (como en ed_perfil) ========== */
  function shouldIgnoreClick(target){
    return !!target.closest('.ma-col-act, .ma-inline-edit, form, button, a, input, textarea');
  }
  document.addEventListener('click', (e) => {
    const row = e.target.closest('#songsTable tr.ma-row');
    if (!row) return;
    if (shouldIgnoreClick(e.target)) return;
    playOne({
      id: row.dataset.id,
      src: row.dataset.audio,
      title: row.dataset.title,
      artist: row.dataset.artist,
      cover: row.dataset.cover
    });
  });
  // accesible con teclado
  document.addEventListener('keydown', (e) => {
    const row = e.target.closest('#songsTable tr.ma-row');
    if (!row) return;
    if (e.key === 'Enter' || e.key === ' ') {
      if (shouldIgnoreClick(e.target)) return;
      e.preventDefault();
      playOne({
        id: row.dataset.id,
        src: row.dataset.audio,
        title: row.dataset.title,
        artist: row.dataset.artist,
        cover: row.dataset.cover
      });
    }
  });

  /* ========== LIKE por canción (UI optimista) ========== */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.like-song');
    if (!btn) return;
    const form = btn.closest('form.ma-inline-like');
    if (!form) return; // sin auth o deshabilitado
    const row = btn.closest('tr.ma-row');
    const icon = btn.querySelector('i');
    const wasLiked = btn.classList.contains('is-liked');

    // Optimismo
    btn.classList.toggle('is-liked', !wasLiked);
    btn.setAttribute('aria-pressed', (!wasLiked).toString());
    icon.classList.toggle('fa-regular', wasLiked);
    icon.classList.toggle('fa-solid', !wasLiked);
    row && (row.dataset.liked = (!wasLiked ? '1' : '0'));

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json().catch(()=>({}));
      const finalLiked = (typeof data.liked === 'boolean') ? data.liked : !wasLiked;
      btn.classList.toggle('is-liked', finalLiked);
      btn.setAttribute('aria-pressed', String(finalLiked));
      icon.classList.toggle('fa-regular', !finalLiked);
      icon.classList.toggle('fa-solid', finalLiked);
      row && (row.dataset.liked = (finalLiked ? '1' : '0'));
    } catch (err) {
      // revertir
      btn.classList.toggle('is-liked', wasLiked);
      btn.setAttribute('aria-pressed', String(wasLiked));
      icon.classList.toggle('fa-regular', !wasLiked);
      icon.classList.toggle('fa-solid', wasLiked);
      row && (row.dataset.liked = (wasLiked ? '1' : '0'));
      console.error('Like error:', err);
    }

    updateLikeAllBtn();
  });

  /* ========== LIKE A TODAS ========== */
  const likeAllBtn = $('#btnLikeAll');
  function updateLikeAllBtn(){
    if (!likeAllBtn) return;
    const rows = $$('#songsTable tbody tr.ma-row');
    const liked = rows.filter(r => r.dataset.liked === '1').length;
    const all = rows.length && liked === rows.length;
    likeAllBtn.dataset.mode = all ? 'unlike' : 'like';
    likeAllBtn.innerHTML = all
      ? '<i class="fa-solid fa-heart"></i> Quitar like a todas'
      : '<i class="fa-regular fa-heart"></i> Dar like a todas';
  }
  likeAllBtn && likeAllBtn.addEventListener('click', async () => {
    const rows = $$('#songsTable tbody tr.ma-row');
    const targetLike = (likeAllBtn.dataset.mode !== 'unlike'); // true = queremos dejar todo en like
    const jobs = [];

    rows.forEach(row => {
      const form = row.querySelector('form.ma-inline-like');
      if (!form) return; // no auth o no disponible
      const btn = form.querySelector('.like-song');
      const icon = btn.querySelector('i');
      const curr = row.dataset.liked === '1';
      if (curr === targetLike) return; // ya está como queremos

      // Optimismo
      btn.classList.toggle('is-liked', targetLike);
      btn.setAttribute('aria-pressed', String(targetLike));
      icon.classList.toggle('fa-regular', !targetLike);
      icon.classList.toggle('fa-solid', targetLike);
      row.dataset.liked = targetLike ? '1' : '0';

      jobs.push(fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      }).then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(data => {
          const final = (typeof data.liked === 'boolean') ? data.liked : targetLike;
          btn.classList.toggle('is-liked', final);
          btn.setAttribute('aria-pressed', String(final));
          icon.classList.toggle('fa-regular', !final);
          icon.classList.toggle('fa-solid', final);
          row.dataset.liked = final ? '1' : '0';
        }).catch(()=>{ /* dejamos estado optimista */ }));
    });

    await Promise.allSettled(jobs);
    updateLikeAllBtn();
  });
  updateLikeAllBtn();

  /* ========== EDITAR TÍTULO / PORTADA (sólo dueño) ========== */
  if (isOwner) {
    const albumTitleInput = $('#albumTitleInput');
    const btnSaveTitle    = $('#btnSaveTitle');
    const albumTitleState = $('#albumTitleState');
    const albumPanel      = $('#albumPanel');
    const updateUrl       = albumPanel?.dataset.updateUrl;

    // Guardar título álbum
    btnSaveTitle?.addEventListener('click', async () => {
      if (!albumTitleInput || !updateUrl) return;
      const val = albumTitleInput.value.trim();
      if (!val) return;
      albumPanel?.classList.add('saving');
      albumTitleState.innerHTML = '<span class="spin" aria-hidden="true"></span>';

      try {
        const res = await fetch(updateUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ title: val, titulo: val, _method: 'PUT' }),
          credentials: 'same-origin'
        });
        if (!res.ok) throw new Error('HTTP '+res.status);
        albumTitleState.innerHTML = '<i class="ok fa-solid fa-circle-check"></i>';
      } catch (e) {
        albumTitleState.innerHTML = '<i class="err fa-solid fa-circle-xmark"></i>';
      } finally {
        albumPanel?.classList.remove('saving');
        setTimeout(()=> albumTitleState.innerHTML='', 900);
      }
    });

    // Cambiar portada: previsualiza y actualiza thumbs
    const coverInput = $('#albumCoverInput');
    const coverImg   = $('#albumCoverImg');
    coverInput?.addEventListener('change', () => {
      const f = coverInput.files?.[0];
      if (!f) return;
      const blobUrl = URL.createObjectURL(f);
      coverImg.src = blobUrl;
      // Actualiza thumbs de todas las canciones al instante
      $$('.ma-thumb').forEach(img => img.src = blobUrl);
    });
  }

  /* ========== GUARDAR TÍTULO DE CANCIÓN (sólo dueño) ========== */
  if (isOwner) {
    document.addEventListener('click', async (e) => {
      const saveBtn = e.target.closest('.ma-save-song');
      if (!saveBtn) return;
      const row = saveBtn.closest('tr.ma-row'); if (!row) return;
      const input = row.querySelector('.ma-song-title');
      if (!input) return;
      const newTitle = input.value.trim();
      if (!newTitle) return;

      const tbl = $('#songsTable');
      const updateBase = tbl?.dataset.songUpdate || '';
      const updateUrl  = updateBase.replace(/0(?!.*0)/, row.dataset.id); // cambia el último 0 por el id

      row.classList.add('saving');
      const state = row.querySelector('.ma-song-state');
      if (state) state.innerHTML = '<span class="spin" aria-hidden="true"></span>';

      try {
        const res = await fetch(updateUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ title: newTitle, titulo: newTitle, _method:'PUT' }),
          credentials: 'same-origin'
        });
        if (!res.ok) throw new Error('HTTP '+res.status);
        if (state) state.innerHTML = '<i class="ok fa-solid fa-circle-check"></i>';
        row.dataset.title = newTitle;
      } catch (e2) {
        if (state) state.innerHTML = '<i class="err fa-solid fa-circle-xmark"></i>';
        row.classList.add('error');
        setTimeout(()=>row.classList.remove('error'), 800);
      } finally {
        row.classList.remove('saving');
        setTimeout(()=> state && (state.innerHTML=''), 900);
      }
    });
  }
 }