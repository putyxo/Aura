// resources/js/ed_perfil.js

/**
 * resources/js/ed_perfil.js
 *
 * JS de perfil con DELEGACIÓN DE EVENTOS.
 * - UI optimista para "Me gusta" (corazón instantáneo).
 * - Menú de 3 puntos con 3 opciones y cierre fuera/ESC.
 * - Modal de confirmar eliminación (álbum y canción).
 * - Reproducción inmediata + precalentado (warm-up) de audios.
 * - Carruseles de álbumes y lanzamientos.
 */

(() => {
  'use strict';

  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const getCSRF = () => $('meta[name="csrf-token"]')?.content || '';

  /** Evita que el click en acciones dentro de la fila dispare la reproducción */
  const isAction = (el) => !!el.closest('.song-actions, .menu-wrap, form, button, a, input, .trash-float, .trash-song');

  /* =========================
   *  MENÚ KEBAB (tres puntos)
   * ========================= */
  function closeAllMenus(exceptWrap = null) {
    $$('.menu-wrap.open').forEach(w => { if (!exceptWrap || w !== exceptWrap) w.classList.remove('open'); });
    $$('.more-btn[aria-expanded="true"]').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
  }

  document.addEventListener('click', (e) => {
    // Toggle del kebab
    const moreBtn = e.target.closest('.more-btn');
    if (moreBtn) {
      const wrap = moreBtn.closest('.menu-wrap');
      const willOpen = !wrap.classList.contains('open');
      closeAllMenus();
      wrap.classList.toggle('open', willOpen);
      moreBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    // Click fuera: cerrar menús
    if (!e.target.closest('.menu-wrap')) closeAllMenus();
  }, { passive: true });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAllMenus();
  });

  /* =========================
   *  LIKE (UI optimista + fetch POST)
   * ========================= */
  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form.inline-like');
    if (!form) return;

    e.preventDefault();
    const btn  = form.querySelector('.like-btn');
    const icon = form.querySelector('.like-btn i');
    const wasLiked = btn.classList.contains('is-liked');

    // OPTIMISTA: toggle instantáneo
    btn.classList.toggle('is-liked', !wasLiked);
    btn.setAttribute('aria-pressed', (!wasLiked).toString());
    if (wasLiked) { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); }
    else          { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); }

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': getCSRF(),
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      });

      if (!res.ok) throw new Error('HTTP '+res.status);
      let data = {};
      try { data = await res.json(); } catch {}
      const finalLiked = !!data.liked;

      // Sincroniza con backend
      btn.classList.toggle('is-liked', finalLiked);
      btn.setAttribute('aria-pressed', String(finalLiked));
      if (finalLiked) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); }
      else            { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); }
    } catch (err) {
      // Revertir si hubo error
      btn.classList.toggle('is-liked', wasLiked);
      btn.setAttribute('aria-pressed', String(wasLiked));
      if (wasLiked) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); }
      else          { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); }
      console.error('Like error:', err);
    }
  });

  /* =========================
   *  MODAL CONFIRMAR DELETE
   * ========================= */
  const getConfirmRefs = () => ({
    confirmModal   : $('#confirmModal'),
    confirmCover   : $('#confirmCover'),
    confirmSubtitle: $('#confirmSubtitle'),
    deleteForm     : $('#deleteForm'),
    cancelDelete   : $('#cancelDelete'),
  });

  function openConfirm({ action, title, cover }) {
    const { confirmModal, confirmCover, confirmSubtitle, deleteForm } = getConfirmRefs();
    if (!confirmModal) return;
    if (deleteForm) deleteForm.setAttribute('action', action || '#');
    if (confirmCover) confirmCover.src = cover || '';
    if (confirmSubtitle) confirmSubtitle.textContent = title ? `“${title}”` : '';
    confirmModal.setAttribute('aria-hidden', 'false');
    confirmModal.classList.add('open');
  }

  function closeConfirm() {
    const { confirmModal } = getConfirmRefs();
    if (!confirmModal) return;
    confirmModal.setAttribute('aria-hidden', 'true');
    confirmModal.classList.remove('open');
  }

  document.addEventListener('click', (e) => {
    const { confirmModal, cancelDelete } = getConfirmRefs();

    // Abrir modal de eliminar (botón fuera del menú o dentro del menú)
    const delBtn = e.target.closest('.open-delete');
    if (delBtn) {
      e.preventDefault();
      const payload = {
        action: delBtn.dataset.action,
        title : delBtn.dataset.title,
        cover : delBtn.dataset.cover
      };
      openConfirm(payload);
      return;
    }

    // Cerrar al hacer click fuera del diálogo
    if (confirmModal && (confirmModal.classList.contains('open') || confirmModal.getAttribute('aria-hidden') === 'false')) {
      if (e.target === confirmModal) { closeConfirm(); return; }
    }

    // Botón cancelar
    if (cancelDelete && e.target === cancelDelete) {
      e.preventDefault();
      closeConfirm();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeConfirm();
  });

  /* =========================
   *  MODALES: BIO y EDITAR
   * ========================= */
  function openModal(modal) {
    if (!modal) return;
    modal.removeAttribute('inert');
    modal.setAttribute('aria-hidden', 'false');
  }
  function closeModal(modal) {
    if (!modal) return;
    modal.setAttribute('aria-hidden', 'true');
    modal.setAttribute('inert', '');
  }

  function bindProfileModals() {
    const bioModal     = $('#bioModal');
    const openBioBtn   = $('#openBio');
    const closeBioBtn1 = $('#closeBio');
    const closeBioBtn2 = $('#closeBioTop');

    openBioBtn && openBioBtn.addEventListener('click', () => openModal(bioModal), { passive: true });
    closeBioBtn1 && closeBioBtn1.addEventListener('click', () => closeModal(bioModal), { passive: true });
    closeBioBtn2 && closeBioBtn2.addEventListener('click', () => closeModal(bioModal), { passive: true });
    bioModal && bioModal.addEventListener('click', (e) => { if (e.target === bioModal) closeModal(bioModal); }, { passive: true });

    const editModal     = $('#editModal');
    const openEditBtn   = $('#editBtn');
    const closeEditBtn1 = $('#closeEdit');
    const closeEditBtn2 = $('#closeEditTop');

    openEditBtn && openEditBtn.addEventListener('click', () => openModal(editModal), { passive: true });
    closeEditBtn1 && closeEditBtn1.addEventListener('click', () => closeModal(editModal), { passive: true });
    closeEditBtn2 && closeEditBtn2.addEventListener('click', () => closeModal(editModal), { passive: true });
    editModal && editModal.addEventListener('click', (e) => { if (e.target === editModal) closeModal(editModal); }, { passive: true });
  }

  /* =========================
   *  CARRUSEL ÁLBUMES 2×2
   * ========================= */
  function initAlbumsCarousel() {
    const track = $('#albumsTrack');
    if (!track) return;
    const pages = parseInt(track.dataset.pages || '1', 10) || 1;
    const prev  = $('#albumsPrev');
    const next  = $('#albumsNext');
    const label = $('#albumsPageLabel');
    let page = 0;

    function update(){
      track.style.transform = `translateX(-${page * 100}%)`;
      if (prev) prev.disabled = page <= 0;
      if (next) next.disabled = page >= pages - 1;
      if (label) label.textContent = pages > 1 ? `${page+1} / ${pages}` : '';
    }
    prev && prev.addEventListener('click', () => { if (page>0){ page--; update(); }});
    next && next.addEventListener('click', () => { if (page<pages-1){ page++; update(); }});
    update();
  }

  /* =========================
   *  CARRUSEL LANZAMIENTOS
   * ========================= */
  function initReleasesCarousel() {
    const track = $('#releasesTrack');
    if (!track) return;
    const pages = parseInt(track.dataset.pages || '1', 10) || 1;
    const prev  = $('#releasesPrev');
    const next  = $('#releasesNext');
    const pager = $('#releasesPager');
    let page = 0;

    function paintPager(){
      if (!pager) return;
      pager.innerHTML = '';
      for (let i=0; i<pages; i++){
        const dot = document.createElement('button');
        dot.className = 'pager-dot' + (i===page ? ' active' : '');
        dot.type = 'button';
        dot.setAttribute('aria-label', `Ir a página ${i+1}`);
        dot.addEventListener('click', () => { page = i; update(); });
        pager.appendChild(dot);
      }
    }

    function update(){
      track.style.transform = `translateX(-${page * 100}%)`;
      if (prev) prev.disabled = page <= 0;
      if (next) next.disabled = page >= pages - 1;
      paintPager();
    }

    prev && prev.addEventListener('click', () => { if (page>0){ page--; update(); }});
    next && next.addEventListener('click', () => { if (page<pages-1){ page++; update(); }});

    update();
  }

  /* =========================
   *  REPRODUCCIÓN + PRECALENTADO
   * ========================= */
  // Player del lateral si existe, si no, usa un <audio> en memoria.
  const playerCard = $('#rightPlayer');
  const audio = playerCard ? (playerCard.querySelector('audio') || new Audio()) : new Audio();
  if (playerCard && !playerCard.querySelector('audio')) {
    audio.preload = 'metadata';
    playerCard.appendChild(audio);
  }

  function setPlayerUI({title, artist, cover}){
    if (!playerCard) return;
    const coverImg = playerCard.querySelector('.cover');
    const nameEl   = playerCard.querySelector('.song-name');
    const autorEl  = playerCard.querySelector('.song-autor');
    coverImg && (coverImg.src = cover || coverImg.src);
    nameEl  && (nameEl.textContent = title || 'Sin título');
    autorEl && (autorEl.textContent = artist || 'Artista');
  }

  async function playSrc(src, meta){
    if (!src) return;
    try {
      if (audio.src !== src) {
        audio.preload = 'metadata';
        audio.src = src;
      }
      setPlayerUI(meta || {});
      await audio.play();
    } catch(err){ console.error('Play error:', err); }
  }

  // Click en fila de canción → reproducir (evita si fue una acción)
  document.addEventListener('click', (e) => {
    const row = e.target.closest('.cancion-item');
    if (!row || isAction(e.target)) return;
    const src = row.getAttribute('data-src');
    const title = row.getAttribute('data-title');
    const artist = row.getAttribute('data-artist');
    const cover = row.getAttribute('data-cover');
    playSrc(src, {title, artist, cover});
  });

  // Reproducir desde lanzamientos (si es canción)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.play-release');
    if (!btn) return;
    const src = btn.getAttribute('data-src');
    const title = btn.getAttribute('data-title') || (btn.closest('.release-card')?.querySelector('h4')?.textContent?.trim() || 'Sin título');
    const artist = btn.getAttribute('data-artist') || ($('#artistName')?.textContent?.trim() || 'Artista');
    const cover = btn.getAttribute('data-cover') || (btn.closest('.release-card')?.querySelector('img')?.src || '');
    playSrc(src, {title, artist, cover});
  });

  // Precalentado (warm-up): primeras 3 canciones y hover
  const warmed = new Set();
  function warmAudio(src){
    if (!src || warmed.has(src)) return;
    const a = new Audio();
    a.preload = 'metadata';
    a.src = src;
    warmed.add(src);
  }
  function warmOnHover(){
    $$('.cancion-item').slice(0,3).forEach(row => warmAudio(row.getAttribute('data-src')));
    $$('.cancion-item').forEach(row => {
      row.addEventListener('pointerenter', () => warmAudio(row.getAttribute('data-src')), { once:true, passive:true });
    });
    $$('.play-release').forEach(btn => {
      btn.addEventListener('pointerenter', () => warmAudio(btn.getAttribute('data-src')), { once:true, passive:true });
    });
  }

  /* =========================
   *  PREFETCH de páginas destino
   * ========================= */
  function bindPrefetch(){
    const hovered = new Set();
    document.addEventListener('pointerenter', (e) => {
      const a = e.target.closest('a[data-prefetch="true"]');
      if (!a || hovered.has(a.href)) return;
      hovered.add(a.href);
      const link = document.createElement('link');
      link.rel = 'prefetch';
      link.href = a.href;
      document.head.appendChild(link);
    }, {passive:true});
  }

  /* =========================
   *  ARRANQUE
   * ========================= */
  function bootOncePerRender(){
    bindProfileModals();
    initAlbumsCarousel();
    initReleasesCarousel();
    warmOnHover();
    bindPrefetch();
  }

  // Soporte Turbo/Hotwire y carga inicial
  document.addEventListener('turbo:load', bootOncePerRender);
  document.addEventListener('DOMContentLoaded', bootOncePerRender);
})();




