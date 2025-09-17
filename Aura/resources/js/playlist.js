// resources/js/playlist.js
document.addEventListener('DOMContentLoaded', () => {
  const $  = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  const root = $('#axplRoot');
  if (!root) return;

  const grid            = $('#axplGrid');
  const search          = $('#axplSearch');
  const clearSearch     = $('#axplClearSearch');
  const chips           = $$('.axpl-chip');
  const selectBtn       = $('#axplSelectMode');
  const deleteBtn       = $('#axplDeleteSelected');
  const selectInd       = $('#axplSelectIndicator');
  const selCountEl      = $('#axplSelCount');
  const openModalBtns   = [$('#axplOpenModal'), $('#axplOpenModal2')].filter(Boolean);

  const modal           = $('#axplModal');
  const backdrop        = $('#axplModalBackdrop');
  const form            = $('#axplForm');
  const modalTitle      = $('#axplModalTitle');
  const closeModalBtn   = $('#axplCloseModal');
  const cancelBtn       = $('#axplCancel');
  const nameInput       = $('#axpl_nombre');
  const descInput       = $('#axpl_desc');
  const coverInput      = $('#axpl_cover');
  const dropZone        = $('#axplCoverDrop');
  const coverPrev       = $('#axplCoverPreview');
  const coverHint       = $('#axplUploaderHint');
  const coverOverlay    = $('#axplUploaderOverlay');
  const nameMsg         = $('#axplNameMsg');
  const coverMsg        = $('#axplCoverMsg');

  const toast           = $('#axplToast');
  const CSRF            = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const UID             = root.dataset.userId || 'guest';
  const MAX_MB          = Number(root.dataset.axplMaxMb || '5');
  const BULK_DELETE_URL = root.dataset.bulkDeleteUrl || '/playlists/bulk-delete';
  const UPDATE_BASE_URL = root.dataset.updateBaseUrl || '/playlists';
  const SHOW_BASE_URL   = root.dataset.showBaseUrl || '/playlists';

  const STORE_SEARCH    = `axpl_search_v1_${UID}`;

  /* ---------- Helpers ---------- */
  const tiles = () => $$('.axpl-tile', grid).filter(t => !t.classList.contains('axpl-empty-state'));
  const selectedTiles = () => tiles().filter(t => t.classList.contains('is-selected'));

  function showToast(msg, type='ok') {
    if (!toast) return;
    toast.className = 'axpl-toast';
    if (type === 'err') toast.classList.add('axpl-toast-err');
    else if (type === 'info') toast.classList.add('axpl-toast-info');
    else toast.classList.add('axpl-toast-ok');
    toast.innerHTML = `<i class="fa-solid ${type==='err'?'fa-circle-xmark': type==='info'?'fa-circle-info':'fa-circle-check'}"></i><span>${msg}</span>`;
    toast.hidden = false;
    requestAnimationFrame(()=> toast.classList.add('is-shown'));
    setTimeout(() => {
      toast.classList.remove('is-shown');
      setTimeout(() => (toast.hidden = true), 280);
    }, 2600);
  }

  function setSelectMode(on) {
    root.classList.toggle('axpl-select-mode', on);
    selectBtn?.classList.toggle('is-on', on);
    selectBtn?.setAttribute('aria-pressed', on ? 'true' : 'false');
    if (on) {
      selectInd?.removeAttribute('hidden');
    } else {
      selectInd?.setAttribute('hidden', '');
      // Limpia selección
      selectedTiles().forEach(t => t.classList.remove('is-selected'));
      updateSelectionUI();
    }
  }

  function updateSelectionUI() {
    const n = selectedTiles().length;
    selCountEl && (selCountEl.textContent = String(n));
    deleteBtn && (deleteBtn.disabled = n === 0);
  }

  function applySearch() {
    const q = (search?.value || '').trim().toLowerCase();
    tiles().forEach(t => {
      const hay = t.dataset.name || '';
      t.style.display = hay.includes(q) ? '' : 'none';
    });
  }

  function sortBy(kind) {
    const list = tiles();
    const frag = document.createDocumentFragment();
    let sorter = (a,b)=>0;

    if (kind === 'az') {
      sorter = (a,b) => (a.dataset.name || '').localeCompare(b.dataset.name || '');
    } else if (kind === 'cantidad') {
      sorter = (a,b) => (Number(b.dataset.count||0) - Number(a.dataset.count||0));
    } else {
      // recientes: dejamos el orden actual (DOM order), no reordenamos
      return;
    }
    list.sort(sorter).forEach(el => frag.appendChild(el));
    grid.appendChild(frag);
  }

  function fileToPreview(file) {
    return new Promise((res) => {
      const reader = new FileReader();
      reader.onload = e => res(e.target.result);
      reader.readAsDataURL(file);
    });
  }

  function resetFormState() {
    form.action = form.getAttribute('action') || `${UPDATE_BASE_URL}`;
    form.querySelector('input[name="_method"]')?.remove();
    form.reset();
    coverPrev.src = '';
    coverPrev.style.display = 'none';
    dropZone.classList.remove('has-preview', 'is-dragover');
    nameMsg.textContent = '';
    coverMsg.textContent = '';
  }

  function openModal({edit=false, id=null, nombre='', descripcion='', cover=''}) {
    resetFormState();
    modalTitle.textContent = edit ? 'Editar Playlist' : 'Añadir Playlist';
    nameInput.value = nombre || '';
    descInput.value = descripcion || '';
    if (cover) {
      coverPrev.src = cover;
      coverPrev.style.display = 'block';
      dropZone.classList.add('has-preview');
    }
    if (edit && id) {
      form.action = `${UPDATE_BASE_URL}/${id}`;
      const m = document.createElement('input');
      m.type = 'hidden'; m.name = '_method'; m.value = 'PUT';
      form.appendChild(m);
    }
    modal.hidden = false;
    backdrop.hidden = false;
    document.documentElement.classList.add('axpl-modal-open');
    nameInput.focus();
  }

  function closeModal() {
    modal.hidden = true;
    backdrop.hidden = true;
    document.documentElement.classList.remove('axpl-modal-open');
  }

  /* ---------- Bind tiles ---------- */
  function bindTile(t) {
    const link   = $('.axpl-tile-link', t);
    const pencil = $('.axpl-pencil', t);
    const play   = $('.axpl-play-btn', t);

    // Toggle selección en modo select
    t.addEventListener('click', (e) => {
      const inSelect = root.classList.contains('axpl-select-mode');
      const targetIsControl = e.target.closest('.axpl-pencil, .axpl-play-btn');
      if (!inSelect || targetIsControl) return;
      e.preventDefault();
      t.classList.toggle('is-selected');
      updateSelectionUI();
    });

    // Edición
    pencil?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const data = {
        edit:true,
        id: t.dataset.id,
        nombre: pencil.dataset.nombre || '',
        descripcion: pencil.dataset.descripcion || '',
        cover: pencil.dataset.cover || ''
      };
      openModal(data);
    });

    // Quick Play (fallback: abrir show)
    play?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const plid = t.dataset.id;
      // Si tienes una API para reproducir playlist, llámala aquí:
      if (window.AuraQueue?.playPlaylist) {
        window.AuraQueue.playPlaylist({ id: Number(plid) });
      } else if (window.AuraPlayer?.openPlaylist) {
        window.AuraPlayer.openPlaylist({ id: Number(plid) });
      } else {
        // fallback: ir a la vista
        window.location.href = `${SHOW_BASE_URL}/${plid}`;
      }
    });

    // Evitar navegación del link cuando hay modo selección
    link?.addEventListener('click', (e) => {
      if (root.classList.contains('axpl-select-mode')) e.preventDefault();
    });
  }

  tiles().forEach(bindTile);

  /* ---------- Search + Sort ---------- */
  const savedQ = localStorage.getItem(STORE_SEARCH);
  if (savedQ != null && search) { search.value = savedQ; }
  applySearch();

  search?.addEventListener('input', () => {
    applySearch();
    localStorage.setItem(STORE_SEARCH, search.value || '');
  });
  clearSearch?.addEventListener('click', () => {
    search.value=''; applySearch(); search.focus();
    localStorage.removeItem(STORE_SEARCH);
  });
  chips.forEach(c => c.addEventListener('click', () => {
    chips.forEach(x => x.classList.remove('is-active'));
    c.classList.add('is-active');
    sortBy(c.dataset.sort);
  }));

  /* ---------- Select mode + Bulk delete ---------- */
  selectBtn?.addEventListener('click', () => {
    const now = !root.classList.contains('axpl-select-mode');
    setSelectMode(now);
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && root.classList.contains('axpl-select-mode')) {
      setSelectMode(false);
    }
  });

  deleteBtn?.addEventListener('click', async () => {
    const ids = selectedTiles().map(t => Number(t.dataset.id));
    if (!ids.length) return;
    if (!confirm(`¿Eliminar ${ids.length} playlist${ids.length>1?'s':''}?`)) return;

    try {
      const fd = new FormData();
      fd.append('_token', CSRF);
      ids.forEach(id => fd.append('ids[]', id));

      const res = await fetch(BULK_DELETE_URL, {
        method:'POST',
        body: fd,
        credentials:'same-origin',
        headers:{ 'X-Requested-With':'XMLHttpRequest' }
      });
      if (!res.ok) throw new Error('Error eliminando');

      // Quitar del DOM solo si el backend confirma
      selectedTiles().forEach(t => t.remove());
      setSelectMode(false);
      showToast('Eliminadas correctamente', 'ok');
    } catch (err) {
      console.error(err);
      showToast('No se pudieron eliminar', 'err');
    }
  });

  /* ---------- Modal: abrir/editar ---------- */
  openModalBtns.forEach(btn => btn.addEventListener('click', () => openModal({edit:false})));
  closeModalBtn?.addEventListener('click', closeModal);
  cancelBtn?.addEventListener('click', closeModal);
  backdrop?.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) closeModal();
  });

  /* ---------- Uploader: drag & drop + preview ---------- */
  function isValidImg(file) {
    if (!file || !file.type?.startsWith('image/')) return { ok:false, msg:'Archivo no es una imagen.' };
    const maxBytes = MAX_MB * 1024 * 1024;
    if (file.size > maxBytes) return { ok:false, msg:`Máximo ${MAX_MB} MB.` };
    return { ok:true, msg:'' };
  }
  function setPreview(src) {
    coverPrev.src = src;
    coverPrev.style.display = 'block';
    dropZone.classList.add('has-preview');
  }
  dropZone?.addEventListener('click', () => coverInput?.click());
  dropZone?.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('is-dragover'); });
  dropZone?.addEventListener('dragleave', () => dropZone.classList.remove('is-dragover'));
  dropZone?.addEventListener('drop', async (e) => {
    e.preventDefault(); dropZone.classList.remove('is-dragover');
    const file = e.dataTransfer.files?.[0];
    const {ok,msg} = isValidImg(file);
    coverMsg.textContent = msg || '';
    if (!ok) return;
    coverInput.files = e.dataTransfer.files;
    setPreview(await fileToPreview(file));
  });
  coverInput?.addEventListener('change', async () => {
    const file = coverInput.files?.[0];
    const {ok,msg} = isValidImg(file);
    coverMsg.textContent = msg || '';
    if (!ok) { coverInput.value=''; return; }
    setPreview(await fileToPreview(file));
  });

  /* ---------- Validación ligera al enviar ---------- */
  form?.addEventListener('submit', (e) => {
    nameMsg.textContent = '';
    if (!nameInput.value.trim() || nameInput.value.trim().length < 3) {
      e.preventDefault();
      nameMsg.textContent = 'El nombre debe tener al menos 3 caracteres.';
      nameInput.focus();
    }
  });
});
