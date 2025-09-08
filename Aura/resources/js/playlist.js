/* ==========================================================================
   AURA — PLAYLISTS (UX PRO)
   - Buscador debajo del título + filtros
   - Tile "Nueva playlist" con modal (drag & drop, preview, validaciones)
   - Selección: click en todo el cuadro, Shift-click y long-press (touch)
   - Skeleton loading; orden A–Z / por cantidad
   ========================================================================== */
(() => {
  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  const grid = $('#playlistGrid');
  const search = $('#plSearch');
  const clearSearch = $('#clearSearch');
  const chips = $$('.chip');
  const selectBtn = $('#btnSelectMode');
  const deleteBtn = $('#btnDeleteSelected');

  /* ---------- Skeletons ---------- */
  const addSkeletons = () => {
    if (!grid) return;
    const tpl = $('#skeletonTemplate'); if (!tpl) return;
    const count = Math.max(6, parseInt(grid?.dataset?.count || '0', 10));
    for (let i=0; i<count; i++) grid.append(tpl.content.cloneNode(true));
  };
  const removeSkeletons = () => $$('.skeleton', grid).forEach(el => el.remove());
  addSkeletons();
  const onAllImagesLoaded = () => setTimeout(removeSkeletons, 150);
  const lazyImgs = $$('img[loading="lazy"]', grid);
  let loaded = 0;
  if (lazyImgs.length === 0) onAllImagesLoaded();
  lazyImgs.forEach(img => img.addEventListener('load', () => { loaded++; if (loaded >= lazyImgs.length) onAllImagesLoaded(); }, { once:true }));
  setTimeout(onAllImagesLoaded, 1200);

  /* ---------- Búsqueda ---------- */
  const norm = v => (v||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  const doFilter = () => {
    const q = norm(search.value);
    $$('.tile[href], .tile:not(.tile-create)', grid).forEach(t => {
      const name = norm(t.dataset.name || t.querySelector('.tile-name')?.textContent || '');
      t.style.display = name.includes(q) ? '' : 'none';
    });
  };
  search?.addEventListener('input', doFilter);
  clearSearch?.addEventListener('click', () => { search.value=''; doFilter(); search.focus(); });

  /* ---------- Orden ---------- */
  chips.forEach(ch => ch.addEventListener('click', () => {
    chips.forEach(c => c.classList.remove('is-active'));
    ch.classList.add('is-active');
    sortGrid(ch.dataset.sort);
  }));
  const sortGrid = (mode) => {
    const tiles = $$('.tile[href]', grid);
    const frag = document.createDocumentFragment();
    const arr = tiles.slice();
    if (mode === 'az') arr.sort((a,b) => (a.dataset.name || '').localeCompare(b.dataset.name || ''));
    else if (mode === 'cantidad') arr.sort((a,b) => (+b.dataset.count||0) - (+a.dataset.count||0));
    else return; // recientes = orden del servidor
    arr.forEach(el => frag.appendChild(el)); grid.appendChild(frag);
  };

  /* ---------- Modal crear ---------- */
  const modal = $('#playlistModal');
  const backdrop = $('#playlistModalBackdrop');
  const openModalBtns = [$('#btnOpenPlaylistModal'), $('#btnOpenPlaylistModal2')].filter(Boolean);
  const closeModalBtn = $('#btnClosePlaylistModal');
  const cancelModalBtn = $('#btnCancelPlaylist');
  const form = $('#playlistForm');
  const nameInput = $('#pl_nombre');
  const descInput = $('#pl_desc');
  const coverInput = $('#pl_cover');
  const coverDrop = $('#coverDrop');
  const coverPreview = $('#coverPreview');
  const uploaderHint = $('#uploaderHint');

  const maxMB = parseFloat($('.playlist-page')?.dataset?.maxSizeMb || '5');

  const openModal = () => {
    backdrop.hidden = false; modal.hidden = false;
    document.body.style.overflow = 'hidden';
    nameInput?.focus();
    trapFocus(modal);
  };
  const closeModal = () => {
    backdrop.hidden = true; modal.hidden = true;
    document.body.style.overflow = '';
    releaseFocusTrap();
    form?.reset(); clearPreview(); clearMsgs();
  };
  openModalBtns.forEach(b => b.addEventListener('click', openModal));
  closeModalBtn?.addEventListener('click', closeModal);
  cancelModalBtn?.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', closeModal);

  const clearPreview = () => { if (coverPreview){ coverPreview.src=''; coverPreview.style.display='none'; } if (uploaderHint){ uploaderHint.style.display=''; } };
  const clearMsgs = () => $$('.field-msg').forEach(m => { m.textContent=''; m.classList.remove('msg-error','msg-ok'); });
  const setMsg = (el, msg, ok=false) => { if (!el) return; el.textContent = msg || ''; el.classList.toggle('msg-error', !!msg && !ok); el.classList.toggle('msg-ok', !!msg && ok); };
  const validateImage = (file) => {
    if (!file) return {ok:true};
    const sizeMB = file.size / (1024*1024);
    if (sizeMB > maxMB) return {ok:false, msg:`Archivo supera ${maxMB}MB`};
    if (!/^image\//.test(file.type)) return {ok:false, msg:`Formato no permitido`};
    return {ok:true};
  };

  coverDrop?.addEventListener('dragover', e => { e.preventDefault(); coverDrop.classList.add('is-dragover'); });
  coverDrop?.addEventListener('dragleave', () => coverDrop.classList.remove('is-dragover'));
  coverDrop?.addEventListener('drop', e => {
    e.preventDefault(); coverDrop.classList.remove('is-dragover');
    const file = e.dataTransfer?.files?.[0]; if (!file) return;
    const v = validateImage(file); setMsg($('#coverMsg'), v.msg || '', v.ok);
    if (!v.ok) return; coverInput.files = e.dataTransfer.files; readPreview(file);
  });
  coverDrop?.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); coverInput?.click(); } });
  coverInput?.addEventListener('change', e => {
    const file = e.target.files?.[0];
    const v = validateImage(file); setMsg($('#coverMsg'), v.msg || '', v.ok);
    if (!v.ok) return clearPreview(); readPreview(file);
  });
  const readPreview = (file) => { const r = new FileReader(); r.onload = () => { if (coverPreview){ coverPreview.src = r.result; coverPreview.style.display='block'; } if (uploaderHint){ uploaderHint.style.display='none'; } }; r.readAsDataURL(file); };

  form?.addEventListener('submit', (e) => {
    clearMsgs();
    let ok = true;
    if (!nameInput?.value?.trim() || nameInput.value.trim().length < 3) { setMsg($('#nameMsg'),'Escribe al menos 3 caracteres'); ok=false; }
    if (descInput?.value && descInput.value.length > 300) { setMsg($('#descMsg'),'Máximo 300 caracteres'); ok=false; }
    const f = coverInput?.files?.[0]; if (f){ const v = validateImage(f); if (!v.ok){ setMsg($('#coverMsg'), v.msg); ok=false; } }
    if (!ok) e.preventDefault();
  });

  /* ---------- Selección mejorada ---------- */
  let selectMode = false;
  const setSelectMode = (on) => {
    selectMode = !!on;
    grid.classList.toggle('is-selecting', selectMode);
    $$('.tile[href]', grid).forEach(t => {
      const cb = $('.bulk-check', t);
      if (!cb) return;
      cb.hidden = !selectMode;
      if (!selectMode){ cb.checked = false; t.classList.remove('is-selected'); t.setAttribute('aria-selected','false'); }
    });
    deleteBtn.disabled = !selectMode;
    selectBtn.querySelector('.btn-text').textContent = selectMode ? 'Cancelar' : 'Seleccionar';
    selectBtn.querySelector('i').className = selectMode ? 'fa-regular fa-square-check' : 'fa-regular fa-square';
  };
  selectBtn?.addEventListener('click', () => setSelectMode(!selectMode));

  // Click sobre la tarjeta -> alterna selección (y evita navegar)
  grid?.addEventListener('click', (e) => {
    const tile = e.target.closest('.tile[href]');
    if (!tile) return;

    // El botón de play sigue funcionando
    if (e.target.closest('[data-action="quick-play"]')) return;

    if (selectMode) {
      e.preventDefault();
      toggleTile(tile);
    }
  });

  // Shift+click activa selección y marca
  grid?.addEventListener('mousedown', (e) => {
    const tile = e.target.closest('.tile[href]'); if (!tile) return;
    if (e.shiftKey && !selectMode) { e.preventDefault(); setSelectMode(true); toggleTile(tile, true); }
  });

  // Long-press (touch)
  let pressTimer = null;
  grid?.addEventListener('pointerdown', (e) => {
    const tile = e.target.closest('.tile[href]'); if (!tile) return;
    if (e.pointerType === 'touch') {
      pressTimer = setTimeout(() => { if (!selectMode) setSelectMode(true); toggleTile(tile, true); }, 420);
    }
  });
  const clearPress = () => { if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; } };
  grid?.addEventListener('pointerup', clearPress);
  grid?.addEventListener('pointerleave', clearPress);
  grid?.addEventListener('pointercancel', clearPress);

  function toggleTile(tile, forceCheck=null){
    const cb = $('.bulk-check', tile); if (!cb) return;
    cb.checked = (forceCheck !== null) ? forceCheck : !cb.checked;
    tile.classList.toggle('is-selected', cb.checked);
    tile.setAttribute('aria-selected', cb.checked ? 'true' : 'false');
  }

  // Eliminar seleccionadas (frontend)
  deleteBtn?.addEventListener('click', () => {
    if (!selectMode) return;
    const selected = $$('.tile[href]', grid).filter(t => $('.bulk-check', t)?.checked);
    if (!selected.length) return;
    selected.forEach(t => { t.style.transition='transform .22s ease, opacity .22s ease'; t.style.transform='scale(.98)'; t.style.opacity='0'; setTimeout(()=>t.remove(), 200); });
    setSelectMode(false);
  });

  /* ---------- Quick play ---------- */
  grid?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="quick-play"]'); if (!btn) return;
    e.preventDefault();
    const tile = e.target.closest('.tile[href]'); if (!tile) return;
    const name = tile.querySelector('.tile-name')?.textContent?.trim();
    const id = tile.dataset.id; const cover = tile.querySelector('img')?.src || '';
    window.dispatchEvent(new CustomEvent('aura:queue:play', { detail:{ type:'playlist', id, name, cover } }));
    btn.style.transform='scale(0.92)'; setTimeout(()=>btn.style.transform='', 120);
  });

  /* ---------- Focus trap (modal) ---------- */
  let lastActive = null;
  function trapFocus(modalEl){
    lastActive = document.activeElement;
    const FOCUSABLE = 'a,button,input,textarea,select,[tabindex]:not([tabindex="-1"])';
    const tabHandler = (e) => {
      const foc = $$(FOCUSABLE, modalEl).filter(el => !el.disabled && !el.getAttribute('aria-hidden'));
      if (!foc.length) return;
      const first = foc[0], last = foc[foc.length-1];
      if (e.key === 'Tab') {
        if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
        else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
      }
      if (e.key === 'Escape') { if (!modal.hidden) closeModal(); }
    };
    modalEl.__trapHandler = tabHandler; modalEl.addEventListener('keydown', tabHandler);
  }
  function releaseFocusTrap(){ if (lastActive){ lastActive.focus({preventScroll:true}); lastActive=null; } modal?.removeEventListener('keydown', modal.__trapHandler); }

  /* ---------- Atajos ---------- */
  window.addEventListener('keydown', (e) => {
    if (e.target.matches('input,textarea')) return;
    if (e.key.toLowerCase() === 'n') openModal();
    if (e.key.toLowerCase() === 's') setSelectMode(!selectMode);
    if (e.key === 'Delete' && selectMode && !deleteBtn.disabled) deleteBtn.click();
    if (e.key === 'Escape') { if (!modal.hidden) closeModal(); }
  });

})();
