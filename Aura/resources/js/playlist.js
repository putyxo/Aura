/* AURA — axpl.playlists.js (aislado en #axplRoot) */
(() => {
  'use strict';

  const root = document.getElementById('axplRoot');
  if (!root) return;

  const $  = (sel, p = root) => p.querySelector(sel);
  const $$ = (sel, p = root) => Array.from(p.querySelectorAll(sel));

  /* ---------- MODAL ---------- */
  const backdrop   = $('#axplModalBackdrop');
  const modal      = $('#axplModal');
  const form       = $('#axplForm');
  const btnOpen1   = $('#axplOpenModal');
  const btnOpen2   = $('#axplOpenModal2');
  const btnClose   = $('#axplCloseModal');
  const btnCancel  = $('#axplCancel');
  const btnSubmit  = $('#axplSubmit');
  const modalTitle = $('#axplModalTitle');

  const fieldNombre = $('#axpl_nombre');
  const fieldDesc   = $('#axpl_desc');
  const nameMsg     = $('#axplNameMsg');

  const drop     = $('#axplCoverDrop');
  const input    = $('#axpl_cover');
  const preview  = $('#axplCoverPreview');
  const overlay  = $('#axplUploaderOverlay');
  const hint     = $('#axplUploaderHint');
  const coverMsg = $('#axplCoverMsg');

  const openModal = (mode = 'create', payload = null) => {
    if (mode === 'create') {
      modalTitle.textContent = 'Añadir Playlist';
      form.removeAttribute('data-editing');
      fieldNombre.value = '';
      fieldDesc.value   = '';
      clearCover();
    } else if (mode === 'edit' && payload) {
      modalTitle.textContent = 'Editar Playlist';
      form.setAttribute('data-editing', payload.id);
      fieldNombre.value = payload.nombre || '';
      fieldDesc.value   = payload.descripcion || '';
      if (payload.cover) setPreview(payload.cover, true); else clearCover();
    }

    backdrop.hidden = false;
    modal.hidden    = false;
    root.classList.add('axpl--modal-open');
    fieldNombre.focus();
  };

  const closeModal = () => {
    backdrop.hidden = true;
    modal.hidden    = true;
    root.classList.remove('axpl--modal-open');
  };

  btnOpen1 && btnOpen1.addEventListener('click', () => openModal('create'));
  btnOpen2 && btnOpen2.addEventListener('click', () => openModal('create'));
  btnClose && btnClose.addEventListener('click', closeModal);
  btnCancel && btnCancel.addEventListener('click', closeModal);
  backdrop && backdrop.addEventListener('click', closeModal);
  window.addEventListener('keydown', e => {
    if (!modal.hidden && e.key === 'Escape') closeModal();
  });

  /* ---------- DROPZONE / PREVIEW ---------- */
  const maxSizeMB = parseFloat(root.dataset.axplMaxMb || '5');

  const setPreview = (src, isUrl = false) => {
    preview.src = src;
    preview.style.display = 'block';
    drop.classList.add('has-image');
    hint.style.display = 'none';
    coverMsg.textContent = '';
    if (isUrl) preview.removeAttribute('data-blob');
  };

  const clearCover = () => {
    preview.removeAttribute('src');
    preview.style.display = 'none';
    drop.classList.remove('has-image');
    hint.style.display = '';
    coverMsg.textContent = '';
  };

  const handleFile = (file) => {
    if (!file) return;
    const okTypes = ['image/jpeg','image/jpg','image/png','image/webp','image/gif'];
    if (!okTypes.includes(file.type)) {
      coverMsg.textContent = 'Formato no válido. Usa PNG/JPG.';
      return;
    }
    if (file.size > maxSizeMB * 1024 * 1024) {
      coverMsg.textContent = `La imagen supera ${maxSizeMB}MB.`;
      return;
    }
    const reader = new FileReader();
    reader.onload = e => setPreview(e.target.result);
    reader.readAsDataURL(file);
  };

  drop.addEventListener('click', () => input.click());
  drop.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
  });

  input.addEventListener('change', (e) => handleFile(e.target.files[0]));
  drop.addEventListener('dragover', (e) => {
    e.preventDefault();
    drop.classList.add('dragover');
    overlay.style.opacity = '1';
  });
  drop.addEventListener('dragleave', () => {
    drop.classList.remove('dragover');
    overlay.style.opacity = '0';
  });
  drop.addEventListener('drop', (e) => {
    e.preventDefault();
    drop.classList.remove('dragover');
    overlay.style.opacity = '0';
    const file = e.dataTransfer.files && e.dataTransfer.files[0];
    if (file) { input.files = e.dataTransfer.files; handleFile(file); }
  });

  /* ---------- VALIDACIÓN ---------- */
  const validate = () => {
    let ok = true;
    nameMsg.textContent = '';
    fieldNombre.removeAttribute('aria-invalid');

    const name = fieldNombre.value.trim();
    if (name.length < 3) {
      nameMsg.textContent = 'El nombre debe tener al menos 3 caracteres.';
      fieldNombre.setAttribute('aria-invalid','true');
      ok = false;
    }
    return ok;
  };

  fieldNombre.addEventListener('input', validate);

  /* ---------- SUBMIT ---------- */
  form.addEventListener('submit', (e) => {
    if (!validate()) {
      e.preventDefault();
      fieldNombre.focus();
      return;
    }
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Guardando…';
  });

  /* ---------- EDITAR (lápiz) ---------- */
  $$('.axpl-pencil').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      e.preventDefault();
      const payload = {
        id: btn.dataset.id,
        nombre: btn.dataset.nombre || '',
        descripcion: btn.dataset.descripcion || '',
        cover: btn.dataset.cover || ''
      };
      openModal('edit', payload);
    });
  });

  /* ---------- BÚSQUEDA EN GRID ---------- */
  const searchInput = $('#axplSearch');
  const clearBtn    = $('#axplClearSearch');
  const grid        = $('#axplGrid');

  const applySearch = () => {
    const q = (searchInput.value || '').trim().toLowerCase();
    $$('.axpl-tile', grid).forEach(card => {
      const name = (card.dataset.name || '').toLowerCase();
      card.style.display = name.includes(q) ? '' : 'none';
    });
  };
  searchInput && searchInput.addEventListener('input', applySearch);
  clearBtn && clearBtn.addEventListener('click', () => {
    searchInput.value=''; applySearch(); searchInput.focus();
  });

})();
