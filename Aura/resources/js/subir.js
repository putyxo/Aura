// AURA — Subir Música JavaScript
// Maneja la selección de tipo, drag & drop, validación y envío de formularios

document.addEventListener('DOMContentLoaded', function() {
  // Elementos principales
  const selection = document.getElementById('axupSelection');

  // Modals
  const modalCancion = document.getElementById('axupModalSong');
  const modalAlbum = document.getElementById('axupModalAlbum');

  // Formularios modales
  const formCancionModal = document.getElementById('axupFormSong');
  const formAlbumModal = document.getElementById('axupFormAlbum');

  // Inputs modales
  const inputsModal = {
    nombre: document.getElementById('axupSongNombre'),
    title: document.getElementById('axupAlbumTitle'),
    mp3: document.getElementById('axupSongFile'),
    portada: document.getElementById('axupSongCover'),
    cover: document.getElementById('axupAlbumCover'),
    tracks: document.getElementById('axupAlbumTracks')
  };

  // Dropzones modales
  const dropzonesModal = {
    mp3: document.getElementById('axupSongFileUploader'),
    portada: document.getElementById('axupSongCoverUploader'),
    cover: document.getElementById('axupAlbumCoverUploader'),
    tracks: document.getElementById('axupAlbumTracksUploader')
  };

  // Overlays modales
  const overlaysModal = {
    mp3: document.getElementById('axupSongFileOverlay'),
    portada: document.getElementById('axupSongCoverOverlay'),
    cover: document.getElementById('axupAlbumCoverOverlay'),
    tracks: document.getElementById('axupAlbumTracksOverlay')
  };

  // Lista de tracks modal
  const tracksListModal = document.getElementById('axupTracksList');
  const tracksContainerModal = document.getElementById('axupTracksList'); // Use tracksList as container

  // Modal backdrop
  const modalBackdrop = document.getElementById('axupModalBackdrop');

  // Funciones principales
  window.abrirModal = function(tipo) {
    if (tipo === 'cancion') {
      modalBackdrop.hidden = false;
      modalCancion.hidden = false;
      // Limpiar formulario
      formCancionModal.reset();
      clearFieldMessagesModal();
    } else if (tipo === 'album') {
      modalBackdrop.hidden = false;
      modalAlbum.hidden = false;
      // Limpiar formulario
      formAlbumModal.reset();
      tracksListModal.hidden = true;
      tracksContainerModal.innerHTML = '';
      clearFieldMessagesModal();
    }
  };

  // Event listeners for modal opening buttons
  const openSongModalBtn = document.getElementById('axupOpenModalSong');
  const openAlbumModalBtn = document.getElementById('axupOpenModalAlbum');

  if (openSongModalBtn) {
    openSongModalBtn.addEventListener('click', (e) => {
      e.preventDefault();
      abrirModal('cancion');
    });
  }

  if (openAlbumModalBtn) {
    openAlbumModalBtn.addEventListener('click', (e) => {
      e.preventDefault();
      abrirModal('album');
    });
  }

  // Event listeners for tile buttons
  const uploadOptions = document.querySelectorAll('.axup-upload-option');
  uploadOptions.forEach(option => {
    const button = option.querySelector('.axup-tile-action');
    if (button) {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const type = option.getAttribute('data-type');
        if (type === 'song') {
          abrirModal('cancion');
        } else if (type === 'album') {
          abrirModal('album');
        }
      });
    }
  });

  window.cerrarModal = function(tipo) {
    if (tipo === 'cancion') {
      modalCancion.hidden = true;
      modalBackdrop.hidden = true;
    } else if (tipo === 'album') {
      modalAlbum.hidden = true;
      modalBackdrop.hidden = true;
    }
  };

  // Event listeners for close buttons
  const closeSongModalBtn = document.getElementById('axupCloseModalSong');
  const closeAlbumModalBtn = document.getElementById('axupCloseModalAlbum');
  const cancelSongBtn = document.getElementById('axupCancelSong');
  const cancelAlbumBtn = document.getElementById('axupCancelAlbum');

  if (closeSongModalBtn) {
    closeSongModalBtn.addEventListener('click', () => cerrarModal('cancion'));
  }

  if (closeAlbumModalBtn) {
    closeAlbumModalBtn.addEventListener('click', () => cerrarModal('album'));
  }

  if (cancelSongBtn) {
    cancelSongBtn.addEventListener('click', () => cerrarModal('cancion'));
  }

  if (cancelAlbumBtn) {
    cancelAlbumBtn.addEventListener('click', () => cerrarModal('album'));
  }

  // Close modal when clicking on backdrop
  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', () => {
      if (!modalCancion.hidden) {
        cerrarModal('cancion');
      } else if (!modalAlbum.hidden) {
        cerrarModal('album');
      }
    });
  }

  // Configurar drag & drop para modals
  function setupDragDropModal(dropzone, input, overlay) {
    if (!dropzone || !input) return;

    // Click para abrir selector de archivos
    dropzone.addEventListener('click', () => input.click());

    // Eventos de drag
    dropzone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropzone.classList.add('dragover');
      if (overlay) overlay.style.opacity = '1';
    });

    dropzone.addEventListener('dragleave', () => {
      dropzone.classList.remove('dragover');
      if (overlay) overlay.style.opacity = '0';
    });

    dropzone.addEventListener('drop', (e) => {
      e.preventDefault();
      dropzone.classList.remove('dragover');
      if (overlay) overlay.style.opacity = '0';

      if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
      }
    });
  }

  // Configurar drag & drop para todos los elementos modales
  setupDragDropModal(dropzonesModal.mp3, inputsModal.mp3, overlaysModal.mp3);
  setupDragDropModal(dropzonesModal.portada, inputsModal.portada, overlaysModal.portada);
  setupDragDropModal(dropzonesModal.cover, inputsModal.cover, overlaysModal.cover);
  setupDragDropModal(dropzonesModal.tracks, inputsModal.tracks, overlaysModal.tracks);

  // Manejar cambio de archivos MP3 modales
  inputsModal.mp3?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
      validateFile(file, 'mp3', 'axupMsgSongFile');
    }
  });

  // Manejar cambio de portada modal
  inputsModal.portada?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
      validateFile(file, 'image', 'axupMsgSongCover');
    }
  });

  // Manejar cambio de portada de álbum modal
  inputsModal.cover?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
      validateFile(file, 'image', 'axupMsgAlbumCover');
    }
  });

  // Manejar cambio de tracks de álbum modal
  inputsModal.tracks?.addEventListener('change', function() {
    const files = Array.from(this.files);
    if (files.length > 0) {
      validateTracksModal(files);
      renderTracksListModal(files);
    } else {
      tracksListModal.hidden = true;
    }
  });

  // Validación de archivos
  function validateFile(file, type, msgId) {
    const msg = document.getElementById(msgId);
    if (!msg) return true;

    let isValid = true;
    let message = '';

    if (type === 'mp3') {
      if (!file.type.includes('audio/mpeg') && !file.name.toLowerCase().endsWith('.mp3')) {
        isValid = false;
        message = 'Solo se permiten archivos MP3';
      } else if (file.size > 50 * 1024 * 1024) { // 50MB
        isValid = false;
        message = 'El archivo no puede superar los 50MB';
      }
    } else if (type === 'image') {
      if (!file.type.startsWith('image/')) {
        isValid = false;
        message = 'Solo se permiten imágenes (JPG, PNG, WEBP)';
      } else if (file.size > 5 * 1024 * 1024) { // 5MB
        isValid = false;
        message = 'La imagen no puede superar los 5MB';
      }
    }

    if (isValid) {
      msg.textContent = '';
      msg.style.display = 'none';
    } else {
      msg.textContent = message;
      msg.style.display = 'block';
      msg.style.color = '#ef4444';
    }

    return isValid;
  }

  function validateTracksModal(files) {
    const msg = document.getElementById('axupMsgAlbumTracks');
    if (!msg) return true;

    let isValid = true;
    let message = '';

    if (files.length === 0) {
      isValid = false;
      message = 'Selecciona al menos una canción';
    } else {
      for (const file of files) {
        if (!file.type.includes('audio/mpeg') && !file.name.toLowerCase().endsWith('.mp3')) {
          isValid = false;
          message = 'Solo se permiten archivos MP3';
          break;
        } else if (file.size > 50 * 1024 * 1024) {
          isValid = false;
          message = 'Cada archivo no puede superar los 50MB';
          break;
        }
      }
    }

    if (isValid) {
      msg.textContent = '';
      msg.style.display = 'none';
    } else {
      msg.textContent = message;
      msg.style.display = 'block';
      msg.style.color = '#ef4444';
    }

    return isValid;
  }

  // Renderizar lista de tracks modal
  function renderTracksListModal(files) {
    tracksContainerModal.innerHTML = '';
    tracksListModal.hidden = false;

    files.forEach((file, index) => {
      const trackItem = document.createElement('div');
      trackItem.className = 'axup-track-item';
      trackItem.innerHTML = `
        <div class="axup-track-info">
          <audio controls class="axup-track-audio">
            <source src="${URL.createObjectURL(file)}" type="audio/mpeg">
          </audio>
          <input type="text" name="titles[]" class="axup-track-title"
                 placeholder="Título de la canción" value="${file.name.replace(/\.[^/.]+$/, '')}" required>
        </div>
        <button type="button" class="axup-track-remove" onclick="removeTrackModal(${index})">
          <i class="fa-solid fa-xmark"></i>
        </button>
      `;
      tracksContainerModal.appendChild(trackItem);
    });
  }

  // Remover track modal
  window.removeTrackModal = function(index) {
    const files = Array.from(inputsModal.tracks.files);
    files.splice(index, 1);

    // Crear nuevo FileList
    const dt = new DataTransfer();
    files.forEach(file => dt.items.add(file));
    inputsModal.tracks.files = dt.files;

    renderTracksListModal(files);
  };

  // Limpiar mensajes de campo modales
  function clearFieldMessagesModal() {
    const messages = document.querySelectorAll('.axup-field-msg');
    messages.forEach(msg => {
      msg.textContent = '';
      msg.style.display = 'none';
    });
  }

  // Validación de formularios modales
  formCancionModal?.addEventListener('submit', function(e) {
    let isValid = true;

    if (!inputsModal.nombre.value.trim()) {
      showFieldErrorModal('axupMsgSongNombre', 'El nombre es obligatorio');
      isValid = false;
    }

    if (!inputsModal.mp3.files[0]) {
      showFieldErrorModal('axupMsgSongFile', 'Selecciona un archivo MP3');
      isValid = false;
    } else if (!validateFile(inputsModal.mp3.files[0], 'mp3', 'axupMsgSongFile')) {
      isValid = false;
    }

    if (inputsModal.portada.files[0] && !validateFile(inputsModal.portada.files[0], 'image', 'axupMsgSongCover')) {
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
      showToast('Corrige los errores antes de continuar', 'err');
    }
  });

  formAlbumModal?.addEventListener('submit', function(e) {
    let isValid = true;

    if (!inputsModal.title.value.trim()) {
      showFieldErrorModal('axupMsgAlbumTitle', 'El título es obligatorio');
      isValid = false;
    }

    if (!inputsModal.cover.files[0]) {
      showFieldErrorModal('axupMsgAlbumCover', 'Selecciona una portada');
      isValid = false;
    } else if (!validateFile(inputsModal.cover.files[0], 'image', 'axupMsgAlbumCover')) {
      isValid = false;
    }

    if (!inputsModal.tracks.files.length) {
      showFieldErrorModal('axupMsgAlbumTracks', 'Selecciona al menos una canción');
      isValid = false;
    } else if (!validateTracksModal(Array.from(inputsModal.tracks.files))) {
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
      showToast('Corrige los errores antes de continuar', 'err');
    }
  });

  // Mostrar error en campo modal
  function showFieldErrorModal(msgId, message) {
    const msg = document.getElementById(msgId);
    if (msg) {
      msg.textContent = message;
      msg.style.display = 'block';
      msg.style.color = '#ef4444';
    }
  }

  // Toast notifications
  function showToast(message, type = 'info') {
    const toast = document.getElementById('axupToast');
    if (!toast) return;

    toast.className = `axup-toast axup-toast-${type}`;
    toast.innerHTML = `
      <i class="fa-solid fa-circle-${type === 'ok' ? 'check' : type === 'err' ? 'xmark' : 'info'}"></i>
      <span>${message}</span>
    `;
    toast.hidden = false;
    toast.classList.add('is-shown');

    setTimeout(() => {
      toast.classList.remove('is-shown');
      setTimeout(() => toast.hidden = true, 300);
    }, 3000);
  }

  // Cerrar modal al hacer clic fuera
  modalCancion?.addEventListener('click', function(e) {
    if (e.target === modalCancion) {
      cerrarModal('cancion');
    }
  });

  modalAlbum?.addEventListener('click', function(e) {
    if (e.target === modalAlbum) {
      cerrarModal('album');
    }
  });

  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (!modalCancion.hidden) {
        cerrarModal('cancion');
      } else if (!modalAlbum.hidden) {
        cerrarModal('album');
      }
    }
  });

  // Focus management
  const focusableElements = document.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
  focusableElements.forEach(el => {
    el.addEventListener('focus', () => {
      el.style.outline = '2px solid rgba(167, 139, 250, 0.55)';
      el.style.outlineOffset = '2px';
    });
    el.addEventListener('blur', () => {
      el.style.outline = '';
    });
  });

  // Toolbar functionality
  const searchInput = document.getElementById('axupSearch');
  const clearSearchBtn = document.getElementById('axupClearSearch');
  const filterButtons = document.querySelectorAll('.axup-filters .axup-chip');
  const btnSelectMode = document.getElementById('axupSelectMode');
  const btnDeleteSelected = document.getElementById('axupDeleteSelected');

  let selectionMode = false;

  // Search functionality
  if (searchInput && clearSearchBtn) {
    clearSearchBtn.addEventListener('click', () => {
      searchInput.value = '';
      searchInput.dispatchEvent(new Event('input'));
    });

    searchInput.addEventListener('input', () => {
      const query = searchInput.value.toLowerCase();
      const tiles = document.querySelectorAll('.axup-tile');
      tiles.forEach(tile => {
        const name = tile.querySelector('.axup-tile-name').textContent.toLowerCase();
        const desc = tile.querySelector('.axup-tile-desc').textContent.toLowerCase();
        if (name.includes(query) || desc.includes(query)) {
          tile.style.display = '';
        } else {
          tile.style.display = 'none';
        }
      });
    });
  }

  // Filter functionality
  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      filterButtons.forEach(btn => btn.classList.remove('is-active'));
      button.classList.add('is-active');
      const filter = button.getAttribute('data-filter');
      const tiles = document.querySelectorAll('.axup-tile');
      tiles.forEach(tile => {
        if (filter === 'all') {
          tile.style.display = '';
        } else if (filter === 'songs' && tile.getAttribute('data-type') === 'song') {
          tile.style.display = '';
        } else if (filter === 'albums' && tile.getAttribute('data-type') === 'album') {
          tile.style.display = '';
        } else if (filter === 'recent') {
          // For now, show all for recent filter (implement as needed)
          tile.style.display = '';
        } else {
          tile.style.display = 'none';
        }
      });
    });
  });

  // Selection mode toggle
  if (btnSelectMode) {
    btnSelectMode.addEventListener('click', () => {
      selectionMode = !selectionMode;
      btnSelectMode.setAttribute('aria-pressed', selectionMode);
      btnSelectMode.querySelector('.axup-btn-text').textContent = selectionMode ? 'Cancelar selección' : 'Seleccionar';
      if (btnDeleteSelected) {
        btnDeleteSelected.disabled = !selectionMode;
      }
      const tiles = document.querySelectorAll('.axup-tile');
      tiles.forEach(tile => {
        if (selectionMode) {
          tile.classList.add('selectable');
        } else {
          tile.classList.remove('selectable');
          tile.classList.remove('selected');
        }
      });
    });
  }

  // Tile selection
  document.querySelectorAll('.axup-tile').forEach(tile => {
    tile.addEventListener('click', () => {
      if (!selectionMode) return;
      tile.classList.toggle('selected');
      const selectedCount = document.querySelectorAll('.axup-tile.selected').length;
      if (btnDeleteSelected) {
        btnDeleteSelected.disabled = selectedCount === 0;
      }
    });
  });

  // Delete selected (placeholder)
  if (btnDeleteSelected) {
    btnDeleteSelected.addEventListener('click', () => {
      const selectedTiles = document.querySelectorAll('.axup-tile.selected');
      if (selectedTiles.length === 0) return;
      if (!confirm(`¿Eliminar ${selectedTiles.length} elementos seleccionados? Esta acción no se puede deshacer.`)) return;
      selectedTiles.forEach(tile => tile.remove());
      btnDeleteSelected.disabled = true;
      if (btnSelectMode) {
        btnSelectMode.setAttribute('aria-pressed', false);
        btnSelectMode.querySelector('.axup-btn-text').textContent = 'Seleccionar';
      }
    });
  }
});
