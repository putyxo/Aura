/* resources/js/playlist.js */
(() => {
  // ---------- HELPERS BÁSICOS ----------
  const $  = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  const root      = $('#axplRoot');
  if (!root) return;

  const grid      = $('#axplGrid');
  const CSRF      = (document.querySelector('meta[name="csrf-token"]')?.content) || '';
  const maxMB     = Number(root.dataset.axplMaxMb || 5);
  const maxBytes  = maxMB * 1024 * 1024;

  const searchIn  = $('#axplSearch');
  const clearBtn  = $('#axplClearSearch');
  const sortChips = $$('.axpl-chip');

  const openBtn1  = $('#axplOpenModal');
  const openBtn2  = $('#axplOpenModal2');

  const modal     = $('#axplModal');
  const backdrop  = $('#axplModalBackdrop');
  const modalTitle= $('#axplModalTitle');
  const form      = $('#axplForm');

  const fieldName = $('#axpl_nombre');
  const fieldDesc = $('#axpl_desc');
  const coverInput= $('#axpl_cover');
  const coverDrop = $('#axplCoverDrop');
  const coverPrev = $('#axplCoverPreview');
  const coverHint = $('#axplUploaderHint');
  const coverOvly = $('#axplUploaderOverlay');

  const btnClose  = $('#axplCloseModal');
  const btnCancel = $('#axplCancel');

  const selectBtn = $('#axplSelectMode');
  const deleteBtn = $('#axplDeleteSelected');

  const toastFromServer = $('.axpl-toast'); // por si vino de sesión

  const tiles = () => $$('.axpl-tile[data-id]', grid);

  // ---------- TOAST ----------
  function toast(msg, type='ok') {
    const t = document.createElement('div');
    t.className = `axpl axpl-toast ${type==='ok' ? 'axpl-toast-ok' : (type==='err' ? 'axpl-toast-err' : 'axpl-toast-info')}`;
    t.setAttribute('role','status');
    t.setAttribute('aria-live','polite');
    t.innerHTML = `<i class="fa-solid ${type==='ok'?'fa-circle-check':(type==='err'?'fa-circle-xmark':'fa-circle-info')}"></i><span>${msg}</span>`;
    root.appendChild(t);
    requestAnimationFrame(() => t.classList.add('is-shown'));
    setTimeout(() => { t.classList.remove('is-shown'); setTimeout(()=> t.remove(), 250); }, 3000);
  }
  if (toastFromServer) setTimeout(() => { toastFromServer.remove(); }, 1800);

  // ---------- CONTADOR EN GRID ----------
  function updateCountUI() {
    const n = tiles().length;
    grid?.setAttribute('data-count', String(n));
    let empty = $('.axpl-empty-state', grid);
    if (!n) {
      if (!empty) {
        const el = document.createElement('div');
        el.className = 'axpl-tile axpl-empty-state';
        el.style.gridColumn = '1/-1';
        el.innerHTML = `
          <i class="fa-solid fa-music"></i>
          <h3>Aún no tienes playlists</h3>
          <p>¡Crea la primera para comenzar! ✨</p>
        `;
        grid.appendChild(el);
      }
    } else if (empty) empty.remove();
  }

  // ---------- BÚSQUEDA ----------
  function applySearch() {
    const q = (searchIn?.value || '').toLowerCase().trim();
    tiles().forEach(t => {
      const hay = t.dataset.name || '';
      t.style.display = hay.includes(q) ? '' : 'none';
    });
  }
  searchIn?.addEventListener('input', applySearch);
  clearBtn?.addEventListener('click', () => { if (!searchIn) return; searchIn.value=''; applySearch(); searchIn.focus(); });

  // ---------- ORDEN ----------
  function setActiveChip(btn) {
    sortChips.forEach(c => c.classList.remove('is-active'));
    btn?.classList.add('is-active');
  }
  const sorters = {
    recientes(a,b){ return 0; },
    az(a,b){ return (a.dataset.name||'').localeCompare(b.dataset.name||'', 'es', {sensitivity:'base'}); },
    cantidad(a,b){ return (+b.dataset.count||0) - (+a.dataset.count||0); },
  };
  function applySort(type='recientes') {
    const items = tiles().filter(el => el.style.display !== 'none');
    if (!items.length) return;
    const sorter = sorters[type] || sorters.recientes;
    const createBtn = $('#axplOpenModal2');
    items.sort(sorter).forEach(el => grid.appendChild(el));
    if (createBtn) grid.insertBefore(createBtn, grid.firstElementChild);
  }
  sortChips.forEach(chip => chip.addEventListener('click', () => {
    setActiveChip(chip);
    applySort(chip.dataset.sort || 'recientes');
  }));

  // ---------- MODAL ----------
  function openModal(mode='create', data=null) {
    form.reset();
    clearValidation();
    removeMethodOverride();

    if (mode === 'create') {
      modalTitle.textContent = 'Añadir Playlist';
      form.action = form.getAttribute('action') || '/playlists';
      clearCoverPreview();
    } else {
      modalTitle.textContent = 'Editar Playlist';
      if (!data?.id) return;
      form.action = `/playlists/${data.id}`;
      addMethodOverride('PUT');
      fieldName.value = data.nombre || '';
      fieldDesc.value = data.descripcion || '';
      if (data.cover) setCoverPreview(data.cover); else clearCoverPreview();
    }

    backdrop.hidden = false;
    modal.hidden = false;
    modal.setAttribute('data-mode', mode);
    modal.setAttribute('data-id', data?.id || '');
    document.documentElement.classList.add('axpl-modal-open');
    fieldName.focus();
  }
  function closeModal() {
    modal.hidden = true;
    backdrop.hidden = true;
    document.documentElement.classList.remove('axpl-modal-open');
    clearValidation();
  }
  openBtn1?.addEventListener('click', () => openModal('create'));
  openBtn2?.addEventListener('click', () => openModal('create'));
  $('#axplCloseModal')?.addEventListener('click', closeModal);
  $('#axplCancel')?.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.hidden) closeModal(); });

  // ---------- VALIDACIÓN ----------
  function clearValidation() {
    $('#axplNameMsg').textContent  = '';
    $('#axplCoverMsg').textContent = '';
    $('#axplDescMsg').textContent  = '';
    $('#axplFieldNombre')?.classList.remove('is-error');
    $('.axpl-cover-field')?.classList.remove('is-error');
    $('.axpl-field-desc')?.classList?.remove?.('is-error');
  }
  function validateForm() {
    clearValidation();
    let ok = true;
    const name = (fieldName.value || '').trim();
    if (name.length < 3) {
      $('#axplNameMsg').textContent = 'El nombre debe tener al menos 3 caracteres.';
      $('#axplFieldNombre')?.classList.add('is-error'); ok = false;
    }
    const file = coverInput.files?.[0];
    if (file) {
      if (!/^image\//.test(file.type)) {
        $('#axplCoverMsg').textContent = 'El archivo debe ser una imagen (PNG/JPG).';
        $('.axpl-cover-field')?.classList.add('is-error'); ok = false;
      } else if (file.size > maxBytes) {
        $('#axplCoverMsg').textContent = `La imagen supera el máximo de ${maxMB} MB.`;
        $('.axpl-cover-field')?.classList.add('is-error'); ok = false;
      }
    }
    return ok;
  }

  // ---------- MÉTODO OVERRIDE (PUT) ----------
  function addMethodOverride(method='PUT') {
    let m = form.querySelector('input[name="_method"]');
    if (!m) {
      m = document.createElement('input');
      m.type = 'hidden'; m.name = '_method'; form.appendChild(m);
    }
    m.value = method;
  }
  function removeMethodOverride() {
    const m = form.querySelector('input[name="_method"]');
    if (m) m.remove();
  }

  // ---------- PORTADA (DND + PREVIEW) ----------
  function setCoverPreview(src) {
    coverPrev.src = src; coverPrev.style.display = 'block';
    coverHint.style.display = 'none'; coverDrop.classList.add('has-preview');
  }
  function clearCoverPreview() {
    coverPrev.removeAttribute('src'); coverPrev.style.display = 'none';
    coverHint.style.display = ''; coverDrop.classList.remove('has-preview');
  }
  const readFileToDataURL = (file) => new Promise((res, rej) => {
    const fr = new FileReader();
    fr.onload = () => res(fr.result);
    fr.onerror = () => rej(new Error('No se pudo leer el archivo'));
    fr.readAsDataURL(file);
  });

  coverDrop?.addEventListener('click', () => coverInput?.click());
  coverDrop?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); coverInput?.click(); }
  });
  coverDrop?.addEventListener('dragover', (e) => { e.preventDefault(); coverDrop.classList.add('is-dragover'); coverOvly.style.display = 'flex'; });
  coverDrop?.addEventListener('dragleave', () => { coverDrop.classList.remove('is-dragover'); coverOvly.style.display = 'none'; });
  coverDrop?.addEventListener('drop', async (e) => {
    e.preventDefault(); coverDrop.classList.remove('is-dragover'); coverOvly.style.display = 'none';
    const file = e.dataTransfer.files?.[0]; if (!file) return;
    if (!/^image\//.test(file.type)) { $('#axplCoverMsg').textContent = 'El archivo debe ser una imagen (PNG/JPG).'; $('.axpl-cover-field')?.classList.add('is-error'); return; }
    if (file.size > maxBytes) { $('#axplCoverMsg').textContent = `La imagen supera el máximo de ${maxMB} MB.`; $('.axpl-cover-field')?.classList.add('is-error'); return; }
    const dt = new DataTransfer(); dt.items.add(file); coverInput.files = dt.files;
    const dataURL = await readFileToDataURL(file).catch(()=>null); if (dataURL) setCoverPreview(dataURL);
  });
  coverInput?.addEventListener('change', async () => {
    const file = coverInput.files?.[0];
    if (!file) { clearCoverPreview(); return; }
    if (!/^image\//.test(file.type)) { $('#axplCoverMsg').textContent = 'El archivo debe ser una imagen (PNG/JPG).'; $('.axpl-cover-field')?.classList.add('is-error'); return; }
    if (file.size > maxBytes) { $('#axplCoverMsg').textContent = `La imagen supera el máximo de ${maxMB} MB.`; $('.axpl-cover-field')?.classList.add('is-error'); return; }
    const dataURL = await readFileToDataURL(file).catch(()=>null); if (dataURL) setCoverPreview(dataURL);
  });

  // ---------- SUBMIT (AJAX con fallback) ----------
  form?.addEventListener('submit', async function onSubmit(e) {
    e.preventDefault();
    if (!validateForm()) return;

    const isEdit = (modal.getAttribute('data-mode') === 'edit');

    const fd = new FormData(form);
    try {
      const r = await fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd });
      const contentType = r.headers.get('content-type') || '';
      if (!r.ok || !contentType.includes('application/json')) {
        form.removeEventListener('submit', onSubmit);
        form.submit(); return;
      }
      const data = await r.json();
      if (!data || !data.id) { toast('Guardado, recargando…', 'info'); setTimeout(()=> location.reload(), 300); return; }
      if (isEdit) { updateTile(data); toast('Playlist actualizada ✅', 'ok'); }
      else { addTile(data); toast('Playlist creada ✅', 'ok'); }
      closeModal();
      applySearch();
      applySort(($('.axpl-chip.is-active')?.dataset.sort) || 'recientes');
    } catch {
      form.removeEventListener('submit', onSubmit);
      form.submit();
    }
  });

  // ---------- CREAR / ACTUALIZAR TILES ----------
  function tileTemplate(pl) {
    const id    = pl.id;
    const name  = pl.nombre || 'Sin nombre';
    const desc  = pl.descripcion || '';
    const cover = pl.cover_url || '';
    const count = Number(pl.canciones_count || 0);

    const wrap = document.createElement('div');
    wrap.className = 'axpl-tile';
    wrap.title = name;
    wrap.dataset.name  = name.toLowerCase();
    wrap.dataset.count = String(count);
    wrap.dataset.id    = String(id);

    wrap.innerHTML = `
      <a href="/playlists/${id}" class="axpl-tile-link" aria-label="Abrir ${escapeHtml(name)}"></a>

      <div class="axpl-tile-cover">
        <div class="axpl-cover-badge" aria-hidden="true"><i class="fa-solid fa-list-music"></i></div>
        ${cover
          ? `<img src="${cover}" alt="Portada de ${escapeHtml(name)}" width="260" height="260" loading="lazy" decoding="async">`
          : `<div class="axpl-cover-placeholder">Sin portada</div>`
        }

        <button type="button"
                class="axpl-pencil"
                data-id="${id}"
                data-nombre="${escapeAttr(name)}"
                data-descripcion="${escapeAttr(desc)}"
                data-cover="${escapeAttr(cover)}">
          <i class="fa-solid fa-pen"></i>
        </button>

        <button type="button" class="axpl-play-btn" data-action="quick-play" aria-label="Reproducir ${escapeHtml(name)}">
          <i class="fa-solid fa-play"></i>
        </button>

        <div class="axpl-selected-mark" aria-hidden="true"><i class="fa-solid fa-check"></i></div>
        <input type="checkbox" class="axpl-bulk-check" aria-label="Seleccionar ${escapeAttr(name)}" hidden>
      </div>

      <div class="axpl-tile-name">${escapeHtml(name)}</div>
      <div class="axpl-tile-count">${count} canciones</div>
    `;
    return wrap;
  }

  function addTile(pl) {
    const createBtn = $('#axplOpenModal2');
    const tile = tileTemplate(pl);
    if (createBtn?.nextSibling) grid.insertBefore(tile, createBtn.nextSibling);
    else grid.appendChild(tile);
    bindTile(tile);
    updateCountUI();
  }

  function updateTile(pl) {
    const t = tiles().find(x => String(x.dataset.id) === String(pl.id));
    if (!t) { addTile(pl); return; }
    const name  = pl.nombre || 'Sin nombre';
    const cover = pl.cover_url || '';
    const count = Number(pl.canciones_count || t.dataset.count || 0);
    t.title = name; t.dataset.name = name.toLowerCase(); t.dataset.count = String(count);
    $('.axpl-tile-name', t).textContent   = name;
    $('.axpl-tile-count', t).textContent  = `${count} canciones`;
    const coverWrap = $('.axpl-tile-cover', t);
    if (cover) {
      const img = coverWrap.querySelector('img');
      if (img) img.src = cover;
      else {
        coverWrap.querySelector('.axpl-cover-placeholder')?.remove();
        const ni = document.createElement('img');
        ni.src = cover; ni.alt = `Portada de ${name}`;
        ni.width = 260; ni.height = 260; ni.loading = 'lazy'; ni.decoding='async';
        coverWrap.insertBefore(ni, coverWrap.firstChild);
      }
    }
    const pencil = $('.axpl-pencil', t);
    if (pencil) { pencil.dataset.nombre = name; pencil.dataset.descripcion = pl.descripcion || ''; pencil.dataset.cover = cover || ''; }
  }

  const escapeHtml = (s) => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const escapeAttr = (s) => escapeHtml(s);

  // ---------- EDITAR / QUICK PLAY / SELECCIÓN ----------
  function bindTile(tile) {
    const pencil = $('.axpl-pencil', tile);
    const quick  = $('.axpl-play-btn', tile);
    const link   = $('.axpl-tile-link', tile);
    const check  = $('.axpl-bulk-check', tile);

    pencil?.addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation();
      openModal('edit', {
        id: tile.dataset.id,
        nombre: pencil.dataset.nombre || '',
        descripcion: pencil.dataset.descripcion || '',
        cover: pencil.dataset.cover || ''
      });
    });

    quick?.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); quickPlay(tile.dataset.id, tile); });

    tile.addEventListener('click', (e) => {
      if (!root.classList.contains('axpl-select-mode')) return;
      if (e.target.closest('.axpl-pencil') || e.target.closest('.axpl-play-btn') || e.target.closest('.axpl-tile-link')) return;
      toggleSelectTile(tile, check);
    });

    link?.addEventListener('click', (e) => { if (root.classList.contains('axpl-select-mode')) e.preventDefault(); });
  }
  tiles().forEach(bindTile);

  // ---------- MODO SELECCIÓN + BORRADO ----------
  function setSelectMode(on) {
    const ico = selectBtn?.querySelector('i');
    const txt = selectBtn?.querySelector('.axpl-btn-text');
    if (on) {
      root.classList.add('axpl-select-mode');
      $$('.axpl-bulk-check', grid).forEach(cb => cb.hidden = false);
      selectBtn?.classList.add('is-on');
      selectBtn?.setAttribute('aria-pressed', 'true');
      if (ico) { ico.className = 'fa-solid fa-check-double'; }
      if (txt) txt.replaceChildren('Seleccionando…');
      deleteBtn.disabled = true;
    } else {
      root.classList.remove('axpl-select-mode');
      $$('.axpl-bulk-check', grid).forEach(cb => { cb.checked=false; cb.hidden = true; });
      tiles().forEach(t => t.classList.remove('is-selected'));
      selectBtn?.classList.remove('is-on');
      selectBtn?.setAttribute('aria-pressed', 'false');
      if (ico) { ico.className = 'fa-regular fa-square'; }
      if (txt) txt.replaceChildren('Seleccionar');
      deleteBtn.disabled = true;
    }
  }

  function toggleSelectTile(tile, checkEl) {
    checkEl.checked = !checkEl.checked;
    tile.classList.toggle('is-selected', checkEl.checked);
    const hasAny = $$('.axpl-bulk-check', grid).some(cb => cb.checked);
    deleteBtn.disabled = !hasAny;
  }

  selectBtn?.addEventListener('click', () => setSelectMode(!root.classList.contains('axpl-select-mode')));

  deleteBtn?.addEventListener('click', async () => {
    const ids = tiles().filter(t => $('.axpl-bulk-check', t)?.checked).map(t => t.dataset.id);
    if (!ids.length) return;
    if (!confirm(`¿Eliminar ${ids.length} playlist(s)? Esta acción no se puede deshacer.`)) return;

    let okCount = 0;
    for (const id of ids) {
      try {
        const r = await fetch(`/playlists/${id}`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          body: new URLSearchParams({ _method: 'DELETE' })
        });
        if (r.ok) {
          const tile = tiles().find(t => String(t.dataset.id) === String(id));
          tile?.remove(); okCount++;
        }
      } catch {}
    }
    if (okCount) toast(`Eliminadas ${okCount} playlist(s) ✅`, 'ok');
    else toast('No se pudieron eliminar las playlists', 'err');

    setSelectMode(false);
    updateCountUI();
    applySearch();
  });

  // ---------- QUICK PLAY ----------
  async function quickPlay(playlistId, tileEl) {
    try {
      const r = await fetch(`/playlists/${playlistId}`, { headers: { 'Accept': 'text/html' } });
      const html = await r.text();
      const doc  = new DOMParser().parseFromString(html, 'text/html');
      const btns = Array.from(doc.querySelectorAll('.cancion-item'));

      const DEF_COVER = '/img/default-cancion.png';
      const songs = btns.map(b => ({
        id      : b.dataset.id || crypto.randomUUID(),
        albumId : b.dataset.albumId || b.dataset.albumid || b.dataset.album_id || null,
        src     : b.dataset.src || '',
        title   : b.dataset.title || 'Sin título',
        artist  : b.dataset.artist || 'Artista',
        cover   : b.dataset.cover || DEF_COVER,
        duration: b.dataset.duration || '--:--'
      })).filter(s => s.src);

      if (!songs.length) { toast('Esta playlist no tiene canciones aún', 'info'); return; }

      if (window.AuraQueue?.setQueue) {
        window.AuraQueue.setQueue(songs, { source: { type:'playlist', id: playlistId }, shuffle:false, persist:true });
        if (window.AuraQueue?.externalPlay) window.AuraQueue.externalPlay(songs[0]);
        else if (window.AuraPlayer?.play)   window.AuraPlayer.play(songs[0]);
      } else if (window.AuraPlayer?.play) {
        window.AuraPlayer.play(songs[0]);
      }

      const btn = tileEl?.querySelector('.axpl-play-btn i');
      if (btn) {
        btn.classList.remove('fa-play');
        btn.classList.add('fa-circle-notch','fa-spin');
        setTimeout(() => {
          btn.classList.remove('fa-circle-notch','fa-spin');
          btn.classList.add('fa-play');
        }, 900);
      }
    } catch {
      toast('No se pudo reproducir la playlist', 'err');
    }
  }

  // Delegación para lápiz añadido dinámicamente
  grid.addEventListener('click', (e) => {
    const pen = e.target.closest?.('.axpl-pencil');
    if (!pen) return;
    e.preventDefault();
    openModal('edit', {
      id: pen.dataset.id,
      nombre: pen.dataset.nombre || '',
      descripcion: pen.dataset.descripcion || '',
      cover: pen.dataset.cover || ''
    });
  });

  // ---------- INIT ----------
  updateCountUI();
  applySearch();
  applySort(($('.axpl-chip.is-active')?.dataset.sort) || 'recientes');
})();
