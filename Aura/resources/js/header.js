/* ================= AURA HEADER JS (delegated, framework-agnostic) ================= */
/* Funciona aunque el DOM se reemplace (Inertia, Turbo, Livewire, PJAX, etc.)       */
/* Se registra UNA sola vez y usa delegación de eventos + recalculos de posición.   */

(() => {
  if (window.__ahDelegatedInit) return; // evita doble carga si Vite HMR
  window.__ahDelegatedInit = true;

  // Helpers
  const hdr      = () => document.querySelector('.ah-header');
  const qsH      = (sel) => hdr()?.querySelector(sel);
  const $$H      = (sel) => Array.from(hdr()?.querySelectorAll(sel) || []);
  const $        = (sel, root=document) => root.querySelector(sel);
  const $$       = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const dbToGain = (db) => Math.pow(10, db/20);

  // ---------- POP SHIFT (Notificaciones) ----------
  function applyPopoverShift() {
    const header = hdr(); if (!header) return;
    const panel = qsH('#ahNotifPanel'); if (!panel) return;

    const styles     = getComputedStyle(header);
    const leftOffset = parseFloat(styles.getPropertyValue('--ah-left-offset')) || 0;
    const rightOffset= parseFloat(styles.getPropertyValue('--ah-right-offset')) || 0;
    const safeGap    = parseFloat(styles.getPropertyValue('--ah-safe-gap')) || 12;

    const rect = panel.getBoundingClientRect();
    const leftLimit  = safeGap + leftOffset;
    const rightLimit = window.innerWidth - rightOffset - safeGap;
    let shift = 0;
    if (rect.right > rightLimit) shift = -(rect.right - rightLimit);
    if (rect.left + shift < leftLimit) shift = leftLimit - rect.left;
    panel.style.setProperty('--ah-pop-shift', `${shift}px`);
  }

  // ---------- DROP SHIFT (Usuario) ----------
  function applyDropdownShift() {
    const header = hdr(); if (!header) return;
    const dd = qsH('#ahUserDropdown'); if (!dd) return;

    const styles     = getComputedStyle(header);
    const rightOffset= parseFloat(styles.getPropertyValue('--ah-right-offset')) || 0;
    const safeGap    = parseFloat(styles.getPropertyValue('--ah-safe-gap')) || 12;

    const rect = dd.getBoundingClientRect();
    const limit = window.innerWidth - rightOffset - safeGap;
    let shift = 0;
    if (rect.right > limit) shift = -(rect.right - limit);
    if (rect.left + shift < safeGap) shift = safeGap - rect.left;
    dd.style.setProperty('--ah-drop-shift', `${shift}px`);
  }

  // ---------- OPEN/CLOSE (Usuario) ----------
  function openUser() {
    const root = qsH('.ah-user'); const btn = qsH('#ahUserBtn'); const dd = qsH('#ahUserDropdown');
    if (!root || !btn || !dd) return;
    root.classList.add('open');
    btn.setAttribute('aria-expanded','true');
    dd.setAttribute('aria-hidden','false');
    requestAnimationFrame(applyDropdownShift);
  }
  function closeUser() {
    const root = qsH('.ah-user'); const btn = qsH('#ahUserBtn'); const dd = qsH('#ahUserDropdown');
    if (!root || !btn || !dd) return;
    root.classList.remove('open');
    btn.setAttribute('aria-expanded','false');
    dd.setAttribute('aria-hidden','true');
  }
  function toggleUser() {
    const root = qsH('.ah-user');
    if (!root) return;
    root.classList.contains('open') ? closeUser() : openUser();
  }

  // ---------- OPEN/CLOSE (Notificaciones) ----------
  function openNotif() {
    const wrap = qsH('.ah-notif.ah-pop'); const btn = qsH('#ahNotifBtn'); const panel = qsH('#ahNotifPanel');
    if (!wrap || !btn || !panel) return;
    wrap.classList.add('open');
    btn.setAttribute('aria-expanded','true');
    panel.setAttribute('aria-hidden','false');
    requestAnimationFrame(applyPopoverShift);
  }
  function closeNotif() {
    const wrap = qsH('.ah-notif.ah-pop'); const btn = qsH('#ahNotifBtn'); const panel = qsH('#ahNotifPanel');
    if (!wrap || !btn || !panel) return;
    wrap.classList.remove('open');
    btn.setAttribute('aria-expanded','false');
    panel.setAttribute('aria-hidden','true');
  }
  function toggleNotif() {
    const wrap = qsH('.ah-notif.ah-pop');
    if (!wrap) return;
    wrap.classList.contains('open') ? closeNotif() : openNotif();
  }

  // ---------- PUSH BODY (header fijo) ----------
  function pushBody() {
    const header = hdr(); if (!header) return;
    const h = Math.ceil(header.getBoundingClientRect().height);
    document.body.style.paddingTop = h + 'px';
  }

  // ---------- LANG SWITCH ----------
  function applyLangUI(lang) {
    const langSwitch = qsH('#ahLangSwitch');
    if (langSwitch) {
      langSwitch.setAttribute('aria-checked', lang==='en' ? 'true' : 'false');
      langSwitch.dataset.lang = lang;
    }
    const input = qsH('.ah-search-input');
    const i18n  = { es:{ search:'Buscar canciones, artistas...' }, en:{ search:'Search tracks, artists...' } };
    if (input) input.placeholder = (i18n[lang] || i18n.es).search;
  }
  function setLang(lang) {
    applyLangUI(lang);
    try { localStorage.setItem('ahLang', lang); } catch(_){}
    // Notifica al backend si quieres
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
      fetch(`/locale/toggle?lang=${lang}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token} });
    } catch(_){}
    window.dispatchEvent(new CustomEvent('lang:toggle', { detail:{ lang } }));
  }

  // ---------- EQUALIZER (una sola vez, persiste entre páginas) ----------
  (() => {
    const FREQS = [60,170,310,600,1000,3000,6000,12000,14000,16000];
    let ac, filters=[], gPreamp;

    function ensureCtx(){
      if (ac) return;
      ac = new (window.AudioContext||window.webkitAudioContext)();

      filters = FREQS.map(freq=>{
        const f = ac.createBiquadFilter();
        f.type = 'peaking'; f.frequency.value = freq; f.Q.value = 1.0; f.gain.value = 0;
        return f;
      });

      gPreamp = ac.createGain(); gPreamp.gain.value = 1;

      for (let i=0;i<filters.length-1;i++) filters[i].connect(filters[i+1]);
      filters[filters.length-1].connect(gPreamp);
      gPreamp.connect(ac.destination);

      // Enlaza al player si existe
      const audio = document.querySelector('#player audio, audio#player');
      if (audio) {
        try {
          const srcNode = ac.createMediaElementSource(audio);
          srcNode.connect(filters[0]);
        } catch(e) { /* ya conectado */ }
      }
    }

    function applyInitialValues(){
      ensureCtx();
      const sliders = $$H('.eq-slider-global');
      sliders.forEach((sl, idx)=> {
        const db = parseFloat(sl.value || '0');
        filters[idx].gain.value = db;
      });
      const preamp = qsH('#global-preamp');
      const dbPre  = parseFloat(preamp?.value || '0');
      gPreamp.gain.value = dbToGain(dbPre);
    }

    document.addEventListener('DOMContentLoaded', applyInitialValues);
    window.bindEqualizerTo = function(audioEl){
      if (!audioEl) return; ensureCtx();
      try{
        const media = ac.createMediaElementSource(audioEl);
        media.connect(filters[0]);
      }catch(_){}
    };
  })();

  // ---------- SEARCH (delegado) ----------
  let searchIdx = -1;
  const debounce = (fn, ms=260) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

  const doSearch = debounce(async () => {
    const input = qsH('.ah-search-input'); const box = qsH('#ahSearchResults');
    if (!input || !box) return;
    const q = (input.value || '').trim();

    const open  = () => { box.style.display='block'; box.setAttribute('aria-expanded','true'); };
    const close = () => { box.style.display='none';  box.setAttribute('aria-expanded','false'); };

    if (q.length === 0) { box.innerHTML = ''; close(); return; }
    if (q.length < 2) { box.innerHTML = `<div class="ah-skel">Escribe al menos 2 letras…</div>`; open(); return; }
    box.innerHTML = `<div class="ah-skel">Buscando…</div>`; open();

    try {
      const res  = await fetch(`/buscar?q=${encodeURIComponent(q)}`, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
      if (!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();
      if (!Array.isArray(data) || !data.length) {
        box.innerHTML = `<div class="ah-skel">No se encontraron resultados</div>`;
        return;
      }
      box.innerHTML = data.map(item => {
        if (item.tipo === 'cancion') {
          return `
            <div class="ah-sr-item ah-sr-song" role="option" tabindex="-1">
              <img src="${item.avatar}" alt="">
              <div>
                <span class="ah-sr-main">${item.nombre}</span>
                <span class="ah-sr-sub">🎵 Canción — ${item.artist || ''}</span>
              </div>
              <button class="ah-hidden-btn" style="display:none"
                data-id="${item.id}" data-src="${item.audio || ''}"
                data-title="${item.nombre}" data-artist="${item.artist || 'Desconocido'}"
                data-cover="${item.avatar}"></button>
            </div>`;
        }
        const sub = item.tipo === 'usuario' ? '👤 Usuario' : '📀 Álbum';
        return `
          <a href="${item.url}" class="ah-sr-item" role="option" tabindex="-1">
            <img src="${item.avatar}" alt="">
            <div>
              <span class="ah-sr-main">${item.nombre}</span>
              <span class="ah-sr-sub">${sub}</span>
            </div>
          </a>`;
      }).join('');
      searchIdx = -1;
    } catch {
      box.innerHTML = `<div class="ah-skel">Error al buscar</div>`;
    }
  }, 260);

  // Delegated: input del buscador
  document.addEventListener('input', (e) => {
    if (e.target.closest('.ah-search-input')) doSearch();
  }, true);

  // Delegated: navegación con teclado en resultados
  document.addEventListener('keydown', (e) => {
    const input = e.target.closest('.ah-search-input');
    if (!input) return;
    const box = qsH('#ahSearchResults'); if (!box) return;
    const items = Array.from(box.querySelectorAll('.ah-sr-item'));
    if (!items.length) return;

    const open  = () => { box.style.display='block'; box.setAttribute('aria-expanded','true'); };
    const close = () => { box.style.display='none';  box.setAttribute('aria-expanded','false'); };

    if (e.key === 'ArrowDown') { e.preventDefault(); searchIdx = (searchIdx + 1) % items.length; }
    if (e.key === 'ArrowUp')   { e.preventDefault(); searchIdx = (searchIdx - 1 + items.length) % items.length; }
    if (e.key === 'Enter' && searchIdx >= 0) {
      e.preventDefault();
      const t = items[searchIdx];
      if (t.classList.contains('ah-sr-song')) {
        const b = t.querySelector('.ah-hidden-btn'); b?.click();
      } else if (t.tagName === 'A') {
        window.location.href = t.getAttribute('href');
      }
      close(); return;
    }
    if (e.key === 'Escape') { close(); return; }

    items.forEach(i => i.classList.remove('is-active'));
    if (searchIdx >= 0) { items[searchIdx].classList.add('is-active'); items[searchIdx].scrollIntoView({ block:'nearest' }); }
    open();
  }, true);

  // Delegated: click en una canción de resultados → reproducir
  document.addEventListener('click', (e) => {
    const song = e.target.closest('.ah-sr-song');
    if (!song) return;
    const btn = song.querySelector('.ah-hidden-btn'); if (!btn) return;

    const audio = document.querySelector('#player audio');
    if (audio) {
      audio.src = btn.dataset.src;
      audio.play().catch(()=>{});
      window.bindEqualizerTo?.(audio);
      const cover  = document.querySelector('#rightPlayer .cover');
      const title  = document.querySelector('#rightPlayer .song-name');
      const artist = document.querySelector('#rightPlayer .song-autor');
      if (cover)  cover.src = btn.dataset.cover;
      if (title)  title.textContent = btn.dataset.title;
      if (artist) artist.textContent = btn.dataset.artist;
    }

    const box = qsH('#ahSearchResults');
    if (box) { box.style.display='none'; box.setAttribute('aria-expanded','false'); }
  }, true);

  // ---------- Delegated: clicks globales para abrir/cerrar menús ----------
  document.addEventListener('click', (e) => {
    // User dropdown
    if (e.target.closest('#ahUserBtn')) {
      e.preventDefault();
      e.stopImmediatePropagation();
      toggleUser();
      return;
    }
    const userWrap = qsH('.ah-user');
    if (userWrap && !userWrap.contains(e.target)) closeUser();

    // Notificaciones
    if (e.target.closest('#ahNotifBtn')) {
      e.preventDefault();
      e.stopImmediatePropagation();
      toggleNotif();
      return;
    }
    const notifWrap = qsH('.ah-notif.ah-pop');
    if (notifWrap && !notifWrap.contains(e.target)) closeNotif();
  }, true); // fase de captura: más robusto frente a otros scripts

  // Escape cierra ambos
  document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') { closeUser(); closeNotif(); } }, true);

  // Resize re-posiciona si están abiertos
  window.addEventListener('resize', () => {
    const u = qsH('.ah-user.open'); if (u) applyDropdownShift();
    const n = qsH('.ah-pop.open');  if (n) applyPopoverShift();
    pushBody();
  });

  // Idioma (delegado)
  document.addEventListener('click', (e) => {
    const langSwitch = e.target.closest('#ahLangSwitch');
    if (!langSwitch) return;
    const next = (langSwitch.dataset.lang === 'es') ? 'en' : 'es';
    setLang(next);
  }, true);

  // Fallback avatar (por si cambió el header)
  function ensureFallbackAvatar() {
    const header = hdr(); if (!header) return;
    const fallback = header.dataset.fallbackAvatar || '';
    $$H('.ah-chip-avatar, .ah-profile-avatar').forEach(img => {
      if (!img.getAttribute('src')) img.setAttribute('src', fallback);
      img.addEventListener('error', () => { if (fallback) img.src = fallback; }, { once:true });
    });
  }

  // Inicializa en primera carga y en cualquier “navegación SPA”
  function initPass() {
    pushBody();
    try { applyLangUI(localStorage.getItem('ahLang') || 'es'); } catch(_){ applyLangUI('es'); }
    ensureFallbackAvatar();
  }
  document.addEventListener('DOMContentLoaded', initPass);
  window.addEventListener('pageshow', initPass);                 // back/forward cache
  document.addEventListener('turbo:load', initPass);             // Turbo/Hotwire
  document.addEventListener('turbolinks:load', initPass);        // Turbolinks
  document.addEventListener('inertia:after', initPass);          // Inertia.js
  document.addEventListener('livewire:navigated', initPass);     // Livewire navigate
  document.addEventListener('htmx:afterSwap', initPass);         // htmx swaps

  // Y por si el header se reinyecta sin disparar eventos: MutationObserver
  const mo = new MutationObserver(() => initPass());
  mo.observe(document.documentElement, { childList:true, subtree:true });

})();
