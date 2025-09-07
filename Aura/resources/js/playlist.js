document.addEventListener('DOMContentLoaded', () => {
  // ===== Animación de entrada de tiles (sin inflar layout)
  const grid = document.querySelector('.playlist-grid');
  if (grid) {
    const tiles = grid.querySelectorAll('.tile');
    requestAnimationFrame(() => {
      tiles.forEach((tile, i) => {
        tile.style.opacity = '0';
        tile.style.transform = 'translateY(14px)';
        tile.style.willChange = 'transform,opacity';
        setTimeout(() => {
          tile.style.transition = 'transform .45s var(--ease), opacity .45s var(--ease)';
          tile.style.opacity = '1';
          tile.style.transform = 'translateY(0)';
        }, i * 60);
      });
    });
  }

  // ===== Modal Crear Playlist
  const openBtn   = document.getElementById('btnOpenPlaylistModal');
  const closeBtn  = document.getElementById('btnClosePlaylistModal');
  const cancelBtn = document.getElementById('btnCancelPlaylist');
  const backdrop  = document.getElementById('playlistModalBackdrop');
  const modal     = document.getElementById('playlistModal');

  const openModal = () => {
    if (!modal || !backdrop) return;
    backdrop.hidden = false;
    modal.hidden = false;
    document.documentElement.style.overflow = 'hidden';
    document.getElementById('pl_nombre')?.focus();
  };
  const closeModal = () => {
    if (!modal || !backdrop) return;
    backdrop.hidden = true;
    modal.hidden = true;
    document.documentElement.style.overflow = '';
  };

  openBtn?.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
  [closeBtn, cancelBtn, backdrop].forEach(el => el?.addEventListener('click', closeModal));
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal?.hidden) closeModal(); });

  // ===== Drag&Drop + Validación portada
  const fileInput = document.getElementById('pl_cover');
  const dropArea  = document.getElementById('coverDrop');
  const overlay   = document.getElementById('uploaderOverlay');
  const hint      = document.getElementById('uploaderHint');
  const preview   = document.getElementById('coverPreview');
  const coverMsg  = document.getElementById('coverMsg');

  const MAX_MB = 5;
  const showError = (el, msg) => { if (!el) return; el.textContent = msg; el.classList.add('msg-error'); };
  const clearMsg  = (el) => { if (!el) return; el.textContent = ''; el.classList.remove('msg-error','msg-ok'); };
  const validType = (file) => /^image\/(png|jpe?g|webp)$/i.test(file.type);
  const validSize = (file) => file.size <= MAX_MB * 1024 * 1024;

  const setPreview = (file) => {
    const url = URL.createObjectURL(file);
    preview.src = url;
    preview.style.display = 'block';
    hint.style.display = 'none';
    dropArea.classList.add('has-preview');
  };

  ['dragenter','dragover'].forEach(evt => {
    dropArea?.addEventListener(evt, e => {
      e.preventDefault(); e.stopPropagation();
      dropArea.classList.add('is-dragover');
      if (overlay) overlay.style.opacity = '1';
    });
  });
  ['dragleave','drop'].forEach(evt => {
    dropArea?.addEventListener(evt, e => {
      e.preventDefault(); e.stopPropagation();
      dropArea.classList.remove('is-dragover');
      if (overlay) overlay.style.opacity = '0';
    });
  });

  dropArea?.addEventListener('drop', e => {
    const file = e.dataTransfer.files?.[0]; if (!file) return;
    clearMsg(coverMsg);
    if (!validType(file)) { showError(coverMsg, 'Formato no permitido. Usa PNG/JPG.'); return; }
    if (!validSize(file)) { showError(coverMsg, `El archivo supera ${MAX_MB}MB.`); return; }
    const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
    setPreview(file);
  });

  fileInput?.addEventListener('change', e => {
    const file = e.target.files?.[0]; if (!file) return;
    clearMsg(coverMsg);
    if (!validType(file)) { showError(coverMsg, 'Formato no permitido. Usa PNG/JPG.'); fileInput.value=''; return; }
    if (!validSize(file)) { showError(coverMsg, `El archivo supera ${MAX_MB}MB.`); fileInput.value=''; return; }
    setPreview(file);
  });

  // ===== Validación mínima formulario
  document.getElementById('playlistForm')?.addEventListener('submit', (e)=>{
    let ok = true;
    const name    = document.getElementById('pl_nombre');
    const nameMsg = document.getElementById('nameMsg');
    clearMsg(nameMsg); clearMsg(coverMsg);

    if (!name.value.trim()) { showError(nameMsg, 'Ingresa un nombre.'); ok = false; }
    if (!fileInput || fileInput.files.length === 0) { showError(coverMsg, 'Agrega una portada.'); ok = false; }
    if (!ok) e.preventDefault();
  });

  // ===== Panel de detalle (opcional)
  document.getElementById('btnCloseDetail')?.addEventListener('click', () => {
    const detail = document.getElementById('playlistDetail');
    if (detail) detail.hidden = true;
  });

  // ===== Modal Acceder por Enlace
  const accessBtn   = document.getElementById('accessPlaylistLink');
  const shareModal  = document.getElementById('shareLinkModal');
  const shareInput  = document.getElementById('shareLinkInput');
  const shareClose  = document.getElementById('closeShareLinkModal');
  const shareSubmit = document.getElementById('submitShareLink');

  accessBtn?.addEventListener('click', () => { shareModal.hidden = false; shareInput.focus(); });
  shareClose?.addEventListener('click', () => { shareModal.hidden = true; shareInput.value = ''; });
  shareSubmit?.addEventListener('click', () => {
    const link = (shareInput.value || '').trim();
    if (!link) { alert('Por favor, ingresa un enlace válido'); return; }

    let code = link;
    if (link.includes('/')) { const parts = link.split('/'); code = parts[parts.length - 1]; }

    fetch(`/api/playlists/share/${code}`)
      .then(r => { if (!r.ok) throw new Error('Playlist no encontrada'); return r.json(); })
      .then(data => {
        if (data.success && data.playlist?.id) window.location.href = `/playlists/${data.playlist.id}`;
        else alert('Error al acceder a la playlist.');
      })
      .catch(() => alert('No se pudo encontrar la playlist. Verifica el enlace.'));
  });
  shareInput?.addEventListener('keypress', (e) => { if (e.key === 'Enter') shareSubmit?.click(); });
});
