<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Playlist Card</title>
  @vite('resources/css/playlist_card.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  @yield('content')
  @include('components.traductor')
  @include('components.footer')

  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')

    <main class="main-content">

      {{-- ====== CARD DE LA PLAYLIST ====== --}}
      <div class="playlist-card">
        <!-- Botón Editar -->
        <button class="edit-btn" id="openEditModal"><i class="fa-solid fa-pen"></i> Editar</button>

        <div class="cover traducible">
          <img src="{{ $playlist->cover_url ?? asset('img/default-cancion.png') }}" 
               alt="Portada Playlist">
        </div>

        <div class="info traducible">
          <!-- Subtítulo con candado -->
          <span class="subtitle traducible" id="playlistStatus">
            Playlist pública <i id="lockIcon" class="fa-solid fa-lock-open lock-toggle"></i>
          </span>

          <h2 class="title">{{ $playlist->nombre }}</h2>
          <span class="update">Actualizado {{ $playlist->updated_at->diffForHumans() }}</span>
          <p class="description traducible">{{ $playlist->descripcion ?? '(Sin descripción)' }}</p>
          <div class="actions">
            <button class="btn play traducible"><i class="fa-solid fa-play"></i> Reproducir</button>
            <button class="btn shuffle traducible"><i class="fa-solid fa-shuffle"></i> Aleatorio</button>
          </div>
        </div>
      </div>

      {{-- ====== LISTA DE CANCIONES ====== --}}
      <section class="playlist-songs">
        <div class="songs-header">
          <h3><i class="fa-solid fa-music"></i> Canciones en esta playlist</h3>
          <input type="text" class="song-search" placeholder="Buscar canción...">
        </div>

        <div class="songs-list">
          @forelse($playlist->canciones as $song)
            @php
              $coverUrl = $song->cover_url
                  ? drive_img_url($song->cover_url, 300)
                  : asset('img/default-cancion.png');

              $rawAudio = $song->audio_url;
              $audioUrl = null;
              if ($rawAudio) {
                  if (Str::contains($rawAudio, 'drive.google')) {
                      if (preg_match('~/d/([^/]+)~', $rawAudio, $m)) {
                          $id = $m[1];
                      } elseif (preg_match('~[?&]id=([^&]+)~', $rawAudio, $m)) {
                          $id = $m[1];
                      } else {
                          $id = null;
                      }
                      $audioUrl = $id ? route('media.drive', ['id' => $id]) : $rawAudio;
                  } else {
                      $audioUrl = $rawAudio;
                  }
              }
            @endphp

            <div class="song-row">
              <div class="song-left">
                <img src="{{ $coverUrl }}" alt="cover">
                <div class="song-info">
                  <h4>{{ $song->title }}</h4>
                  <p>{{ $song->artist ?? 'Artista desconocido' }}</p>
                </div>
              </div>

              <div class="song-duration">{{ $song->duration ?? '0:00' }}</div>

              <!-- Botón invisible para el reproductor -->
              <button class="cancion-item"
                      style="display:none"
                      data-id="{{ $song->id }}"
                      data-src="{{ $audioUrl }}"
                      data-title="{{ $song->title }}"
                      data-artist="{{ $song->artist ?? 'Desconocido' }}"
                      data-cover="{{ $coverUrl }}">
              </button>

              <!-- Botón para quitar canción de la playlist -->
              <form action="{{ route('playlists.removeSong', [$playlist->id, $song->id]) }}"
                    method="POST"
                    onsubmit="return confirm('¿Quitar esta canción de la playlist?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-btn"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          @empty
            <p class="empty-msg traducible"><i class="fa-solid fa-music"></i> Aún no tienes canciones en esta playlist.</p>
          @endforelse
        </div>
      </section>
    </main>
  </div>

  <!-- ===== MODAL EDITAR PLAYLIST ===== -->
  <div class="modal" id="editModal" aria-hidden="true">
    <div class="modal-content glass">
      <div class="modal-header">
        <h3><i class="fa-solid fa-pen-to-square"></i> Editar Playlist</h3>
        <button type="button" class="btn-secondary" id="closeEditModal"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form>
        <!-- Nombre -->
        <div class="modal-row">
          <div class="label-col">Nombre</div>
          <div class="input-col">
            <input type="text" placeholder="Escribe el nombre de la playlist" value="{{ $playlist->nombre }}">
          </div>
        </div>

        <!-- Portada -->
        <div class="modal-row">
          <div class="label-col">Portada</div>
          <div class="input-col">
            <label class="file-preview banner-edit">
              <img src="{{ $playlist->cover_url ?? asset('img/default-cancion.png') }}" alt="cover">
              <input type="file" accept="image/*" hidden>
              <div class="overlay"><i class="fa-solid fa-camera"></i></div>
            </label>
          </div>
        </div>

        <!-- Descripción -->
        <div class="modal-row">
          <div class="label-col">Descripción</div>
          <div class="input-col">
            <textarea rows="4" placeholder="Escribe una descripción...">{{ $playlist->descripcion ?? '' }}</textarea>
          </div>
        </div>

        <!-- Pública o privada -->
        <div class="modal-row">
          <div class="label-col">Visibilidad</div>
          <div class="input-col visibilidad">
            <span id="modalStatus">Playlist pública</span>
            <i id="modalLockIcon" class="fa-solid fa-lock-open lock-toggle"></i>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
          <button type="button" class="btn-secondary" id="cancelEdit"><i class="fa-solid fa-xmark"></i> Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const openBtn = document.getElementById('openEditModal');
    const closeBtn = document.getElementById('closeEditModal');
    const cancelBtn = document.getElementById('cancelEdit');
    const modal = document.getElementById('editModal');

    openBtn.addEventListener('click', () => modal.setAttribute('aria-hidden','false'));
    closeBtn.addEventListener('click', () => modal.setAttribute('aria-hidden','true'));
    cancelBtn.addEventListener('click', () => modal.setAttribute('aria-hidden','true'));

    // Toggle candado en la card
    const lockIcon = document.getElementById('lockIcon');
    const playlistStatus = document.getElementById('playlistStatus');
    lockIcon.addEventListener('click', () => {
      if (lockIcon.classList.contains('fa-lock-open')) {
        lockIcon.classList.remove('fa-lock-open');
        lockIcon.classList.add('fa-lock');
        playlistStatus.innerHTML = 'Playlist privada <i id="lockIcon" class="fa-solid fa-lock lock-toggle"></i>';
      } else {
        lockIcon.classList.remove('fa-lock');
        lockIcon.classList.add('fa-lock-open');
        playlistStatus.innerHTML = 'Playlist pública <i id="lockIcon" class="fa-solid fa-lock-open lock-toggle"></i>';
      }
    });

    // Toggle candado en el modal
    const modalLockIcon = document.getElementById('modalLockIcon');
    const modalStatus = document.getElementById('modalStatus');
    modalLockIcon.addEventListener('click', () => {
      if (modalLockIcon.classList.contains('fa-lock-open')) {
        modalLockIcon.classList.remove('fa-lock-open');
        modalLockIcon.classList.add('fa-lock');
        modalStatus.textContent = 'Playlist privada';
      } else {
        modalLockIcon.classList.remove('fa-lock');
        modalLockIcon.classList.add('fa-lock-open');
        modalStatus.textContent = 'Playlist pública';
      }
    });
  </script>
</body>
</html>
