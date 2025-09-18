<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Playlists</title>

  <!-- Fuentes + Iconos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Vite (sigue pudiendo cargar tu JS/CSS habitual) -->
  @vite(['resources/css/playlist.css','resources/js/playlist.js'])
</head>
<body>
<div class="app">
  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    @php
      // Estas URLs son informativas para el front.
      $updateBaseUrl = url('/playlists');               // PUT /playlists/{id}
      $showBaseUrl   = url('/playlists');               // GET /playlists/{id}
    @endphp

    <!-- ROOT AISLADO -->
    <main id="axplRoot"
          class="axpl axpl-main main-content"
          data-axpl-max-mb="5"
          data-update-base-url="{{ $updateBaseUrl }}"
          data-show-base-url="{{ $showBaseUrl }}"
          data-user-id="{{ auth()->id() ?? 'guest' }}"
    >
      <div class="axpl-shell">

        <!-- HERO -->
        <section class="axpl-hero" aria-label="Tus Playlists">
          <div class="axpl-hero__bg"></div>

          <div class="axpl-hero__row">
            <div class="axpl-hero__content">
              <div class="axpl-hero__icon">
                <i class="fa-solid fa-music" aria-hidden="true"></i>
              </div>
              <div>
                <h1 class="axpl-hero__title">{{ __('playlist.title') }}</h1>
                <p class="axpl-hero__sub">{{ __('playlist.subtitle') }}</p>
              </div>
            </div>
            <div class="axpl-hero__actions">
              <button class="axpl-btn axpl-btn-primary" id="axplOpenModal">
                <i class="fa-solid fa-plus"></i> <span>{{ __('playlist.new_playlist') }}</span>
              </button>
            </div>
          </div>

          <div class="axpl-hero__toolbar" role="toolbar" aria-label="Herramientas de playlists">
            <div class="axpl-toolbar__left">
              <div class="axpl-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input id="axplSearch" type="search" placeholder="{{ __('playlist.search_placeholder') }}" aria-label="{{ __('playlist.search_placeholder') }}" autocomplete="off">
                <button class="axpl-clear" id="axplClearSearch" aria-label="{{ __('playlist.clear_search') }}"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <div class="axpl-filters">
                <button class="axpl-chip is-active" data-sort="recientes">{{ __('playlist.filter_recent') }}</button>
                <button class="axpl-chip" data-sort="az">{{ __('playlist.filter_az') }}</button>
                <button class="axpl-chip" data-sort="cantidad">{{ __('playlist.filter_most_songs') }}</button>
              </div>

              <!-- Visible en modo selección -->
              <span id="axplSelectIndicator" class="axpl-select-ind" hidden>
                <i class="fa-regular fa-square-check"></i>
                <span><b id="axplSelCount">0</b> {{ __('playlist.selected_count') }}</span>
              </span>
            </div>
            <div class="axpl-toolbar__right">
              <button class="axpl-btn axpl-btn-ghost" id="axplSelectMode" aria-pressed="false">
                <i class="fa-regular fa-square"></i><span class="axpl-btn-text">{{ __('playlist.select') }}</span>
              </button>
              <button class="axpl-btn axpl-btn-ghost axpl-is-danger" id="axplDeleteSelected" disabled>
                <i class="fa-regular fa-trash-can"></i><span class="axpl-btn-text">{{ __('playlist.delete') }}</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Toast por sesión -->
        @if(session('ok'))
          <div class="axpl-toast axpl-toast-ok is-shown" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif
        <!-- Toast runtime -->
        <div id="axplToast" class="axpl-toast" role="status" aria-live="polite" hidden></div>

        <!-- GRID -->
        <section class="axpl-grid" id="axplGrid" data-count="{{ count($playlists ?? []) }}">
          <!-- Crear nueva -->
          <button type="button" class="axpl-tile axpl-tile-create axpl-no-select" id="axplOpenModal2">
            <div class="axpl-tile-cover axpl-create-cover" aria-hidden="true">
              <i class="fa-solid fa-plus"></i>
            </div>
            <div class="axpl-tile-name">{{ __('playlist.new_playlist') }}</div>
          </button>

          <!-- Playlists -->
          @forelse($playlists as $pl)
            <div
              class="axpl-tile"
              title="{{ $pl->nombre }}"
              data-name="{{ \Illuminate\Support\Str::lower($pl->nombre) }}"
              data-count="{{ $pl->canciones_count ?? 0 }}"
              data-id="{{ $pl->id }}"
              data-del="{{ route('playlists.destroy', $pl->id) }}"
            >
              <a href="{{ route('playlists.show', $pl->id) }}" class="axpl-tile-link" aria-label="Abrir {{ $pl->nombre }}"></a>

              <div class="axpl-tile-cover">
                <div class="axpl-cover-badge" aria-hidden="true"><i class="fa-solid fa-music"></i></div>

                @if($pl->cover_url)
                  <img src="{{ $pl->cover_url }}" alt="Portada de {{ $pl->nombre }}" width="260" height="260" loading="lazy" decoding="async">
                @else
                  <div class="axpl-cover-placeholder">{{ __('playlist.no_cover') }}</div>
                @endif

                <!-- Editar -->
                <button type="button"
                        class="axpl-pencil"
                        data-id="{{ $pl->id }}"
                        data-nombre="{{ $pl->nombre }}"
                        data-descripcion="{{ $pl->descripcion }}"
                        data-cover="{{ $pl->cover_url }}">
                  <i class="fa-solid fa-pen"></i>
                </button>

                <!-- Eliminar (nuevo) -->
                <button type="button"
                        class="axpl-trash"
                        title="Eliminar {{ $pl->nombre }}"
                        data-id="{{ $pl->id }}"
                        data-name="{{ $pl->nombre }}"
                        data-delete-url="{{ route('playlists.destroy', $pl->id) }}">
                  <i class="fa-solid fa-trash"></i>
                </button>

                <!-- Reproducir rápido -->
                <button type="button" class="axpl-play-btn" data-action="quick-play" aria-label="Reproducir {{ $pl->nombre }}">
                  <i class="fa-solid fa-play"></i>
                </button>

                <div class="axpl-selected-mark" aria-hidden="true"><i class="fa-solid fa-check"></i></div>
                <input type="checkbox" class="axpl-bulk-check" aria-label="Seleccionar {{ $pl->nombre }}" hidden>
              </div>

              <div class="axpl-tile-name">{{ $pl->nombre }}</div>
              <div class="axpl-tile-count">{{ $pl->canciones_count ?? 0 }} canciones</div>
            </div>
          @empty
            <div class="axpl-tile axpl-empty-state" style="grid-column:1/-1;">
              <i class="fa-solid fa-music"></i>
              <h3>Aún no tienes playlists</h3>
              <p>¡Crea la primera para comenzar! ✨</p>
            </div>
          @endforelse
        </section>

      </div>

      <!-- Backdrop + Modal (crear/editar) -->
      <div id="axplModalBackdrop" class="axpl-modal-backdrop" hidden></div>

      <div id="axplModal" class="axpl-modal" hidden role="dialog" aria-modal="true" aria-labelledby="axplModalTitle">
        <form id="axplForm" action="{{ route('playlists.store') }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf
          <div class="axpl-modal-header">
            <h3 id="axplModalTitle" class="axpl-modal-title">Añadir Playlist</h3>
            <button class="axpl-modal-close" id="axplCloseModal" type="button" aria-label="Cerrar">×</button>
          </div>

          <div class="axpl-modal-grid">
            <div class="axpl-field" id="axplFieldNombre" aria-live="polite">
              <label for="axpl_nombre" class="axpl-label">Nombre</label>
              <input id="axpl_nombre" name="nombre" type="text" class="axpl-input" placeholder="Nombre de la playlist" required minlength="3" maxlength="80">
              <small class="axpl-field-msg" id="axplNameMsg"></small>
            </div>

            <div class="axpl-cover-field">
              <label class="axpl-label">Portada</label>
              <label for="axpl_cover" class="axpl-cover-uploader" id="axplCoverDrop" tabindex="0" aria-label="Arrastra y suelta una imagen o presiona para seleccionar">
                <input id="axpl_cover" name="portada" type="file" accept="image/*" hidden>
                <img id="axplCoverPreview" alt="Vista previa de la portada" />
                <div class="axpl-uploader-hint" id="axplUploaderHint">
                  <div class="axpl-uploader-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
                  <div class="axpl-uploader-text">
                    <strong>Arrastra y suelta</strong> la imagen<br>
                    <span class="axpl-muted">o haz clic para seleccionar</span>
                  </div>
                  <div class="axpl-uploader-meta">
                    <span class="axpl-badge">PNG/JPG</span>
                    <span class="axpl-badge">Máx 5MB</span>
                  </div>
                </div>
                <div class="axpl-uploader-overlay" id="axplUploaderOverlay">Suelta la imagen aquí</div>
              </label>
              <small class="axpl-hint">PNG/JPG hasta 5MB</small>
              <small class="axpl-field-msg" id="axplCoverMsg"></small>
            </div>

            <div class="axpl-field axpl-field-span2 axpl-field-desc" aria-live="polite">
              <label for="axpl_desc" class="axpl-label">Descripción</label>
              <textarea id="axpl_desc" name="descripcion" class="axpl-textarea" rows="8" placeholder="(Opcional)" maxlength="300"></textarea>
              <small class="axpl-field-msg" id="axplDescMsg"></small>
            </div>
          </div>

          <div class="axpl-actions">
            <button type="button" class="axpl-btn axpl-btn-secondary" id="axplCancel">Cancelar</button>
            <button type="submit" class="axpl-btn axpl-btn-primary" id="axplSubmit">
              <i class="fa-regular fa-floppy-disk"></i> Guardar
            </button>
          </div>
        </form>
      </div>
    </main>

    @include('components.footer')
  </div>
