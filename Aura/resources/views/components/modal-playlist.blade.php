<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Añadir a Playlist</title>

  <!-- Fuente + Iconos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Vite CSS -->
  @vite(['resources/css/playlist.css']) <!-- Asegúrate de tener el archivo CSS correcto para el modal -->
</head>
<body>
  <!-- Modal para añadir a playlist -->
  <div id="playlistModal" class="lk-modal" role="dialog" aria-modal="true" aria-labelledby="playlistModalTitle" hidden>
    <div class="lk-modal__backdrop" data-close="1"></div>
    <div class="lk-modal__card">
      <div class="lk-modal__header">
        <h3 id="playlistModalTitle"><i class="fa-solid fa-plus"></i> Añadir a Playlist</h3>
        <button class="lk-modal__x" data-close="1" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="lk-modal__body">
        <p>Selecciona una playlist para añadir la canción.</p>
        <select id="playlistSelect" class="lk-modal__select">
          <!-- Aquí se cargarán las playlists del usuario -->
        </select>
      </div>
      <div class="lk-modal__actions">
        <button class="lk-btn lk-btn--ghost" data-close="1">Cancelar</button>
        <button class="lk-btn lk-btn--primary" id="add-to-playlist-confirm">Añadir</button>
      </div>
    </div>
  </div>

  <script>
    // JavaScript para manejar el comportamiento del modal y cargar las playlists
    (() => {
      const $  = (s, r = document) => r.querySelector(s);
      const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

      const modal = $('#playlistModal');
      const closeModalBtns = $$('[data-close="1"]');
      const openModalBtn = document.getElementById('add-to-playlist');  // El botón para abrir el modal
      const playlistSelect = $('#playlistSelect');
      const addToPlaylistConfirmBtn = $('#add-to-playlist-confirm');

      // Abre el modal
      function openModal() {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';  // Bloquea el fondo cuando el modal está abierto
        modal.classList.add('is-open');
      }

      // Cierra el modal
      function closeModal() {
        modal.classList.remove('is-open');
        modal.hidden = true;
        document.body.style.overflow = '';  // Restablece el fondo
      }

      // Carga las playlists en el select
      function loadPlaylists() {
        fetch('/api/my-playlists')  // Endpoint que devuelve las playlists del usuario
          .then(response => response.json())
          .then(playlists => {
            playlistSelect.innerHTML = ''; // Limpia el select

            playlists.forEach(playlist => {
              const option = document.createElement('option');
              option.value = playlist.id;
              option.textContent = playlist.nombre;
              playlistSelect.appendChild(option);
            });
          })
          .catch(error => console.error('Error cargando playlists:', error));
      }

      // Agrega la canción a la playlist seleccionada
      function addSongToPlaylist(songId, playlistId) {
        fetch(`/playlists/${playlistId}/add-song/${songId}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
        })
          .then(response => response.json())
          .then(data => {
            if (data.ok) {
              alert('Canción añadida correctamente.');
              closeModal();
            } else {
              alert('Error al añadir la canción.');
            }
          })
          .catch(error => console.error('Error al añadir canción:', error));
      }

      // Evento para abrir el modal cuando el botón es presionado
      openModalBtn?.addEventListener('click', () => {
        openModal();
        loadPlaylists();  // Carga las playlists cuando se abre el modal
      });

      // Evento para cerrar el modal
      closeModalBtns.forEach(btn => {
        btn.addEventListener('click', closeModal);
      });

      // Confirmar la adición de la canción a la playlist
      addToPlaylistConfirmBtn?.addEventListener('click', () => {
        const selectedPlaylistId = playlistSelect.value;
        if (selectedPlaylistId) {
          const songId = document.getElementById('song-id').value; // Asegúrate de pasar el ID de la canción al modal
          addSongToPlaylist(songId, selectedPlaylistId);
        } else {
          alert('Por favor, selecciona una playlist.');
        }
      });

      // Cerrar el modal cuando se presione ESC
      document.addEventListener('keydown', (e) => {
        if (!modal.hidden && e.key === 'Escape') {
          closeModal();
        }
      });
    })();
  </script>
</body>
</html>
