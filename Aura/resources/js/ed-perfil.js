// resources/js/ed-perfil.js
export default function initProfile() {
  console.log("Init perfil");

  const $  = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  /* ==========================================================
   * Bloque 1 — Variables CSS (sidebar / player)
   * ========================================================== */
  const rootEl = document.getElementById('page-profile') || document.documentElement;
  const sidebar = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
  const rightPlayer = document.getElementById('rightPlayer');
  const setVar = (n, px) => rootEl.style.setProperty(n, Math.max(0, Math.round(px)) + 'px');

  let rafId = null;
  function measureNow() {
    setVar('--aurp-sb-w', sidebar ? sidebar.getBoundingClientRect().width || 90 : 0);
    setVar('--aurp-rp-w', rightPlayer ? rightPlayer.getBoundingClientRect().width || 360 : 0);
    rafId = null;
  }
  function measure(){ if (!rafId) rafId = requestAnimationFrame(measureNow); }

  window.addEventListener('load', measure, { once:true });
  window.addEventListener('resize', measure, { passive:true });

  if (window.ResizeObserver){
    const ro = new ResizeObserver(measure);
    sidebar && ro.observe(sidebar);
    rightPlayer && ro.observe(rightPlayer);
  }
  const mo = new MutationObserver(measure);
  sidebar && mo.observe(sidebar, {attributes:true, attributeFilter:['class','style']});
  document.body && mo.observe(document.body, {attributes:true, attributeFilter:['class']});
  measure();

  /* ==========================================================
   * Bloque 2 — Banner en alta resolución
   * ========================================================== */
  const run = (fn) =>
    (window.requestIdleCallback
      ? requestIdleCallback(fn, { timeout: 1500 })
      : setTimeout(fn, 0));

  run(() => {
    const b = document.querySelector('.profile-banner');
    if (!b) return;
    const hires = b.getAttribute('data-hires');
    if (!hires) return;
    const img = new Image();
    img.decoding = 'async';
    img.src = hires;
    img.onload = () => {
      b.style.backgroundImage = `url('${hires}')`;
      b.classList.add('loaded');
    };
  });

  /* ==========================================================
   * Bloque 3 — Modales (Bio / Editar / Confirmar eliminar)
   * ========================================================== */
  function openModal(el){
    if(!el) return;
    document.body.classList.add('blurred');
    el.removeAttribute('inert');
    el.setAttribute('aria-hidden','false');
  }
  function closeModal(el){
    if(!el) return;
    el.setAttribute('aria-hidden','true');
    el.setAttribute('inert','');
    document.body.classList.remove('blurred');
  }

  const bioModal  = $('#bioModal');
  const editModal = $('#editModal');
  $('#openBio')?.addEventListener('click', ()=> openModal(bioModal));
  $('#closeBio')?.addEventListener('click', ()=> closeModal(bioModal));
  $('#closeBioTop')?.addEventListener('click', ()=> closeModal(bioModal));
  bioModal?.addEventListener('click', (e)=>{ if(e.target===bioModal) closeModal(bioModal); });

  $('#editBtn')?.addEventListener('click', ()=> openModal(editModal));
  $('#closeEdit')?.addEventListener('click', ()=> closeModal(editModal));
  $('#closeEditTop')?.addEventListener('click', ()=> closeModal(editModal));
  editModal?.addEventListener('click', (e)=>{ if(e.target===editModal) closeModal(editModal); });

  document.addEventListener('keydown', (e)=> {
    if(e.key==='Escape'){
      closeModal(bioModal);
      closeModal(editModal);
      closeConfirm();
    }
  });

  const confirmModal   = $('#confirmModal');
  const confirmCover   = $('#confirmCover');
  const confirmSubtitle= $('#confirmSubtitle');
  const deleteForm     = $('#deleteForm');
  const cancelDelete   = $('#cancelDelete');

  function openConfirm({action, title, cover}){
    if (!confirmModal) return;
    deleteForm?.setAttribute('action', action || '#');
    if (confirmCover) confirmCover.src = cover || '';
    if (confirmSubtitle) confirmSubtitle.textContent = title ? `“${title}”` : '';
    document.body.classList.add('blurred');
    confirmModal.setAttribute('aria-hidden','false');
  }
  function closeConfirm(){
    if (!confirmModal) return;
    document.body.classList.remove('blurred');
    confirmModal.setAttribute('aria-hidden','true');
  }

  document.addEventListener('click', (e) => {
    const delBtn = e.target.closest('.open-delete');
    if (delBtn) {
      e.preventDefault();
      const action = delBtn.dataset.action || '#';
      if (action === '#') return;
      openConfirm({
        action,
        title: delBtn.dataset.title,
        cover: delBtn.dataset.cover
      });
      return;
    }
    if (confirmModal && confirmModal.getAttribute('aria-hidden')==='false' && e.target === confirmModal) {
      closeConfirm();
    }
    if (cancelDelete && e.target === cancelDelete) {
      e.preventDefault();
      closeConfirm();
    }
  });

  /* ==========================================================
   * Bloque 4 — Menú 3 puntos
   * ========================================================== */
  function closeMenus(except=null){
    $$('.menu-wrap .kebab-menu.open').forEach(m=>{
      if (except && m===except) return;
      m.classList.remove('open');
      m.parentElement?.querySelector('.more-btn')?.setAttribute('aria-expanded','false');
    });
  }
  document.addEventListener('click', (e) => {
    const moreBtn = e.target.closest('.more-btn');
    if (moreBtn) {
      const wrap = moreBtn.closest('.menu-wrap');
      const menu = wrap?.querySelector('.kebab-menu');
      if (!menu) return;
      const willOpen = !menu.classList.contains('open');
      closeMenus();
      menu.classList.toggle('open', willOpen);
      moreBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      return;
    }
    if (!e.target.closest('.menu-wrap')) closeMenus();
  });

  /* ==========================================================
   * Bloque 5 — Likes
   * ========================================================== */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.inline-like .like-btn');
    if (!btn) return;
    e.preventDefault();
    const form = btn.closest('form.inline-like');
    const icon = btn.querySelector('i');
    const wasLiked = btn.classList.contains('is-liked');

    // UI optimista
    btn.classList.toggle('is-liked', !wasLiked);
    btn.setAttribute('aria-pressed', (!wasLiked).toString());
    icon.classList.toggle('fa-regular', wasLiked);
    icon.classList.toggle('fa-solid', !wasLiked);

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
      const data = await res.json();
      const finalLiked = !!data.liked;
      btn.classList.toggle('is-liked', finalLiked);
      btn.setAttribute('aria-pressed', String(finalLiked));
      icon.classList.toggle('fa-regular', !finalLiked);
      icon.classList.toggle('fa-solid', finalLiked);
    } catch (err) {
      // rollback
      btn.classList.toggle('is-liked', wasLiked);
      btn.setAttribute('aria-pressed', String(wasLiked));
      icon.classList.toggle('fa-regular', !wasLiked);
      icon.classList.toggle('fa-solid', wasLiked);
      console.error('Like error:', err);
    }
  });

  /* ==========================================================
   * Bloque 6 — Reproducción rápida
   * ========================================================== */
  const playerCard = document.getElementById('rightPlayer');
  const sharedAudio = playerCard ? (playerCard.querySelector('audio') || new Audio()) : new Audio();
  if (playerCard && !playerCard.querySelector('audio')) {
    sharedAudio.preload = 'metadata';
    playerCard.appendChild(sharedAudio);
  } else {
    sharedAudio.preload = 'metadata';
  }

  function setPlayerUI({title, artist, cover}){
    if (!playerCard) return;
    const coverImg = playerCard.querySelector('.cover');
    const nameEl   = playerCard.querySelector('.song-name');
    const autorEl  = playerCard.querySelector('.song-autor');
    coverImg && cover && (coverImg.src = cover);
    nameEl  && (nameEl.textContent = title || 'Sin título');
    autorEl && (autorEl.textContent = artist || 'Artista');
  }

  async function playSrc(src, meta){
    if (!src) return;
    try {
      if (window.AuraQueue?.externalPlay) {
        window.AuraQueue.externalPlay({ id: meta?.id, src, title: meta?.title, artist: meta?.artist, cover: meta?.cover });
        return;
      }
      if (window.AuraPlayer?.play) {
        window.AuraPlayer.play({ id: meta?.id, src, title: meta?.title, artist: meta?.artist, cover: meta?.cover });
        return;
      }
      if (sharedAudio.src !== src) {
        sharedAudio.preload = 'metadata';
        sharedAudio.src = src;
      }
      setPlayerUI(meta || {});
      await sharedAudio.play();
    } catch (err) { console.error('Play error:', err); }
  }

  document.addEventListener('click', (e) => {
    const row = e.target.closest('.song-row');
    if (!row) return;
    if (e.target.closest('.song-actions, .menu-wrap, form, button, a, input, .trash-float')) return;
    playSrc(row.getAttribute('data-src'), {
      id: row.getAttribute('data-id'),
      title: row.getAttribute('data-title'),
      artist: row.getAttribute('data-artist'),
      cover: row.getAttribute('data-cover')
    });
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.play-release');
    if (!btn) return;
    playSrc(btn.dataset.src, {
      id: btn.dataset.id,
      title: btn.dataset.title,
      artist: btn.dataset.artist,
      cover: btn.dataset.cover
    });
  });

  /* ==========================================================
   * Bloque 7 — Prefetch de álbumes
   * ========================================================== */
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

  /* ==========================================================
   * Bloque 8 — Añadir a cola
   * ========================================================== */
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="queue"]');
    if (!btn) return;
    const row = btn.closest('.song-row');
    if (!row) return;

    const song = {
      id: Number(row.dataset.id),
      title: row.dataset.title,
      artist: row.dataset.artist,
      cover: row.dataset.cover,
      audio: row.dataset.src,
      duration: Number(row.dataset.duration) || 0
    };

    if (window.AuraQueue?.addToEnd) {
      window.AuraQueue.addToEnd([song]);
    }
  });

  /* ==========================================================
   * Bloque 9 — Carrusel álbumes
   * ========================================================== */
  function initCarousel(trackSel, prevSel, nextSel, labelSel, pagerSel){
    const track = $(trackSel); if (!track) return;
    const pages = parseInt(track.dataset.pages || '1', 10) || 1;
    const prev  = $(prevSel);
    const next  = $(nextSel);
    const label = labelSel ? $(labelSel) : null;
    const pager = pagerSel ? $(pagerSel) : null;
    let page = 0;

    function paintPager(){
      if (!pager) return;
      pager.innerHTML = '';
      for (let i=0; i<pages; i++){
        const dot = document.createElement('div');
        dot.className = 'dot' + (i===page ? ' active' : '');
        pager.appendChild(dot);
      }
    }
    function update(){
      track.style.transform = `translateX(-${page * 100}%)`;
      prev && (prev.disabled = page <= 0);
      next && (next.disabled = page >= pages - 1);
      label && (label.textContent = pages > 1 ? `${page+1} / ${pages}` : '');
      paintPager();
    }
    prev && prev.addEventListener('click', () => { if (page>0){ page--; update(); }});
    next && next.addEventListener('click', () => { if (page<pages-1){ page++; update(); }});
    update();
  }

  initCarousel('#albumsTrack', '#albumsPrev', '#albumsNext', '#albumsPageLabel', null);
}
