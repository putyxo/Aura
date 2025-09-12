<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Subir Música</title>

  <!-- Fuentes + Iconos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Vite -->
  @vite(['resources/css/subir.css','resources/js/subir.js'])
</head>
<body>
<div class="app">
  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    <!-- ROOT AISLADO -->
    <main id="axupRoot"
          class="axup axup-main main-content"
          data-axup-max-mb="50"
          data-user-id="{{ auth()->id() ?? 'guest' }}"
    >
      <div class="axup-shell">

        <!-- HERO -->
        <section class="axup-hero" aria-label="Subir Música">
          <div class="axup-hero__bg"></div>

          <div class="axup-hero__row">
            <div class="axup-hero__content">
              <div class="axup-hero__icon">
                <i class="fa-solid fa-upload" aria-hidden="true"></i>
              </div>
              <div>
                <h1 class="axup-hero__title">Subir Música</h1>
                <p class="axup-hero__sub">Comparte tu talento con el mundo. Sube canciones individuales o álbumes completos.</p>
              </div>
            </div>
            <div class="axup-hero__actions">
              <button class="axup-btn axup-btn-primary" id="axupOpenModalSong">
                <i class="fa-solid fa-music"></i> <span>Subir Canción</span>
              </button>
              <button class="axup-btn axup-btn-secondary" id="axupOpenModalAlbum">
                <i class="fa-solid fa-compact-disc"></i> <span>Crear Álbum</span>
              </button>
            </div>
          </div>

          <div class="axup-hero__toolbar" role="toolbar" aria-label="Herramientas de subir">
            <div class="axup-toolbar__left">
              <div class="axup-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="axupSearch" type="search" placeholder="Buscar subidas..." aria-label="Buscar subidas" autocomplete="off">
                <button class="axup-clear" id="axupClearSearch" aria-label="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <div class="axup-filters">
                <button class="axup-chip is-active" data-filter="all">Todas</button>
                <button class="axup-chip" data-filter="songs">Canciones</button>
                <button class="axup-chip" data-filter="albums">Álbumes</button>
                <button class="axup-chip" data-filter="recent">Recientes</button>
              </div>
            </div>
            <div class="axup-toolbar__right">
              <button class="axup-btn axup-btn-ghost" id="axupSelectMode" aria-pressed="false">
                <i class="fa-regular fa-square"></i><span class="axup-btn-text">Seleccionar</span>
              </button>
              <button class="axup-btn axup-btn-ghost axup-is-danger" id="axupDeleteSelected" disabled>
                <i class="fa-regular fa-trash-can"></i><span class="axup-btn-text">Eliminar</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Toast por sesión -->
        @if(session('ok'))
          <div class="axup-toast axup-toast-ok is-shown" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif
        @if(session('error'))
          <div class="axup-toast axup-toast-err is-shown" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-xmark"></i>
            <span>{{ session('error') }}</span>
          </div>
        @endif
        <!-- Toast runtime -->
        <div id="axupToast" class="axup-toast" role="status" aria-live="polite" hidden></div>

        <!-- GRID DE OPCIONES -->
        <section class="axup-grid" id="axupGrid">
          <!-- Opción Canción -->
          <div class="axup-tile axup-upload-option" data-type="song">
            <div class="axup-tile-cover">
              <div class="axup-option-icon">
                <i class="fa-solid fa-music"></i>
              </div>
            </div>
            <div class="axup-tile-name">Canción Individual</div>
            <div class="axup-tile-desc">Sube una sola pista con su portada personalizada</div>
            <button class="axup-btn axup-btn-primary axup-tile-action">
              <i class="fa-solid fa-plus"></i> Subir Canción
            </button>
          </div>

          <!-- Opción Álbum -->
          <div class="axup-tile axup-upload-option" data-type="album">
            <div class="axup-tile-cover">
              <div class="axup-option-icon">
                <i class="fa-solid fa-compact-disc"></i>
              </div>
            </div>
            <div class="axup-tile-name">Álbum Completo</div>
            <div class="axup-tile-desc">Sube múltiples canciones con una portada del álbum</div>
            <button class="axup-btn axup-btn-secondary axup-tile-action">
              <i class="fa-solid fa-plus"></i> Crear Álbum
            </button>
          </div>
        </section>

      </div>

      <!-- Backdrop + Modal (subir canción) -->
      <div id="axupModalBackdrop" class="axup-modal-backdrop" hidden></div>

      <div id="axupModalSong" class="axup-modal" hidden role="dialog" aria-modal="true" aria-labelledby="axupModalSongTitle">
        <form id="axupFormSong" class="axup-modal-form" action="{{ route('songs.store') }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf
          <div class="axup-modal-header">
            <h3 id="axupModalSongTitle" class="axup-modal-title">Subir Canción</h3>
            <button class="axup-modal-close" id="axupCloseModalSong" type="button" aria-label="Cerrar">×</button>
          </div>

          <div class="axup-modal-body">
            <p class="axup-modal-desc">Sube una canción individual con su portada personalizada.</p>
          </div>

          <div class="axup-modal-grid">
            <div class="axup-field" id="axupFieldSongNombre">
              <label class="axup-label" for="axupSongNombre">Nombre de la canción</label>
              <input class="axup-input" id="axupSongNombre" name="nombre" type="text" placeholder="Título de tu canción" required>
              <div class="axup-field-msg" id="axupMsgSongNombre"></div>
            </div>

            <div class="axup-field" id="axupFieldSongFile">
              <label class="axup-label" for="axupSongFile">Archivo de audio</label>
              <div class="axup-file-uploader" id="axupSongFileUploader">
                <div class="axup-uploader-hint">
                  <i class="fa-solid fa-music"></i>
                  <p>Arrastra tu archivo MP3 aquí o haz clic para seleccionar</p>
                  <small>Formatos: MP3 (máx. 50MB)</small>
                </div>
                <input type="file" id="axupSongFile" name="mp3" accept=".mp3,audio/mpeg" hidden required>
              </div>
              <div class="axup-field-msg" id="axupMsgSongFile"></div>
            </div>

            <div class="axup-field" id="axupFieldSongCover">
              <label class="axup-label" for="axupSongCover">Portada (opcional)</label>
              <div class="axup-cover-uploader" id="axupSongCoverUploader">
                <div class="axup-uploader-hint">
                  <i class="fa-solid fa-image"></i>
                  <p>Arrastra una imagen aquí o haz clic para seleccionar</p>
                  <small>Formatos: JPG, PNG, WEBP (máx. 5MB)</small>
                </div>
                <input type="file" id="axupSongCover" name="portada" accept="image/*" hidden>
              </div>
              <div class="axup-field-msg" id="axupMsgSongCover"></div>
            </div>
          </div>

          <div class="axup-actions">
            <button type="button" class="axup-btn axup-btn-secondary" id="axupCancelSong">Cancelar</button>
            <button type="submit" class="axup-btn axup-btn-primary" id="axupSubmitSong">
              <i class="fa-solid fa-upload"></i> Subir Canción
            </button>
          </div>
        </form>
      </div>

      <!-- Modal (crear álbum) -->
      <div id="axupModalAlbum" class="axup-modal" hidden role="dialog" aria-modal="true" aria-labelledby="axupModalAlbumTitle">
        <form id="axupFormAlbum" class="axup-modal-form" action="{{ route('albums.store') }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf
          <div class="axup-modal-header">
            <h3 id="axupModalAlbumTitle" class="axup-modal-title">Crear Álbum</h3>
            <button class="axup-modal-close" id="axupCloseModalAlbum" type="button" aria-label="Cerrar">×</button>
          </div>

          <div class="axup-modal-body">
            <p class="axup-modal-desc">Crea un álbum completo con múltiples canciones y una portada.</p>
          </div>

          <div class="axup-modal-grid">
            <div class="axup-field" id="axupFieldAlbumTitle">
              <label class="axup-label" for="axupAlbumTitle">Título del álbum</label>
              <input class="axup-input" id="axupAlbumTitle" name="title" type="text" placeholder="Título de tu álbum" required>
              <div class="axup-field-msg" id="axupMsgAlbumTitle"></div>
            </div>

            <div class="axup-field" id="axupFieldAlbumCover">
              <label class="axup-label" for="axupAlbumCover">Portada del álbum</label>
              <div class="axup-cover-uploader" id="axupAlbumCoverUploader">
                <div class="axup-uploader-hint">
                  <i class="fa-solid fa-image"></i>
                  <p>Arrastra una imagen aquí o haz clic para seleccionar</p>
                  <small>Formatos: JPG, PNG, WEBP (máx. 5MB)</small>
                </div>
                <input type="file" id="axupAlbumCover" name="cover" accept="image/*" hidden required>
              </div>
              <div class="axup-field-msg" id="axupMsgAlbumCover"></div>
            </div>

            <div class="axup-field axup-field-full" id="axupFieldAlbumTracks">
              <label class="axup-label" for="axupAlbumTracks">Canciones del álbum</label>
              <div class="axup-tracks-uploader" id="axupAlbumTracksUploader">
                <div class="axup-uploader-hint">
                  <i class="fa-solid fa-compact-disc"></i>
                  <p>Arrastra múltiples archivos MP3 aquí o haz clic para seleccionar</p>
                  <small>Formatos: MP3 (máx. 50MB cada uno)</small>
                </div>
                <input type="file" id="axupAlbumTracks" name="tracks[]" accept=".mp3,audio/mpeg" multiple hidden required>
              </div>
              <div id="axupTracksList" class="axup-tracks-list"></div>
              <div class="axup-field-msg" id="axupMsgAlbumTracks"></div>
            </div>
          </div>

          <div class="axup-actions">
            <button type="button" class="axup-btn axup-btn-secondary" id="axupCancelAlbum">Cancelar</button>
            <button type="submit" class="axup-btn axup-btn-primary" id="axupSubmitAlbum">
              <i class="fa-solid fa-plus"></i> Crear Álbum
            </button>
          </div>
        </form>
      </div>
    </main>

    @include('components.footer')
  </div>
