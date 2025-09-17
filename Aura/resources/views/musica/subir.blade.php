<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — {{ __('upload.title') }}</title>

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
                <h1 class="axup-hero__title">{{ __('upload.title') }}</h1>
                <p class="axup-hero__sub">{{ __('upload.subtitle') }}</p>
              </div>
            </div>
            <div class="axup-hero__actions">
              <button class="axup-btn axup-btn-primary" id="axupOpenModalSong" type="button">
                <i class="fa-solid fa-music"></i> <span>{{ __('upload.upload_song') }}</span>
              </button>
              <button class="axup-btn axup-btn-secondary" id="axupOpenModalAlbum" type="button">
                <i class="fa-solid fa-compact-disc"></i> <span>{{ __('upload.create_album') }}</span>
              </button>
            </div>
          </div>

          <div class="axup-hero__toolbar" role="toolbar" aria-label="Herramientas de subir">
            <div class="axup-toolbar__left">
              <div class="axup-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="axupSearch" type="search" placeholder="{{ __('upload.search_placeholder') }}" aria-label="{{ __('upload.search_placeholder') }}" autocomplete="off">
                <button class="axup-clear" id="axupClearSearch" aria-label="Limpiar búsqueda" type="button"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <div class="axup-filters">
                <button class="axup-chip is-active" data-filter="all" type="button">{{ __('upload.filter_all') }}</button>
                <button class="axup-chip" data-filter="songs" type="button">{{ __('upload.filter_songs') }}</button>
                <button class="axup-chip" data-filter="albums" type="button">{{ __('upload.filter_albums') }}</button>
                <button class="axup-chip" data-filter="recent" type="button">{{ __('upload.filter_recent') }}</button>
              </div>
            </div>
            <div class="axup-toolbar__right">
              <button class="axup-btn axup-btn-ghost" id="axupSelectMode" aria-pressed="false" type="button">
                <i class="fa-regular fa-square"></i><span class="axup-btn-text">{{ __('upload.select_mode') }}</span>
              </button>
              <button class="axup-btn axup-btn-ghost axup-is-danger" id="axupDeleteSelected" type="button" disabled>
                <i class="fa-regular fa-trash-can"></i><span class="axup-btn-text">{{ __('upload.delete_selected') }}</span>
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
            <div class="axup-tile-name">{{ __('upload.song_option_title') }}</div>
            <div class="axup-tile-desc">{{ __('upload.song_option_desc') }}</div>
            <button class="axup-btn axup-btn-primary axup-tile-action" type="button">
              <i class="fa-solid fa-plus"></i> {{ __('upload.song_option_button') }}
            </button>
          </div>

          <!-- Opción Álbum -->
          <div class="axup-tile axup-upload-option" data-type="album">
            <div class="axup-tile-cover">
              <div class="axup-option-icon">
                <i class="fa-solid fa-compact-disc"></i>
              </div>
            </div>
            <div class="axup-tile-name">{{ __('upload.album_option_title') }}</div>
            <div class="axup-tile-desc">{{ __('upload.album_option_desc') }}</div>
            <button class="axup-btn axup-btn-secondary axup-tile-action" type="button">
              <i class="fa-solid fa-plus"></i> {{ __('upload.album_option_button') }}
            </button>
          </div>
        </section>

      </div>

      <!-- Backdrop + Modals -->
      <div id="axupModalBackdrop" class="axup-modal-backdrop" aria-hidden="true">
        <!-- Modal (subir canción) -->
        <div id="axupModalSong" class="axup-modal" role="dialog" aria-modal="true" aria-labelledby="axupModalSongTitle" aria-hidden="true">
          <form id="axupFormSong" class="axup-modal-form" action="{{ route('songs.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="axup-modal-header">
              <h3 id="axupModalSongTitle" class="axup-modal-title">{{ __('upload.modal_song_title') }}</h3>
              <button class="axup-modal-close" id="axupCloseModalSong" type="button" aria-label="Cerrar">×</button>
            </div>

            <div class="axup-modal-body">
              <p class="axup-modal-desc">{{ __('upload.modal_song_desc') }}</p>
            </div>

            <div class="axup-modal-grid">
              <div class="axup-field" id="axupFieldSongNombre">
                <label class="axup-label" for="axupSongNombre">{{ __('upload.song_name_label') }}</label>
                <input class="axup-input" id="axupSongNombre" name="nombre" type="text" placeholder="{{ __('upload.song_name_placeholder') }}" required>
                <div class="axup-field-msg" id="axupMsgSongNombre"></div>
              </div>

              <div class="axup-field" id="axupFieldSongFile">
                <label class="axup-label" for="axupSongFile">{{ __('upload.song_file_label') }}</label>
                <div class="axup-file-uploader" id="axupSongFileUploader">
                  <div class="axup-uploader-hint">
                    <i class="fa-solid fa-music"></i>
                    <p>{{ __('upload.song_file_hint') }}</p>
                    <small>{{ __('upload.song_file_formats') }}</small>
                  </div>
                  <input type="file" id="axupSongFile" name="mp3" accept=".mp3,audio/mpeg" hidden required>
                </div>
                <div class="axup-field-msg" id="axupMsgSongFile"></div>
              </div>

              <div class="axup-field" id="axupFieldSongCover">
                <label class="axup-label" for="axupSongCover">{{ __('upload.song_cover_label') }}</label>
                <div class="axup-cover-uploader" id="axupSongCoverUploader">
                  <div class="axup-uploader-hint">
                    <i class="fa-solid fa-image"></i>
                    <p>{{ __('upload.song_cover_hint') }}</p>
                    <small>{{ __('upload.song_cover_formats') }}</small>
                  </div>
                  <input type="file" id="axupSongCover" name="portada" accept="image/*" hidden>
                </div>
                <div class="axup-field-msg" id="axupMsgSongCover"></div>
              </div>
            </div>

            <div class="axup-actions">
              <button type="button" class="axup-btn axup-btn-secondary" id="axupCancelSong">{{ __('Cancel') }}</button>
              <button type="submit" class="axup-btn axup-btn-primary" id="axupSubmitSong">
                <i class="fa-solid fa-upload"></i> {{ __('upload.upload_song') }}
              </button>
            </div>
          </form>
        </div>

        <!-- Modal (crear álbum) -->
        <div id="axupModalAlbum" class="axup-modal" role="dialog" aria-modal="true" aria-labelledby="axupModalAlbumTitle" aria-hidden="true">
          <form id="axupFormAlbum" class="axup-modal-form" action="{{ route('albums.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="axup-modal-header">
              <h3 id="axupModalAlbumTitle" class="axup-modal-title">{{ __('upload.modal_album_title') }}</h3>
              <button class="axup-modal-close" id="axupCloseModalAlbum" type="button" aria-label="Cerrar">×</button>
            </div>

            <div class="axup-modal-body">
              <p class="axup-modal-desc">{{ __('upload.modal_album_desc') }}</p>
            </div>

            <div class="axup-modal-grid">
              <div class="axup-field" id="axupFieldAlbumTitle">
                <label class="axup-label" for="axupAlbumTitle">{{ __('upload.album_title_label') }}</label>
                <input class="axup-input" id="axupAlbumTitle" name="title" type="text" placeholder="{{ __('upload.album_title_placeholder') }}" required>
                <div class="axup-field-msg" id="axupMsgAlbumTitle"></div>
              </div>

              <div class="axup-field" id="axupFieldAlbumCover">
                <label class="axup-label" for="axupAlbumCover">{{ __('upload.album_cover_label') }}</label>
                <div class="axup-cover-uploader" id="axupAlbumCoverUploader">
                  <div class="axup-uploader-hint">
                    <i class="fa-solid fa-image"></i>
                    <p>{{ __('upload.song_cover_hint') }}</p>
                    <small>{{ __('upload.song_cover_formats') }}</small>
                  </div>
                  <input type="file" id="axupAlbumCover" name="cover" accept="image/*" hidden required>
                </div>
                <div class="axup-field-msg" id="axupMsgAlbumCover"></div>
              </div>

              <div class="axup-field axup-field-full" id="axupFieldAlbumTracks">
                <label class="axup-label" for="axupAlbumTracks">{{ __('upload.album_tracks_label') }}</label>
                <div class="axup-tracks-uploader" id="axupAlbumTracksUploader">
                  <div class="axup-uploader-hint">
                    <i class="fa-solid fa-compact-disc"></i>
                    <p>{{ __('upload.album_tracks_hint') }}</p>
                    <small>{{ __('upload.album_tracks_formats') }}</small>
                  </div>
                  <input type="file" id="axupAlbumTracks" name="tracks[]" accept=".mp3,audio/mpeg" multiple hidden required>
                </div>
                <div id="axupTracksList" class="axup-tracks-list"></div>
                <div class="axup-field-msg" id="axupMsgAlbumTracks"></div>
              </div>
            </div>

            <div class="axup-actions">
              <button type="button" class="axup-btn axup-btn-secondary" id="axupCancelAlbum">{{ __('Cancel') }}</button>
              <button type="submit" class="axup-btn axup-btn-primary" id="axupSubmitAlbum">
                <i class="fa-solid fa-plus"></i> {{ __('upload.create_album') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>

    @include('components.footer')
  </div>
</div>
<script>
  // Set global variables
  window.userId = @json(Auth::id());
</script>

<!-- Guard-rails de layout -->
<script>
(() => {
  const root = document.querySelector('#axupRoot.axup');

  function setVar(name, px){
    const v = (Math.max(0, Math.round(px || 0))) + 'px';
    document.documentElement.style.setProperty(name, v);
    root?.style.setProperty(name, v);
  }

  function widthIfDockedLeft(el){ if(!el) return 0; const r = el.getBoundingClientRect(); return Math.abs(r.left) < 2 ? r.width : 0; }
  function widthIfDockedRight(el){ if(!el) return 0; const r = el.getBoundingClientRect(); return Math.abs(innerWidth - r.right) < 2 ? r.width : 0; }
  function heightIfDockedTop(el){ if(!el) return 0; const r = el.getBoundingClientRect(); return r.top <= 0 ? r.height : 0; }
  function heightIfDockedBottom(el){ if(!el) return 0; const r = el.getBoundingClientRect(); return Math.abs(innerHeight - r.bottom) < 2 ? r.height : 0; }

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

  addEventListener('resize', measure);
  addEventListener('orientationchange', measure);
  document.addEventListener('DOMContentLoaded', measure);
  measure();
})();
</script>

<!-- ==== PATCH: NO más doble selección de archivos ==== -->
<script>
(() => {
  if (window.__AXUP_PATCH_BOUND__) return;
  window.__AXUP_PATCH_BOUND__ = true;

  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  const backdrop   = $('#axupModalBackdrop');
  const modalSong  = $('#axupModalSong');
  const modalAlbum = $('#axupModalAlbum');

  const btnOpenSong   = $('#axupOpenModalSong');
  const btnOpenAlbum  = $('#axupOpenModalAlbum');
  const btnCloseSong  = $('#axupCloseModalSong');
  const btnCancelSong = $('#axupCancelSong');
  const btnCloseAlbum = $('#axupCloseModalAlbum');
  const btnCancelAlbum= $('#axupCancelAlbum');

  const zones = [
    $('#axupSongFileUploader'),
    $('#axupSongCoverUploader'),
    $('#axupAlbumCoverUploader'),
    $('#axupAlbumTracksUploader'),
  ].filter(Boolean);

  const inputs = [
    $('#axupSongFile'),
    $('#axupSongCover'),
    $('#axupAlbumCover'),
    $('#axupAlbumTracks'),
  ].filter(Boolean);

  // ---------- Helpers Modal ----------
  function openModal(which){
    if (!backdrop) return;
    backdrop.classList.add('is-open');
    backdrop.setAttribute('aria-hidden','false');
    const m = which === 'song' ? modalSong : modalAlbum;
    if (m){ m.classList.add('is-active'); m.setAttribute('aria-hidden','false'); }
  }
  function closeAll(){
    if (!backdrop) return;
    [modalSong, modalAlbum].forEach(m => { if (!m) return; m.classList.remove('is-active'); m.setAttribute('aria-hidden','true'); });
    backdrop.classList.remove('is-open');
    backdrop.setAttribute('aria-hidden','true');
  }

  btnOpenSong?.addEventListener('click', () => openModal('song'));
  btnOpenAlbum?.addEventListener('click', () => openModal('album'));
  btnCloseSong?.addEventListener('click', closeAll);
  btnCancelSong?.addEventListener('click', closeAll);
  btnCloseAlbum?.addEventListener('click', closeAll);
  btnCancelAlbum?.addEventListener('click', closeAll);

  // Cerrar solo si el gesto empieza y termina en el backdrop (evita cierres fantasma)
  let downOnBackdrop = false;
  backdrop?.addEventListener('pointerdown', (e) => {
    if (backdrop.dataset.blockClose === '1') return;
    downOnBackdrop = (e.target === backdrop);
  });
  backdrop?.addEventListener('pointerup', (e) => {
    if (backdrop.dataset.blockClose === '1') return;
    if (downOnBackdrop && e.target === backdrop) closeAll();
    downOnBackdrop = false;
  });
  [modalSong, modalAlbum].forEach(m => {
    m?.addEventListener('pointerdown', e => e.stopPropagation());
    m?.addEventListener('click', e => e.stopPropagation());
  });

  // ---------- Reentrancia / dedupe al abrir el file picker ----------
  function blockClose(){ if (backdrop) backdrop.dataset.blockClose = '1'; }
  function unblockClose(){ if (backdrop) delete backdrop.dataset.blockClose; }

  function openPicker(input){
    if (!input) return;

    // Si ya hay una apertura en curso, no repetir
    if (input.dataset.open === '1') return;
    input.dataset.open = '1';

    // Garantizar que 'change' se dispare aunque elijas el mismo archivo
    try { input.value = ''; } catch(_) {}

    blockClose();

    // showPicker si existe, sino click()
    if (typeof input.showPicker === 'function') {
      input.showPicker();
    } else {
      input.click();
    }

    const cleanup = () => {
      delete input.dataset.open;
      unblockClose();
    };

    // Al seleccionar o cancelar, liberar bloqueos
    input.addEventListener('change', cleanup, { once:true });
    // Algunos navegadores disparan 'cancel', otros no; no pasa nada si no existe
    input.addEventListener('cancel', cleanup, { once:true });

    // Failsafe por si algo dejó bloqueado
    setTimeout(() => { if (input.dataset.open === '1') cleanup(); }, 15000);
  }

  // Evitar doble-binding si otra parte del código también abre el file picker:
  // usamos captura + stopImmediatePropagation para que SOLO se ejecute una vez.
  zones.forEach(z => {
    if (z.dataset.wired === '1') return;
    z.dataset.wired = '1';

    // Captura: tomamos prioridad sobre otros listeners
    z.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      const input = z.parentElement?.querySelector('input[type="file"]');
      openPicker(input);
    }, { capture:true });

    // También prevenir que pointerdown genere side-effects
    z.addEventListener('pointerdown', (e) => {
      e.stopPropagation();
      // No cerramos el modal si el usuario empieza gesto aquí
      blockClose();
      setTimeout(unblockClose, 0);
    }, { capture:true });
  });

  // Por si el usuario hace click directo (no en la zona), también blindamos los inputs
  inputs.forEach(inp => {
    if (inp.dataset.wired === '1') return;
    inp.dataset.wired = '1';

    inp.addEventListener('click', (e) => {
      // Si alguien más llamó click(), cancelamos duplicados
      e.stopPropagation();
      if (inp.dataset.open === '1') { e.preventDefault(); return; }
      // Convertimos el click “directo” en openPicker para unificar lógica
      e.preventDefault();
      openPicker(inp);
    }, { capture:true });
  });

  // Ignorar clicks globales mientras el picker esté abierto (evita cierre al soltar)
  document.addEventListener('click', (e) => {
    if (backdrop?.dataset.blockClose === '1') e.stopPropagation();
  }, true);
})();
</script>
</body>
</html>
