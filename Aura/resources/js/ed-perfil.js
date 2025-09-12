(() => {
  const rootSel = '#page-profile';
  const ri = window.requestIdleCallback || (cb => setTimeout(cb, 1));
  const $ = (sel, sc=document) => sc.querySelector(sel);
  const $$ = (sel, sc=document) => Array.from(sc.querySelectorAll(sel));

  const getCSRF = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  // === NUEVO: sincroniza altura de canciones con el 2×2 de álbumes ===
  function syncSongsHeight() {
    const songsBox = $('#songsList');
    const albumsViewport = $('#albumsViewport');
    if (!songsBox || !albumsViewport) return;

    const h = Math.max(0, Math.round(albumsViewport.getBoundingClientRect().height));
    if (h > 0) {
      const row = Math.floor(h / 6); // 6 filas
      document.documentElement.style.setProperty('--aurp-songs-box-h', `${h}px`);
      document.documentElement.style.setProperty('--aurp-song-row-h', `${row}px`);
    }
  }
  let syncTO = null;
  const syncLater = () => { clearTimeout(syncTO); syncTO = setTimeout(syncSongsHeight, 60); };

  function initProfile() {
    const root = document.querySelector(rootSel);
    if (!root) return;
    document.body.classList.remove('blurred','modal-open');

    // Hover anim en filas (conservado + suave)
    $$('.song-row', root).forEach(row => {
      row.addEventListener('mouseenter', function(){
        this.style.transform = 'translateY(-8px) scale(1.02)';
        this.style.boxShadow = '0 12px 24px rgba(0,0,0,.15)';
        this.style.zIndex = '10';
        this.style.transition = 'all .3s cubic-bezier(.4,0,.2,1)';
      });
      row.addEventListener('mouseleave', function(){
        this.style.transform = 'translateY(0) scale(1)';
        this.style.boxShadow = 'none';
        this.style.zIndex = '1';
      });
    });

    // Banner progressive
    const b = root.querySelector('.profile-banner');
    if (b?.dataset.hires){
      const hi = new Image();
      hi.src = b.dataset.hires; hi.decoding = 'async';
      hi.onload = () => { b.style.backgroundImage = `url('${b.dataset.hires}')`; b.classList.add('loaded'); };
    }

    // Reproducir al click en fila (evitando botones)
    $$('.song-row', root).forEach(row => {
      const play = () => row.querySelector('.cancion-item')?.click();
      row.addEventListener('click', (e) => {
        if (e.target.closest('.icon-chip') || e.target.closest('.kebab-menu')) return;
        play();
      }, { passive:true });
    });

    // Menú 3 puntos
    $$('.more-btn', root).forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const wrap = btn.closest('.menu-wrap');
        const menu = wrap.querySelector('.kebab-menu');
        menu.classList.toggle('open');
        btn.setAttribute('aria-expanded', menu.classList.contains('open'));
      });
    });
    document.addEventListener('click', () => {
      $$('.kebab-menu.open', root).forEach(m => m.classList.remove('open'));
    }, { passive:true });

    // === Carrusel Álbumes ===
    (function albums(){
      const track = $('#albumsTrack');
      const viewport = $('#albumsViewport');
      const prev = $('#albumsPrev');
      const next = $('#albumsNext');
      const label = $('#albumsPageLabel');
      if(!track || !viewport) return;
      let page = 0, pages = parseInt(track.dataset.pages || '0', 10);

      const update = () => {
        track.style.transform = `translateX(-${page * 100}%)`;
        if (prev) prev.disabled = (page === 0);
        if (next) next.disabled = (page >= pages - 1);
        if (label) label.textContent = pages ? `Página ${page+1} de ${pages}` : '';
        syncLater();
      };
      prev?.addEventListener('click', () => { if (page>0){ page--; update(); }});
      next?.addEventListener('click', () => { if (page<pages-1){ page++; update(); }});
      update();

      // Recalcular cuando cargan imágenes
      $$('img', viewport).forEach(img => img.addEventListener('load', syncLater, {once:true}));

      if (window.ResizeObserver){
        const ro = new ResizeObserver(syncLater);
        ro.observe(viewport);
      }
    })();

    // === Carrusel Últimos lanzamientos ===
    (function releases(){
      const track = $('#releasesTrack');
      if(!track) return;
      const prev = $('#releasesPrev');
      const next = $('#releasesNext');
      const pager = $('#releasesPager');
      let page = 0, pages = parseInt(track.dataset.pages || '0', 10);

      // Puntos
      pager.innerHTML = '';
      for (let i=0;i<pages;i++){
        const d = document.createElement('div');
        d.className = 'dot' + (i===0 ? ' active':'' );
        d.role = 'button'; d.tabIndex = 0; d.ariaLabel = `Ir a página ${i+1}`;
        d.addEventListener('click', ()=>{ page = i; update(); });
        pager.appendChild(d);
      }

      function update(){
        track.style.transform = `translateX(-${page * 100}%)`;
        if (prev) prev.disabled = (page === 0);
        if (next) next.disabled = (page >= pages - 1);
        [...pager.children].forEach((el,idx)=> el.classList.toggle('active', idx===page));
      }

      prev?.addEventListener('click', ()=>{ if (page>0) { page--; update(); }});
      next?.addEventListener('click', ()=>{ if (page<pages-1) { page++; update(); }});
      update();
    })();

    // === Editar / tabs / previews ===
    (function modals(){
      const editModal = $('#editModal');
      if(!editModal) return;
      const openEdit  = $('#editBtn');
      const closeEdit1= $('#closeEdit');
      const closeEdit2= $('#closeEditTop');
      const close = ()=> editModal?.setAttribute('aria-hidden','true');

      const openModal = (tab='basic') => {
        editModal?.setAttribute('aria-hidden','false');
        $$('.tab-btn', editModal).forEach(b=> b.classList.toggle('active', b.dataset.tab===tab));
        $$('.tab-pane', editModal).forEach(p=> p.classList.toggle('hidden', p.dataset.pane!==tab));
      };
      openEdit?.addEventListener('click', ()=> openModal('basic'));
      closeEdit1?.addEventListener('click', close);
      closeEdit2?.addEventListener('click', close);
      editModal?.addEventListener('click', (e)=>{ if(e.target===editModal) close(); });

      // Tabs
      $$('.tab-btn', editModal).forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const tab = btn.dataset.tab;
          $$('.tab-btn', editModal).forEach(b=> b.classList.toggle('active', b===btn));
          $$('.tab-pane', editModal).forEach(p=> p.classList.toggle('hidden', p.dataset.pane!==tab));
        });
      });

      // Previews
      const avatarInput = $('#avatarInput');
      const avatarPrev  = $('#avatarPreview');
      const avatarLive  = $('#avatarPreviewLive');
      const bannerInput = $('#bannerInput');
      const bannerPrev  = $('#bannerPreview');

      $('.avatar-edit')?.addEventListener('click', ()=> avatarInput?.click());
      $('.banner-edit')?.addEventListener('click', ()=> bannerInput?.click());

      avatarInput?.addEventListener('change', ()=>{
        const f = avatarInput.files?.[0]; if(!f) return;
        const url = URL.createObjectURL(f);
        if (avatarPrev) { avatarPrev.src = url; avatarPrev.style.display='block'; }
        if (avatarLive) { avatarLive.src = url; }
      });
      bannerInput?.addEventListener('change', ()=>{
        const f = bannerInput.files?.[0]; if(!f) return;
        const url = URL.createObjectURL(f);
        const banner = $('.profile-banner');
        if (bannerPrev) { bannerPrev.src = url; bannerPrev.style.display='block'; }
        if (banner)     { banner.style.backgroundImage = `url('${url}')`; }
      });
    })();

    // === Confirmar eliminar ===
    (function confirmDelete(){
      const cModal = $('#confirmModal'); if(!cModal) return;
      const cCover = $('#confirmCover');
      const cTitle = $('#confirmTitle');
      const cSub   = $('#confirmSubtitle');
      const dForm  = $('#deleteForm');
      const dangerBtn = $('#confirmDeleteBtn');
      const cancelBtn = $('#cancelDelete');
      let lastFocused = null;

      const focusableSel = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
      function trapFocus(container, e){
        const f = [...container.querySelectorAll(focusableSel)].filter(el=>!el.disabled && el.offsetParent !== null);
        if (!f.length) return; const first = f[0], last = f[f.length - 1];
        if (e.key === 'Tab'){
          if (e.shiftKey && document.activeElement === first){ last.focus(); e.preventDefault(); }
          else if (!e.shiftKey && document.activeElement === last){ first.focus(); e.preventDefault(); }
        }
      }
      function openConfirm(type, action, title, cover){
        lastFocused = document.activeElement;
        cCover.src = cover || '';
        cTitle.textContent = '¿Deseas eliminar ' + (type === 'album' ? 'este álbum?' : 'esta canción?');
        cSub.textContent   = title || '';
        dForm.action       = action;
        cModal.setAttribute('aria-hidden','false');
        document.body.classList.add('blurred','modal-open');
        dangerBtn.focus();

        const onKey = (e)=>{
          if (e.key === 'Escape'){ closeConfirm(); }
          if (e.key === 'Enter' && cModal.getAttribute('aria-hidden') === 'false' && document.activeElement !== cancelBtn){
            e.preventDefault(); dangerBtn.click();
          }
          trapFocus(cModal, e);
        };
        cModal._escHandler = onKey;
        document.addEventListener('keydown', onKey);
      }
      function closeConfirm(){
        cModal.setAttribute('aria-hidden','true');
        document.body.classList.remove('blurred','modal-open');
        if (cModal._escHandler){
          document.removeEventListener('keydown', cModal._escHandler);
          cModal._escHandler = null;
        }
        lastFocused?.focus?.();
      }
      $$('.open-delete', root).forEach(btn=>{
        btn.addEventListener('click', (e)=>{
          e.stopPropagation();
          openConfirm(btn.dataset.type, btn.dataset.action, btn.dataset.title, btn.dataset.cover);
        });
      });
      cancelBtn?.addEventListener('click', closeConfirm);
      cModal?.addEventListener('click', (e)=>{ if (e.target === cModal) closeConfirm(); });
    })();

    // === BIO modal ===
    (function bio(){
      const modal = $('#bioModal');
      const open  = $('#openBio');
      const closeTop = $('#closeBioTop');
      const closeBtn = $('#closeBio');
      const close = () => modal?.setAttribute('aria-hidden','true');

      open?.addEventListener('click', ()=> modal?.setAttribute('aria-hidden','false'));
      closeTop?.addEventListener('click', close);
      closeBtn?.addEventListener('click', close);
      modal?.addEventListener('click', (e)=>{ if(e.target===modal) close(); });
      document.addEventListener('keydown', (e)=> {
        if (e.key === 'Escape' && modal?.getAttribute('aria-hidden') === 'false') close();
      }, { passive:true });
    })();

    // === Likes (intercepta para pintar al instante) ===
    $$('.inline-like', root).forEach(form => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('.like-btn');
        const url = form.action;
        try {
          const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCSRF(), 'X-Requested-With':'XMLHttpRequest' }
          });
          if (res.ok) {
            const pressed = btn.getAttribute('aria-pressed') === 'true';
            btn.setAttribute('aria-pressed', String(!pressed));
            const i = btn.querySelector('i');
            i.classList.toggle('fa-regular', pressed);
            i.classList.toggle('fa-solid', !pressed);
            btn.classList.toggle('is-liked', !pressed);
          } else {
            console.warn('Like request failed', await res.text());
          }
        } catch (err) { console.error(err); }
      });
    });

    // === Agregar a playlist ===
    function openAddToPlaylist(songId){
      const ev = new CustomEvent('playlist:add', { detail: { songId } });
      window.dispatchEvent(ev);
      if (typeof window.showAddToPlaylist === 'function') {
        window.showAddToPlaylist(songId);
      }
    }
    $$('.add-playlist-btn', root).forEach(btn => {
      btn.addEventListener('click', (e)=>{
        e.stopPropagation();
        const row = btn.closest('.song-row');
        openAddToPlaylist(row?.dataset.songId);
      });
    });
    $$('.kebab-menu .menu-item', root).forEach(item=>{
      item.addEventListener('click', (e)=>{
        e.stopPropagation();
        const row = item.closest('.song-row');
        const id  = row?.dataset.songId;
        const act = item.dataset.action;
        if (act === 'playlist') openAddToPlaylist(id);
        if (act === 'like') row?.querySelector('.inline-like .like-btn')?.click();
        if (act === 'queue') window.dispatchEvent(new CustomEvent('player:queue:add',{detail:{songId:id}}));
        item.closest('.kebab-menu')?.classList.remove('open');
      });
    });

    // Recalcular altura al final
    ri(syncSongsHeight);
    window.addEventListener('resize', syncLater, {passive:true});
    $$('#albumsViewport img').forEach(img => img.addEventListener('load', syncLater, {once:true}));
  }

  document.addEventListener('DOMContentLoaded', initProfile);
  document.addEventListener('turbo:load', initProfile);
  document.addEventListener('turbo:render', initProfile);
  window.addEventListener('pageshow', (e)=>{ if(e.persisted) initProfile(); });
})();