</div>
<script>
  // Set global variables
  window.userId = @json(Auth::id());
</script>

<!-- Guard-rails de layout: calcula márgenes seguros según sidebar/header/footer/player -->
<script>
(() => {
  const root = document.querySelector('#axupRoot.axup');

  function setVar(name, px){
    const v = (Math.max(0, Math.round(px || 0))) + 'px';
    document.documentElement.style.setProperty(name, v);
    root?.style.setProperty(name, v);
  }

  function widthIfDockedLeft(el){
    if(!el) return 0;
    const r = el.getBoundingClientRect();
    return Math.abs(r.left) < 2 ? r.width : 0;
  }

  function widthIfDockedRight(el){
    if(!el) return 0;
    const r = el.getBoundingClientRect();
    return Math.abs(window.innerWidth - r.right) < 2 ? r.width : 0;
  }

  function heightIfDockedTop(el){
    if(!el) return 0;
    const r = el.getBoundingClientRect();
    return r.top <= 0 ? r.height : 0;
  }

  function heightIfDockedBottom(el){
    if(!el) return 0;
    const r = el.getBoundingClientRect();
    return Math.abs(window.innerHeight - r.bottom) < 2 ? r.height : 0;
  }

  function measure(){
    const sidebar = document.querySelector('.sidebar') || document.querySelector('[class*="side"]');
    const player  = document.querySelector('.player, .right-player') || document.querySelector('[class*="player"]');
    const header  = document.querySelector('.header') || document.querySelector('header');
    const footer  = document.querySelector('.footer') || document.querySelector('footer');

    setVar('--safe-left',   widthIfDockedLeft(sidebar));
    setVar('--safe-right',  widthIfDockedRight(player));
    setVar('--safe-top',    heightIfDockedTop(header));
    setVar('--safe-bottom', heightIfDockedBottom(footer));
  }

  const ro = new ResizeObserver(measure);
  ['.sidebar','[class*="side"]','.player','.right-player','[class*="player"]','.header','header','.footer','footer']
    .forEach(sel => document.querySelectorAll(sel).forEach(el => ro.observe(el)));

  window.addEventListener('resize', measure);
  window.addEventListener('orientationchange', measure);
  document.addEventListener('DOMContentLoaded', measure);
  measure();
})();
</script>
</body>
</html>
