<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Álbumes — {{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</title>

  <!-- Fuentes y estilos -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  @vite('resources/css/menu_album.css')

</head>
<body>
  @include('components.sidebar')
  @include('components.header')
  @include('components.traductor')

  @php
    $bannerHigh = $user->banner ? drive_img_url($user->banner, 1920) . '&v=' . time() : asset('img/default-banner.jpg');
  @endphp

  <main class="main-content">
    <!-- Banner -->
    <div class="user-banner" style="background-image: url('{{ $bannerHigh }}'); margin-top: 90px; padding-top: 150px;">
      <div class="banner-overlay"></div>

      <div class="user-info">
        <h2 class="user-name">{{ $user->nombre_artistico ?? $user->nombre ?? 'Invitado' }}</h2>
        <p class="user-followers">{{ $followersCount ?? 0 }} seguidores</p>
      </div>
    </div>

    <!-- Título flotante debajo del banner -->
    <div class="albums-head" style="margin-top: 20px;">
      <div class="section-title">
        <h2><i class="fa-solid fa-compact-disc"></i> Álbumes</h2>
      </div>
      <div class="page-label" id="albumsPageLabel"></div>
    </div>

    <!-- Contenedor álbumes -->
    <div class="albums-wrap no-clip">
      <!-- Flecha izquierda -->
      <button class="albums-arrow left" id="albumsPrev" aria-label="Anterior">
        <i class="fa-solid fa-chevron-left"></i>
      </button>

      <div class="albums-viewport" id="albumsViewport">
        <div class="albums-track" id="albumsTrack" data-pages="{{ $albumPages->count() }}">
          @foreach($albumPages as $page)
            <div class="albums-page">
              <div class="albums-grid-2x2">
                @foreach($page as $album)
                  @php
                    $albumCover = $album->portada
                      ? drive_img_url($album->portada, 360) . '&v=' . time()
                      : asset('img/default-album.png');

                    $tracksData = $album->songs->map(function($song) {
                      return [
                        'title' => $song->titulo,
                        'audio_url' => $song->audio_path ?: ''
                      ];
                    })->toArray();
                    $tracksJson = json_encode($tracksData);
                  @endphp

                  <div class="card album-card {{ (Auth::check() && Auth::id() === $user->id) ? 'has-trash' : '' }}"
                       data-title="{{ $album->titulo }}"
                       data-tracks='{{ $tracksJson }}'>
                    <div class="card-link">
                      <div class="card-img">
                        <img src="{{ $albumCover }}" alt="Portada" loading="lazy" decoding="async">
                        <span class="album-play"><i class="fa-solid fa-play"></i></span>
                      </div>
                      <h4 class="album-title" style="cursor: pointer;">{{ $album->titulo }}</h4>
                      <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</p>
                    </div>

                    @if(Auth::check() && Auth::id() === $user->id)
                      <button class="trash-float open-delete"
                              title="Eliminar álbum"
                              data-type="album"
                              data-action="{{ route('album.destroy', $album->id) }}"
                              data-title="{{ $album->titulo }}"
                              data-cover="{{ $albumCover }}">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Flecha derecha -->
      <button class="albums-arrow right" id="albumsNext" aria-label="Siguiente">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
  </main>

  @include('components.footer')

  <!-- Modal canciones -->
  <div id="albumModal" class="modal-overlay">
    <div class="modal-content">
      <button class="modal-close">&times;</button>
      <h2 id="modal-album-title"><i class="fa-solid fa-compact-disc"></i> Álbum</h2>
      <ul id="modal-album-tracks"></ul>
    </div>
  </div>

  <!-- Script navegación y modal -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Navegación álbumes
      const track = document.getElementById('albumsTrack');
      const prev = document.getElementById('albumsPrev');
      const next = document.getElementById('albumsNext');
      const label = document.getElementById('albumsPageLabel');
      if (track) {
        let page = 0, pages = parseInt(track.dataset.pages || '0', 10);
        function update() {
          track.style.transform = `translateX(-${page * 100}%)`;
          prev.disabled = (page === 0);
          next.disabled = (page >= pages - 1);
          label.textContent = pages ? `Página ${page + 1} de ${pages}` : '';
        }
        prev?.addEventListener('click', () => { if (page > 0) { page--; update(); } });
        next?.addEventListener('click', () => { if (page < pages - 1) { page++; update(); } });
        update();
      }

      // Modal canciones
      const modal = document.getElementById('albumModal');
      const modalTitle = document.getElementById('modal-album-title');
      const modalTracks = document.getElementById('modal-album-tracks');
      const modalClose = modal.querySelector('.modal-close');

      if (modal && modalTitle && modalTracks && modalClose) {
        // Modal para el título principal "Álbumes"
        const albumsTitle = document.querySelector('.section-title h2');
        if (albumsTitle) {
        albumsTitle.addEventListener('click', (e) => {
          e.preventDefault();
          modalTitle.innerHTML = `<i class="fa-solid fa-compact-disc"></i> Canciones de Álbumes que te gustaron`;
          modalTracks.innerHTML = "";

          // Obtener todas las canciones de álbumes que el usuario ha marcado con "like"
          // Aquí asumimos que hay un endpoint o variable JS que contiene estas canciones
          // Como no tenemos acceso directo, mostraremos un mensaje placeholder

          // TODO: Reemplazar con la lógica real para obtener canciones liked por el usuario
          const likedSongsData = @json($likedSongs);

          if (likedSongsData.length > 0) {
            likedSongsData.forEach((song, index) => {
              const title = song.title || song.titulo || 'Canción';
              const albumTitle = song.album ? song.album.titulo : 'Álbum desconocido';
              const li = document.createElement('li');
              const audioUrl = song.audio_path || song.audio_url || '';
              li.innerHTML = `<span>${index + 1}. ${title} - <em>${albumTitle}</em></span> <i class="fa-solid fa-play play-icon" data-title="${title} - ${albumTitle}" data-audio="${audioUrl}"></i>`;
              modalTracks.appendChild(li);
            });
          } else {
            const li = document.createElement('li');
            li.innerHTML = `<span>No tienes canciones de álbumes que te gustaron</span>`;
            modalTracks.appendChild(li);
          }

          modal.classList.add('active');
        });
        }

        // Modal para imágenes de álbumes (click en la portada)
        document.querySelectorAll('.album-card .card-img').forEach(imgElement => {
          imgElement.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const card = imgElement.closest('.album-card');
            const title = card.getAttribute('data-title');
            const tracks = JSON.parse(card.getAttribute('data-tracks') || '[]');

            modalTitle.innerHTML = `<i class="fa-solid fa-compact-disc"></i> ${title}`;
            modalTracks.innerHTML = "";

            if (tracks.length > 0) {
              tracks.forEach((track, i) => {
                const li = document.createElement('li');
                const songTitle = track.title || track.titulo || 'Canción';
                const audioUrl = track.audio_url || track.audio_path || '';
                li.innerHTML = `<span>${i+1}. ${songTitle}</span> <i class="fa-solid fa-play play-icon" data-title="${songTitle}" data-audio="${audioUrl}"></i>`;
                modalTracks.appendChild(li);
              });
            } else {
              const li = document.createElement('li');
              li.innerHTML = `<span>No hay canciones disponibles</span>`;
              modalTracks.appendChild(li);
            }

            modal.classList.add('active');
          });
        });

        // También mantener funcionalidad para títulos de álbumes
        document.querySelectorAll('.album-card .album-title').forEach(titleElement => {
          titleElement.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const card = titleElement.closest('.album-card');
            const title = card.getAttribute('data-title');
            const tracks = JSON.parse(card.getAttribute('data-tracks') || '[]');

            modalTitle.innerHTML = `<i class="fa-solid fa-compact-disc"></i> ${title}`;
            modalTracks.innerHTML = "";

            if (tracks.length > 0) {
              tracks.forEach((track, i) => {
                const li = document.createElement('li');
                const songTitle = track.title || track.titulo || 'Canción';
                const audioUrl = track.audio_url || track.audio_path || '';
                li.innerHTML = `<span>${i+1}. ${songTitle}</span> <i class="fa-solid fa-play play-icon" data-title="${songTitle}" data-audio="${audioUrl}"></i>`;
                modalTracks.appendChild(li);
              });
            } else {
              const li = document.createElement('li');
              li.innerHTML = `<span>No hay canciones disponibles</span>`;
              modalTracks.appendChild(li);
            }

            modal.classList.add('active');
          });
        });

        modalClose.addEventListener('click', () => modal.classList.remove('active'));
        modal.addEventListener('click', e => {
          if (e.target === modal) modal.classList.remove('active');
        });

        // Reproducir canción al hacer click en el icono play dentro del modal
        let currentAudio = null;

        document.addEventListener('click', (e) => {
          if (e.target.classList.contains('play-icon')) {
            const songTitle = e.target.getAttribute('data-title');
            const audioUrl = e.target.getAttribute('data-audio');

            // Detener audio anterior si existe
            if (currentAudio) {
              currentAudio.pause();
              currentAudio.currentTime = 0;
            }

            if (audioUrl && audioUrl.trim() !== '') {
              try {
                // Crear nuevo elemento de audio
                currentAudio = new Audio(audioUrl);

                // Manejar eventos de audio
                currentAudio.addEventListener('loadstart', () => {
                  console.log(`Cargando canción: ${songTitle}`);
                });

                currentAudio.addEventListener('canplay', () => {
                  console.log(`Canción lista para reproducir: ${songTitle}`);
                });

                currentAudio.addEventListener('play', () => {
                  console.log(`Reproduciendo: ${songTitle}`);
                  // Cambiar icono a pausa
                  e.target.className = 'fa-solid fa-pause play-icon';
                  e.target.setAttribute('data-playing', 'true');
                });

                currentAudio.addEventListener('pause', () => {
                  console.log(`Pausado: ${songTitle}`);
                  // Cambiar icono a play
                  e.target.className = 'fa-solid fa-play play-icon';
                  e.target.removeAttribute('data-playing');
                });

                currentAudio.addEventListener('ended', () => {
                  console.log(`Canción terminada: ${songTitle}`);
                  // Resetear icono
                  e.target.className = 'fa-solid fa-play play-icon';
                  e.target.removeAttribute('data-playing');
                });

                currentAudio.addEventListener('error', (error) => {
                  console.error(`Error al reproducir ${songTitle}:`, error);
                  alert(`Error al reproducir la canción "${songTitle}". Verifica que el archivo de audio exista.`);
                });

                // Reproducir la canción
                currentAudio.play().catch(error => {
                  console.error('Error al intentar reproducir:', error);
                  alert(`No se pudo reproducir la canción "${songTitle}". Puede que el navegador bloquee la reproducción automática.`);
                });

              } catch (error) {
                console.error('Error al crear elemento de audio:', error);
                alert(`Error al intentar reproducir la canción "${songTitle}".`);
              }
            } else {
              alert(`La canción "${songTitle}" no tiene un archivo de audio disponible.`);
            }
          }
        });
      }
    });
  </script>
</body>
</html>