/* =========================
 *  PICKER DE PLAYLISTS
 * ========================= */
(function(){
  const root = document.getElementById('page-profile');
  if (!root) return;

  const listUrl   = root.dataset.plList || '';
  const quickUrl  = root.dataset.plQuick || '';
  const addPat    = root.dataset.plAddPattern || '';
  const isAuth    = (root.dataset.auth === '1');

  // Refs
  const modal     = document.getElementById('playlistModal');
  const listEl    = document.getElementById('plList');
  const searchEl  = document.getElementById('plSearch');
  const quickForm = document.getElementById('plQuickForm');
  const quickName = document.getElementById('plQuickName');
  const plCloseT  = document.getElementById('plCloseTop');
  const plCloseB  = document.getElementById('plCloseBottom');

  let currentSongId = null;
  let cached = [];

  function escapeHtml(s){
    return (s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  function openPl(songId){
    if (!isAuth) return; // botón ya está protegido por @auth, pero por si acaso
    currentSongId = songId;
    if (typeof openModal === 'function') openModal(modal);
    else { modal?.setAttribute('aria-hidden','false'); modal?.removeAttribute('inert'); }
    paintLoading();
    fetchList();
  }

  function closePl(){
    if (typeof closeModal === 'function') closeModal(modal);
    else { modal?.setAttribute('aria-hidden','true'); modal?.setAttribute('inert',''); }
  }

  function paintLoading(){
    listEl.innerHTML = `<li class="pl-empty">Cargando playlists...</li>`;
  }

  function paintEmpty(){
    listEl.innerHTML = `<li class="pl-empty">Aún no tienes playlists. Crea una con el cuadro de abajo.</li>`;
  }

  function render(items){
    if (!items?.length) { paintEmpty(); return; }
    listEl.innerHTML = items.map(p => `
      <li>
        <button type="button" class="pl-item" data-id="${p.id}">
          <i class="fa-solid fa-list"></i>
          <span>${escapeHtml(p.nombre || p.name || 'Sin título')}</span>
        </button>
      </li>
    `).join('');
  }

  async function fetchList(){
    try{
      const res = await fetch(listUrl, { headers: { 'Accept':'application/json' }, credentials:'same-origin' });
      if (!res.ok) throw new Error('HTTP '+res.status);
      cached = await res.json();
      render(cached);
    }catch(e){
      console.error(e);
      listEl.innerHTML = `<li class="pl-empty">No se pudo cargar. Recarga la página.</li>`;
    }
  }

  async function addToPlaylist(playlistId){
    if (!currentSongId) return;
    const url = addPat.replace('PL_ID', playlistId).replace('SONG_ID', currentSongId);
    try{
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      // opcional: toast
      closePl();
    }catch(e){
      console.error('addToPlaylist error:', e);
      alert('No se pudo agregar a la playlist.');
    }
  }

  // Delegación: abrir picker desde botón de cada canción
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.add-playlist-btn');
    if (!btn) return;
    const row = btn.closest('.song-row');
    const sid = row?.dataset?.id;
    if (!sid) return;
    e.preventDefault();
    openPl(sid);
  });

  // Click en una playlist
  listEl?.addEventListener('click', (e) => {
    const item = e.target.closest('.pl-item');
    if (!item) return;
    addToPlaylist(item.dataset.id);
  });

  // Búsqueda local
  searchEl?.addEventListener('input', () => {
    const q = searchEl.value.trim().toLowerCase();
    if (!cached?.length) return;
    const filtered = !q ? cached : cached.filter(p => (p.nombre || p.name || '').toLowerCase().includes(q));
    render(filtered);
  });

  // Crear playlist rápida y agregar
  quickForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const nombre = (quickName?.value || '').trim();
    if (!nombre) return;
    try{
      const res = await fetch(quickUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept'      : 'application/json',
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ nombre })
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json(); // { id, nombre, message }
      // mete al principio y actualiza cache
      const newItem = { id: data.id, nombre: data.nombre };
      cached = [newItem, ...cached];
      render(cached);
      quickName.value = '';
      // auto-agregar
      addToPlaylist(newItem.id);
    }catch(err){
      console.error('quick playlist error:', err);
      alert('No se pudo crear la playlist.');
    }
  });

  // Cierre modal
  plCloseT?.addEventListener('click', closePl);
  plCloseB?.addEventListener('click', closePl);
  modal?.addEventListener('click', (e)=>{ if (e.target === modal) closePl(); });
  document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape' && modal?.getAttribute('aria-hidden') === 'false') closePl(); });
})();
