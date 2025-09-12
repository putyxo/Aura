<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Recientes</title>

  <!-- Fuentes + Iconos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Vite -->
  @vite(['resources/css/recientes.css','resources/js/recientes.js'])
</head>
<body>
<div class="app">
  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    <!-- ROOT AISLADO -->
    <main id="axrcRoot"
          class="axrc axrc-main main-content"
          data-axrc-max-mb="5"
          data-user-id="{{ auth()->id() ?? 'guest' }}"
    >
      <div class="axrc-shell">

        <!-- HERO -->
        <section class="axrc-hero" aria-label="Tus Canciones Recientes">
          <div class="axrc-hero__bg"></div>

          <div class="axrc-hero__row">
            <div class="axrc-hero__content">
              <div class="axrc-hero__icon">
                <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
              </div>
              <div>
                <h1 class="axrc-hero__title">Tus Canciones Recientes</h1>
                <p class="axrc-hero__sub">Revisa las canciones que has escuchado recientemente.</p>
              </div>
            </div>
            <div class="axrc-hero__actions">
              <button class="axrc-btn axrc-btn-primary" id="axrcClearHistory">
                <i class="fa-solid fa-trash"></i> <span>Limpiar Historial</span>
              </button>
            </div>
          </div>

          <div class="axrc-hero__toolbar" role="toolbar" aria-label="Herramientas de recientes">
            <div class="axrc-toolbar__left">
              <div class="axrc-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="axrcSearch" type="search" placeholder="Buscar canción..." aria-label="Buscar canción" autocomplete="off">
                <button class="axrc-clear" id="axrcClearSearch" aria-label="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <div class="axrc-filters">
                <button class="axrc-chip is-active" data-sort="recientes">Recientes</button>
                <button class="axrc-chip" data-sort="hoy">Hoy</button>
                <button class="axrc-chip" data-sort="semana">Esta semana</button>
                <button class="axrc-chip" data-sort="mes">Este mes</button>
              </div>
            </div>
            <div class="axrc-toolbar__right">
              <button class="axrc-btn axrc-btn-ghost" id="axrcSelectMode" aria-pressed="false">
                <i class="fa-regular fa-square"></i><span class="axrc-btn-text">Seleccionar</span>
              </button>
              <button class="axrc-btn axrc-btn-ghost axrc-is-danger" id="axrcDeleteSelected" disabled>
                <i class="fa-regular fa-trash-can"></i><span class="axrc-btn-text">Eliminar</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Toast por sesión -->
        @if(session('ok'))
          <div class="axrc-toast axrc-toast-ok is-shown" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif
        <!-- Toast runtime -->
        <div id="axrcToast" class="axrc-toast" role="status" aria-live="polite" hidden></div>

        <!-- GRID -->
        <section class="axrc-grid" id="axrcGrid">
          <!-- Empty state -->
          <div class="axrc-tile axrc-empty-state" id="axrcEmptyState" style="grid-column:1/-1;">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <h3>Aún no se ha reproducido ninguna canción</h3>
            <p>¡Empieza a escuchar música para ver tu historial! 🎵</p>
          </div>
        </section>

      </div>

      <!-- Backdrop + Modal (confirmar limpiar) -->
      <div id="axrcModalBackdrop" class="axrc-modal-backdrop" hidden></div>

      <div id="axrcModal" class="axrc-modal" hidden role="dialog" aria-modal="true" aria-labelledby="axrcModalTitle">
        <form id="axrcForm" action="#" method="POST" novalidate>
          @csrf
          <div class="axrc-modal-header">
            <h3 id="axrcModalTitle" class="axrc-modal-title">Limpiar Historial</h3>
            <button class="axrc-modal-close" id="axrcCloseModal" type="button" aria-label="Cerrar">×</button>
          </div>

          <div class="axrc-modal-body">
            <p>¿Estás seguro de que quieres limpiar todo el historial de canciones reproducidas? Esta acción no se puede deshacer.</p>
          </div>

          <div class="axrc-actions">
            <button type="button" class="axrc-btn axrc-btn-secondary" id="axrcCancel">Cancelar</button>
            <button type="button" class="axrc-btn axrc-btn-danger" id="axrcConfirmClear">
              <i class="fa-regular fa-trash-can"></i> Limpiar
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
  window.defaultCover = '{{ asset('img/default-cancion.png') }}';
</script>

<!-- Guard-rails de layout: calcula márgenes seguros según sidebar/header/footer/player -->
<script>
(() => {
  const root = document.querySelector('#axrcRoot.axrc');

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
