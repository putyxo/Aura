<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Playlists</title>

  {{-- Fuente + Iconos --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- Vite --}}
  @vite(['resources/css/playlist.css','resources/js/playlist.js'])
</head>
<body>
<div class="app">
  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')
    <main class="main-content playlist-page" data-max-size-mb="5">
      <div class="shell">

        {{-- ================== HERO ================== --}}
        <section class="hero">
          <div class="hero__head">
            <div class="hero__title">
              <h1 class="title">Tus Playlists</h1>
              <p class="sub">Organiza tu música y crea la banda sonora perfecta para cada momento.</p>
            </div>

            <div class="hero__actions">
              <button class="btn-primary" id="btnOpenPlaylistModal">
                <i class="fa-solid fa-plus"></i> <span>Nueva playlist</span>
              </button>
            </div>
          </div>

          {{-- Barra debajo del título (como en tu 2ª imagen) --}}
          <div class="hero__toolbar" role="toolbar" aria-label="Herramientas de playlists">
            <div class="toolbar__left">
              <div class="search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="plSearch" type="search" placeholder="Buscar playlist..." aria-label="Buscar playlist" autocomplete="off">
                <button class="clear" id="clearSearch" aria-label="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></button>
              </div>

              <div class="filters">
                <button class="chip is-active" data-sort="recentes">Recientes</button>
                <button class="chip" data-sort="az">A–Z</button>
                <button class="chip" data-sort="cantidad">Más canciones</button>
              </div>
            </div>

            <div class="toolbar__right">
              <button class="btn-ghost" id="btnSelectMode">
                <i class="fa-regular fa-square"></i><span class="btn-text">Seleccionar</span>
              </button>
              <button class="btn-ghost danger" id="btnDeleteSelected" disabled>
                <i class="fa-regular fa-trash-can"></i><span class="btn-text">Eliminar</span>
              </button>
            </div>
          </div>
        </section>

        {{-- Toast --}}
        @if(session('ok'))
          <div class="toast toast-ok" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif

        {{-- ================== GRID ================== --}}
        <section class="playlist-grid skeletonize" id="playlistGrid" data-count="{{ count($playlists ?? []) }}">
          {{-- Crear nueva --}}
          <button type="button" class="tile tile-create no-select" id="btnOpenPlaylistModal2">
            <div class="tile-cover create-cover" aria-hidden="true">
              <i class="fa-solid fa-plus"></i>
            </div>
            <div class="tile-name">Nueva playlist</div>
          </button>

          {{-- Playlists --}}
          @forelse($playlists as $pl)
            <a href="{{ route('playlists.show', $pl->id) }}"
               class="tile"
               title="{{ $pl->nombre }}"
               data-name="{{ Str::lower($pl->nombre) }}"
               data-count="{{ $pl->canciones_count ?? 0 }}"
               data-id="{{ $pl->id }}">
              <div class="tile-cover">
                @if($pl->cover_url)
                  <img src="{{ $pl->cover_url }}" alt="Portada de {{ $pl->nombre }}" width="260" height="260" loading="lazy" decoding="async">
                @else
                  <div class="cover-placeholder">Sin portada</div>
                @endif
                <button type="button" class="play-btn" data-action="quick-play" aria-label="Reproducir {{ $pl->nombre }}">
                  <i class="fa-solid fa-play"></i>
                </button>
                <input type="checkbox" class="bulk-check" aria-label="Seleccionar {{ $pl->nombre }}" hidden>
              </div>
              <div class="tile-name">{{ $pl->nombre }}</div>
              <div class="tile-count">{{ $pl->canciones_count ?? 0 }} canciones</div>
            </a>
          @empty
            <div class="tile empty-state" style="grid-column:1/-1;">
              <i class="fa-solid fa-music"></i>
              <h3>Aún no tienes playlists</h3>
              <p>¡Crea la primera para comenzar! ✨</p>
            </div>
          @endforelse

          {{-- Skeletons --}}
          <template id="skeletonTemplate">
            <div class="tile skeleton">
              <div class="tile-cover"></div>
              <div class="tile-name"></div>
              <div class="tile-count"></div>
            </div>
          </template>
        </section>

        {{-- (Opcional) tarjeta detalle, se queda oculta por defecto --}}
        <section id="playlistDetail" class="playlist-card" hidden>
          <div class="cover">
            <img id="detailCover" src="" alt="Portada Playlist" width="220" height="220" decoding="async">
          </div>
          <div class="info">
            <h2 id="detailTitle" class="title"></h2>
            <span id="detailCount" class="count"></span>
            <h3 id="detailSubtitle" class="subtitle"></h3>
            <span id="detailUpdate" class="update"></span>
            <p id="detailDescription" class="description"></p>
            <div class="actions">
              <button class="btn-primary"><i class="fa-solid fa-play"></i> Reproducir</button>
              <button class="btn-secondary"><i class="fa-solid fa-shuffle"></i> Aleatorio</button>
              <button class="btn-secondary" id="btnCloseDetail"><i class="fa-solid fa-xmark"></i> Cerrar</button>
            </div>
          </div>
        </section>

      </div>
      {{-- Backdrop + Modal --}}
      <div id="playlistModalBackdrop" class="modal-backdrop" hidden></div>

      <div id="playlistModal" class="modal" hidden role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <button class="modal-close" id="btnClosePlaylistModal" aria-label="Cerrar">×</button>
        <h3 id="modalTitle" class="modal-title">Añadir Playlist</h3>

        <form id="playlistForm" action="{{ route('playlists.store') }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf

          <div class="modal-grid">
            <div class="field" aria-live="polite">
              <label for="pl_nombre" class="label">Nombre del álbum</label>
              <input id="pl_nombre" name="nombre" type="text" class="input" placeholder="(NOMBRE DEL ÁLBUM)" required minlength="3" maxlength="80">
              <small class="field-msg" id="nameMsg"></small>
            </div>

            <div class="cover-field">
              <label class="label">Portada</label>
              <label for="pl_cover" class="cover-uploader" id="coverDrop" tabindex="0" aria-label="Arrastra y suelta una imagen o presiona para seleccionar">
                <input id="pl_cover" name="portada" type="file" accept="image/*" hidden>
                <img id="coverPreview" alt="Vista previa de la portada" />
                <div class="uploader-hint" id="uploaderHint">
                  <div class="uploader-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
                  <div class="uploader-text">
                    <strong>Arrastra y suelta</strong> la imagen<br>
                    <span class="muted">o haz clic para seleccionar</span>
                  </div>
                  <div class="uploader-meta">
                    <span class="badge">PNG/JPG</span>
                    <span class="badge">Máx 5MB</span>
                  </div>
                </div>
                <div class="uploader-overlay" id="uploaderOverlay">Suelta la imagen aquí</div>
              </label>
              <small class="hint">PNG/JPG hasta 5MB</small>
              <small class="field-msg" id="coverMsg"></small>
            </div>

            <div class="field field-span2" aria-live="polite">
              <label for="pl_desc" class="label">Descripción</label>
              <textarea id="pl_desc" name="descripcion" class="textarea" rows="4" placeholder="(Descripción)" maxlength="300"></textarea>
              <small class="field-msg" id="descMsg"></small>
            </div>
          </div>

          <div class="actions">
            <button type="button" class="btn-secondary" id="btnCancelPlaylist">Cancelar</button>
            <button type="submit" class="btn-primary" id="btnSubmit">Guardar</button>
          </div>
        </form>
      </div>
    </main>

    @include('components.footer')
  </div>
</div>
</body>
</html>
