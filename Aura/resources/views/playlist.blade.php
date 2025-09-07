<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Playlists</title>

  {{-- Preconnect + Fonts (reduce FOUT/CLS) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">

  {{-- Vite --}}
  @vite(['resources/css/playlist.css','resources/js/playlist.js'])

  {{-- CSS crítico para evitar “flash” de layout si el CSS principal tarda en llegar --}}

  {{-- Iconos --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app">
  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')

    <main class="main-content playlist-page">
      <div class="shell">

        {{-- ====== HEADER ====== --}}
        <section class="playlist-header">
          <h1 class="playlist-title">Tus Playlists</h1>
          <p class="playlist-subtitle">Organiza tu música y crea la banda sonora perfecta para cada momento</p>
          <div class="header-actions">
            <button class="btn-secondary" id="accessPlaylistLink">
              <i class="fa-solid fa-link"></i> Acceder con enlace
            </button>
          </div>
        </section>

        {{-- Toast OK --}}
        @if(session('ok'))
          <div class="toast toast-ok">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif

        {{-- ====== GRID ====== --}}
        <section class="playlist-grid">
          {{-- Nueva playlist --}}
          <button type="button" class="tile tile-create" id="btnOpenPlaylistModal">
            <div class="tile-cover create-cover" aria-hidden="true">
              <i class="fa-solid fa-plus"></i>
            </div>
            <div class="tile-name traducible">Nueva playlist</div>
          </button>

          {{-- Playlists desde BD --}}
          @forelse($playlists as $pl)
            <a href="{{ route('playlists.show', $pl->id) }}" class="tile" title="{{ $pl->nombre }}">
              <div class="tile-cover">
                @if($pl->cover_url)
                  <img
                    src="{{ $pl->cover_url }}"
                    alt="Portada de {{ $pl->nombre }}"
                    width="280" height="280"
                    loading="lazy" decoding="async" fetchpriority="low">
                @else
                  <div class="cover-placeholder traducible">Sin portada</div>
                @endif
                <div class="play-btn"><i class="fa-solid fa-play"></i></div>
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
        </section>

        {{-- ====== DETALLE (opcional) ====== --}}
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

      </div> {{-- /.shell --}}

      {{-- ====== BACKDROP MODAL ====== --}}
      <div id="playlistModalBackdrop" class="modal-backdrop" hidden></div>

      {{-- ====== MODAL CREAR PLAYLIST ====== --}}
      <div id="playlistModal" class="modal" hidden role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <button class="modal-close" id="btnClosePlaylistModal" aria-label="Cerrar">×</button>
        <h3 id="modalTitle" class="modal-title traducible">Añadir Playlist</h3>

        <form id="playlistForm" action="{{ route('playlists.store') }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf

          <div class="modal-grid">
            {{-- Nombre --}}
            <div class="field" aria-live="polite">
              <label for="pl_nombre" class="label traducible">Nombre del álbum</label>
              <input id="pl_nombre" name="nombre" type="text" class="input" placeholder="(NOMBRE DEL ÁLBUM)" required>
              <small class="field-msg" id="nameMsg"></small>
            </div>

            {{-- Portada --}}
            <div class="cover-field">
              <label class="label traducible">Portada</label>
              <label for="pl_cover" class="cover-uploader" id="coverDrop" tabindex="0"
                     aria-label="Arrastra y suelta una imagen o presiona para seleccionar">
                <input id="pl_cover" name="portada" type="file" accept="image/*" hidden>
                <img id="coverPreview" alt="Vista previa de la portada" />
                <div class="uploader-hint" id="uploaderHint">
                  <div class="uploader-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
                  <div class="uploader-text traducible">
                    <strong>Arrastra y suelta</strong> la imagen<br>
                    <span class="muted">o haz clic para seleccionar</span>
                  </div>
                  <div class="uploader-meta">
                    <span class="badge">PNG/JPG</span>
                    <span class="badge">Máx 5MB</span>
                  </div>
                </div>
                <div class="uploader-overlay traducible" id="uploaderOverlay">Suelta la imagen aquí</div>
              </label>
              <small class="hint traducible">PNG/JPG hasta 5MB</small>
              <small class="field-msg" id="coverMsg"></small>
            </div>

            {{-- Descripción --}}
            <div class="field field-span2" aria-live="polite">
              <label for="pl_desc" class="label traducible">Descripción</label>
              <textarea id="pl_desc" name="descripcion" class="textarea" rows="4" placeholder="(Descripción)"></textarea>
              <small class="field-msg" id="descMsg"></small>
            </div>
          </div>

          <div class="actions">
            <button type="button" class="btn-secondary traducible" id="btnCancelPlaylist">Cancelar</button>
            <button type="submit" class="btn-primary traducible" id="btnSubmit">Guardar</button>
          </div>
        </form>
      </div>

      {{-- ====== MODAL ACCESO POR ENLACE ====== --}}
      <div id="shareLinkModal" class="modal" hidden>
        <button class="modal-close" id="closeShareLinkModal">×</button>
        <h3 class="modal-title">Acceder a Playlist</h3>
        <input type="text" id="shareLinkInput" placeholder="Pega el enlace aquí" />
        <button id="submitShareLink" class="btn-primary">Acceder</button>
      </div>

    </main>

    @include('components.footer')
  </div>
</div>
</body>
</html>
