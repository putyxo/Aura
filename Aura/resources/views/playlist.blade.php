<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AURA — Playlists</title>
  @vite(['resources/css/playlist-unified.css', 'resources/js/playlist.js'])
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app">
  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    
    <main class="main-content">
      <div class="shell">
        {{-- ====== HEADER ====== --}}
        <section class="playlist-header">
          <h1 class="playlist-title">Tus Playlists</h1>
          <button id="accessPlaylistLink" class="btn">Acceder a Playlist</button>

          {{-- Modal para ingresar link de compartir --}}
          <div id="shareLinkModal" class="modal" hidden>
            <button class="modal-close" id="closeShareLinkModal">×</button>
            <h3 class="modal-title">Acceder a Playlist</h3>
            <input type="text" id="shareLinkInput" placeholder="Pega el enlace aquí" />
            <button id="submitShareLink">Acceder</button>
          </div>

          <p class="playlist-subtitle">Organiza tu música y crea la banda sonora perfecta para cada momento</p>
        </section>

        @if(session('ok'))
          <div class="toast toast-ok">
            <i class="fa-solid fa-circle-check"></i> <span>{{ session('ok') }}</span>
          </div>
        @endif

        {{-- ====== GRID DE PLAYLISTS ====== --}}
        <section class="playlist-grid">
          {{-- Crear nueva playlist --}}
          <button type="button" class="tile tile-create" id="btnOpenPlaylistModal">
            <div class="tile-cover create-cover">
              <i class="fa-solid fa-plus"></i>
            </div>
            <div class="tile-name traducible">Nueva playlist</div>
          </button>

          {{-- Playlists del usuario --}}
          @forelse($playlists as $pl)
            <a href="{{ route('playlists.show', $pl->id) }}" class="tile">
              <div class="tile-cover">
                @if($pl->cover_url)
                  <img src="{{ $pl->cover_url }}" alt="Portada de {{ $pl->nombre }}">
                @else
                  <div class="cover-placeholder traducible">Sin portada</div>
                @endif
                <div class="play-btn">
                  <i class="fa-solid fa-play"></i>
                </div>
              </div>
              <div class="tile-name" title="{{ $pl->nombre }}">{{ $pl->nombre }}</div>
              <div class="tile-count">{{ $pl->canciones_count ?? 0 }} canciones</div>
            </a>
          @empty
            <div class="tile traducible" style="grid-column: 1 / -1; text-align: center; padding: 40px; background: var(--panel); border-radius: var(--r-lg);">
              <i class="fa-solid fa-music" style="font-size: 48px; color: var(--dim); margin-bottom: 16px;"></i>
              <h3 style="color: var(--text); margin-bottom: 8px;">Aún no tienes playlists</h3>
              <p style="color: var(--dim);">¡Crea la primera para comenzar! ✨</p>
            </div>
          @endforelse
        </section>

        {{-- ====== DETALLE DE PLAYLIST ====== --}}
        @isset($playlist)
        <div id="playlistDetail" class="playlist-card">
          <div class="cover">
            <img id="detailCover" src="{{ $playlist->cover_url ?? asset('img/default-cancion.png') }}" alt="Portada Playlist">
          </div>
          <div class="info">
            <h2 id="detailTitle" class="title">{{ $playlist->nombre }}</h2>
            <h3 id="detailSubtitle" class="subtitle">{{ $playlist->user->nombre ?? 'Usuario' }}</h3>
            <span id="detailUpdate" class="update">Actualizado {{ $playlist->updated_at->diffForHumans() }}</span>
            <p id="detailDescription" class="description">{{ $playlist->descripcion ?? '(Sin descripción)' }}</p>
            <div class="actions">
              <button class="btn play"><i class="fa-solid fa-play"></i> Reproducir</button>
              <button class="btn shuffle"><i class="fa-solid fa-shuffle"></i> Aleatorio</button>
            </div>
          </div>
        </div>

        {{-- ====== CANCIONES DE LA PLAYLIST ====== --}}
        <section class="likes-section">
          <h3 class="likes-header"><i class="fa-solid fa-music"></i> Canciones en esta playlist</h3>

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

                <!-- botón invisible para el reproductor -->
                <button class="cancion-item"
                        style="display:none"
                        data-id="{{ $song->id }}"
                        data-src="{{ $audioUrl }}"
                        data-title="{{ $song->title }}"
                        data-artist="{{ $song->artist ?? 'Desconocido' }}"
                        data-cover="{{ $coverUrl }}">
                </button>

                <!-- Botón para quitar canción -->
                <form action="{{ route('playlists.removeSong', [$playlist->id, $song->id]) }}"
                      method="POST"
                      onsubmit="return confirm('¿Quitar esta canción de la playlist?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="delete-btn"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            @empty
              <p class="empty-msg"><i class="fa-solid fa-circle-exclamation"></i> Esta playlist aún no tiene canciones.</p>
            @endforelse
          </div>
        </section>
        @endisset
      </div>

      {{-- ====== MODAL CREAR PLAYLIST ====== --}}
      <div id="playlistModalBackdrop" class="modal-backdrop" hidden></div>
      <div id="playlistModal" class="modal" hidden role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <button class="modal-close" id="btnClosePlaylistModal" aria-label="Cerrar">×</button>
        <h3 id="modalTitle" class="modal-title traducible">Añadir Playlist</h3>
        <form id="playlistForm" action="{{ route('playlists.store') }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf
          {{-- ... inputs de nombre, portada, descripción y link (igual a tu código anterior) ... --}}
        </form>
      </div>
    </main>
    @include('components.footer')
  </div>
</div>
</body>
</html>