</div>

<!-- Guard-rails de layout: calcula márgenes seguros según sidebar/header/footer/player -->
<script>
(() => {
  const root = document.querySelector('#axplRoot.axpl');

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

<!-- JS de eliminación (individual y múltiple) -->
<script>
(() => {
  const $  = (s,c=document)=>c.querySelector(s);
  const $$ = (s,c=document)=>Array.from(c.querySelectorAll(s));
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const root = $('#axplRoot');
  const grid = $('#axplGrid');
  const toast = $('#axplToast');
  const selectBtn = $('#axplSelectMode');
  const delSelectedBtn = $('#axplDeleteSelected');
  const selIndicator = $('#axplSelectIndicator');
  const selCountEl = $('#axplSelCount');

  function showToast(msg, type='ok'){
    if(!toast) return;
    toast.className = 'axpl-toast axpl-toast-' + (type==='err'?'err':type==='info'?'info':'ok');
    toast.innerHTML = (type==='err' ? '<i class="fa-solid fa-circle-xmark"></i> ' :
                       type==='info'? '<i class="fa-solid fa-circle-info"></i> ' :
                                      '<i class="fa-solid fa-circle-check"></i> ') + `<span>${msg}</span>`;
    toast.hidden = false;
    requestAnimationFrame(()=> toast.classList.add('is-shown'));
    setTimeout(()=> toast.classList.remove('is-shown'), 2600);
    setTimeout(()=> toast.hidden = true, 3000);
  }

  // === Eliminar UNA playlist (botón papelera en cada tile)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.axpl-trash');
    if(!btn) return;
    e.preventDefault();

    const name = btn.dataset.name || 'esta playlist';
    const url  = btn.dataset.deleteUrl;
    const tile = btn.closest('.axpl-tile');
    if(!url || !tile) return;

    if(!confirm(`¿Eliminar “${name}”?`)) return;

    try{
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': CSRF,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams({ _method: 'DELETE' })
      });
      if(!res.ok) throw new Error('HTTP '+res.status);
      tile.style.opacity = .55; tile.style.pointerEvents = 'none';
      setTimeout(()=> tile.remove(), 150);
      showToast('Playlist eliminada', 'ok');
      updateSelectionUI();
    }catch(err){
      console.error(err);
      showToast('No se pudo eliminar la playlist', 'err');
    }
  });

  // === Modo selección (para eliminar varias)
  function toggleSelectMode(on){
    const willOn = (typeof on === 'boolean') ? on : !root.classList.contains('axpl-select-mode');
    root.classList.toggle('axpl-select-mode', willOn);
    selectBtn?.setAttribute('aria-pressed', willOn ? 'true' : 'false');
    if(!willOn){
      $$('.axpl-tile.is-selected').forEach(t => t.classList.remove('is-selected'));
    }
    updateSelectionUI();
  }
  selectBtn?.addEventListener('click', ()=> toggleSelectMode());

  // Click en tile para seleccionar/deseleccionar
  grid?.addEventListener('click', (e)=>{
    if(!root.classList.contains('axpl-select-mode')) return;
    const tile = e.target.closest('.axpl-tile');
    if(!tile || tile.id === 'axplOpenModal2') return;
    // Evitar que botones internos cambien la selección
    if(e.target.closest('.axpl-pencil, .axpl-trash, .axpl-play-btn, .axpl-tile-link')) return;
    tile.classList.toggle('is-selected');
    updateSelectionUI();
  });

  function updateSelectionUI(){
    const n = $$('.axpl-tile.is-selected').length;
    if(selIndicator){
      selIndicator.hidden = n === 0;
      if(selCountEl) selCountEl.textContent = n;
    }
    if(delSelectedBtn) delSelectedBtn.disabled = n === 0;
  }

  // Eliminar SELECCIONADAS (una por una con DELETE)
  delSelectedBtn?.addEventListener('click', async ()=>{
    const tiles = $$('.axpl-tile.is-selected');
    if(!tiles.length) return;

    if(!confirm(`¿Eliminar ${tiles.length} playlist(s)?`)) return;

    delSelectedBtn.disabled = true;
    let ok = 0, fail = 0;

    for(const tile of tiles){
      const url = tile.getAttribute('data-del');
      if(!url) { fail++; continue; }
      try{
        const res = await fetch(url, {
          method:'POST',
          headers:{
            'X-CSRF-TOKEN':CSRF,
            'X-Requested-With':'XMLHttpRequest',
            'Accept':'application/json',
            'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body: new URLSearchParams({ _method:'DELETE' })
        });
        if(!res.ok) throw new Error('HTTP '+res.status);
        ok++;
        tile.style.opacity = .55; tile.style.pointerEvents = 'none';
        setTimeout(()=> tile.remove(), 120);
      }catch(err){
        console.error(err);
        fail++;
      }
    }

    toggleSelectMode(false);
    if(fail===0) showToast(`Eliminadas ${ok} playlist(s)`, 'ok');
    else if(ok>0) showToast(`Eliminadas ${ok}. Fallaron ${fail}.`, 'info');
    else showToast('No se pudieron eliminar las playlists', 'err');
  });

})();
</script>
</body>
</html>
|