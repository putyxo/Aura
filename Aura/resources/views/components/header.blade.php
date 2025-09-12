@vite('resources/css/header.css')

<header class="ah-header">
  <div class="ah-inner">
    <!-- === Navegación (izquierda del buscador) === -->
    <div class="ah-nav">
      <button class="ah-nav-btn" id="ahBackBtn" title="Atrás" aria-label="Atrás">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <button class="ah-nav-btn" id="ahForwardBtn" title="Adelante" aria-label="Adelante">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>

    <!-- === Buscador (centro) === -->
    <div class="ah-search-group">
      <span class="ah-search-icon" aria-hidden="true"><i class="fas fa-search"></i></span>
      <input class="ah-search-input" type="text" placeholder="Buscar canciones, artistas..." aria-label="Buscar" autocomplete="off" />
      <div id="ahSearchResults" class="ah-search-results" role="listbox" aria-expanded="false"></div>
    </div>

    <!-- === Acciones + Usuario (derecha) === -->
    <div class="ah-right">
      <!-- Notificaciones -->
      <div class="ah-pop ah-notif">
       
        <div class="ah-popover" id="ahNotifPanel" role="dialog" aria-hidden="true">
          <div class="ah-popover-head"><span>Notificaciones</span></div>
          <div class="ah-popover-body">
            <div class="ah-empty">
              <i class="fa-regular fa-bell-slash"></i>
              <p>Aún no tienes notificaciones</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Configuración -->
      <button class="ah-btn" type="button" title="Ajustes" aria-label="Ajustes">
        <i class="fa-solid fa-gear"></i>
      </button>

      <!-- Usuario -->
      <div class="ah-user">
        <button class="ah-user-chip" id="ahUserBtn" type="button" aria-expanded="false" aria-controls="ahUserDropdown">
          <img class="ah-chip-avatar"
               src="@if(auth()->user()->avatar)
                       {{ drive_img_url(auth()->user()->avatar, 100) }}&v={{ time() }}
                     @else
                       {{ asset('img/default-user.png') }}
                     @endif"
               alt="{{ auth()->user()->nombre_artistico ?? auth()->user()->nombre }}">
          <span class="ah-chip-name">
            {{ auth()->user()->es_artista ? auth()->user()->nombre_artistico : auth()->user()->nombre }}
          </span>
          <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div class="ah-dropdown" id="ahUserDropdown" aria-hidden="true" role="menu">
          <div class="ah-profile-header">
            <div class="ah-profile-info">
              <img class="ah-profile-avatar"
                   src="@if(auth()->user()->avatar)
                           {{ drive_img_url(auth()->user()->avatar, 300) }}&v={{ time() }}
                         @else
                           {{ asset('img/default-user.png') }}
                         @endif"
                   alt="{{ auth()->user()->nombre_artistico ?? auth()->user()->nombre }}">
              <div class="ah-profile-text">
                <div class="ah-profile-name">
                  {{ auth()->user()->es_artista ? auth()->user()->nombre_artistico : auth()->user()->nombre }}
                </div>
                @if(auth()->user()->email)
                  <div class="ah-profile-email">{{ auth()->user()->email }}</div>
                @endif
              </div>
            </div>
          </div>

          <div class="ah-menu-options">
            <a href="{{ route('perfil.show', auth()->id()) }}" class="ah-menu-item">
              <i class="fas fa-user"></i><span>Ver mi perfil</span>
            </a>
            <a href="{{ route('cuenta', auth()->id()) }}" class="ah-menu-item">
              <i class="fas fa-cog"></i><span>Mi cuenta</span>
            </a>
             <a href="{{ route('preferencias', auth()->id()) }}" class="ah-menu-item">
               <i class="fas fa-sliders-h"></i><span>Preferencias</span>
            </a>

            @auth
              @if(auth()->user()->es_artista)
                <a href="{{ route('musica.subir') }}" class="ah-menu-item">
                  <i class="fas fa-upload"></i><span>Subir música</span>
                </a>
              @endif
            @endauth

            <!-- Idioma (switch ES/EN) -->
            <div class="ah-menu-item ah-lang-item">
              <i class="fas fa-globe"></i><span>Idioma</span>
              <button class="ah-toggle" id="ahLangSwitch" role="switch" aria-checked="false" data-lang="es" title="Cambiar idioma">
                <span class="ah-toggle-track">
                  <span class="ah-toggle-label ah-l-es">ES</span>
                  <span class="ah-toggle-label ah-l-en">EN</span>
                  <span class="ah-toggle-knob"></span>
                </span>
              </button>
            </div>
          </div>

          <div class="ah-menu-sep"></div>

          <div class="ah-menu-logout">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="ah-menu-item ah-logout-btn">
                <i class="fas fa-sign-out-alt"></i><span>Cerrar sesión</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- ====== Ecualizador invisible (cargado en todas las páginas) ====== -->
<div id="global-eq" style="display:none">
  @foreach([60,170,310,600,1000,3000,6000,12000,14000,16000] as $freq)
    <input 
      type="range" 
      min="-12" max="12" step="0.5"
      value="{{ optional(auth()->user()->equalizer)->{'band_'.$freq} ?? 0 }}"
      data-freq="{{ $freq }}"
      class="eq-slider-global"
    >
  @endforeach

  <input id="global-preamp" type="range" min="-18" max="18" step="0.5"
         value="{{ optional(auth()->user()->equalizer)->preamp ?? 0 }}">
</div>

<script>
(() => {
  const FREQS = [60,170,310,600,1000,3000,6000,12000,14000,16000];
  const dbToGain = db => Math.pow(10, db/20);

  const sliders = document.querySelectorAll('.eq-slider-global');
  const preamp = document.getElementById('global-preamp');

  let ac, filters=[], gPreamp, srcNode;

  function ensureCtx(){
    if (ac) return;
    ac = new (window.AudioContext||window.webkitAudioContext)();

    filters = FREQS.map(freq=>{
      const f = ac.createBiquadFilter();
      f.type = 'peaking';
      f.frequency.value = freq;
      f.Q.value = 1.0;
      f.gain.value = 0;
      return f;
    });

    gPreamp = ac.createGain();
    gPreamp.gain.value = dbToGain(parseFloat(preamp.value || '0'));

    for (let i=0;i<filters.length-1;i++) filters[i].connect(filters[i+1]);
    filters[filters.length-1].connect(gPreamp);
    gPreamp.connect(ac.destination);

    // Conectar al player global (si existe)
    const audio = document.querySelector('#player audio, audio#player');
    if (audio) {
      try {
        srcNode = ac.createMediaElementSource(audio);
        srcNode.connect(filters[0]);
      } catch(e) {
        console.warn("EQ ya conectado");
      }
    }
  }

  // Aplicar valores iniciales guardados
  function applyInitialValues(){
    ensureCtx();
    sliders.forEach((sl, idx)=>{
      const db = parseFloat(sl.value);
      filters[idx].gain.value = db;
    });
    const dbPreamp = parseFloat(preamp.value);
    gPreamp.gain.value = dbToGain(dbPreamp);
  }

  document.addEventListener('DOMContentLoaded', applyInitialValues);

  // API pública global para reconectar cuando cambies canción
  window.bindEqualizerTo=function(audioEl){
    if (!audioEl) return;
    ensureCtx();
    const media = ac.createMediaElementSource(audioEl);
    media.connect(filters[0]);
    srcNode = media;
  };
})();
</script>


</header>

<style>
/* ================== AURA HEADER (prefijo ah-) ================== */
.ah-header{
  /* offsets – ajústalos a tu layout */
  --ah-left-offset: 76px;     /* sidebar mini */
  --ah-right-offset: 300px;   /* player derecho (más equilibrado) */
  --ah-safe-gap: 12px;

  /* tamaños + tipografías (AGRANDADO) */
  --ah-font: 15.5px;
  --ah-font-sm: 14.5px;
  --ah-font-lg: 18px;

  /* paleta */
  --ah-bg-1:#000000; --ah-bg-2:#090212;
  --ah-panel:#0f0f17; --ah-panel2:#141428;
  --ah-text:#f6f7fb; --ah-dim:#b6b6c8;
  --ah-line:#26263a; --ah-line-soft: rgba(168,85,247,.14);
  --ah-a1:#a855f7; --ah-a2:#ec4899;

  position: sticky; top: 0; z-index: 40;
  background:
    radial-gradient(900px 140px at 10% 0%, color-mix(in oklab, var(--ah-a2) 8%, transparent) 0%, transparent 55%),
    radial-gradient(900px 140px at 90% 0%, color-mix(in oklab, var(--ah-a1) 10%, transparent) 0%, transparent 55%),
    linear-gradient(90deg, var(--ah-bg-2), var(--ah-bg-1));
  border-bottom: 1px solid var(--ah-line-soft);
  box-shadow: 0 6px 30px rgba(0,0,0,.35);
  padding: 12px 0; /* vertical; los laterales van en .ah-inner */
  color: var(--ah-text);
  font-size: var(--ah-font);
}

/* Contenedor centrado */
.ah-inner{
  max-width: 1320px;              /* <<< centrado y más ancho */
  margin: 0 auto;
  display: grid;
  grid-template-columns: auto 1fr auto; /* nav | buscador | acciones+usuario */
  align-items: center;
  column-gap: 20px;
  padding-left: calc(14px + var(--ah-left-offset));
  padding-right: calc(14px + var(--ah-right-offset));
}

/* ===== Navegación ===== */
.ah-nav{ display:flex; gap:12px; align-items:center }
.ah-nav-btn{
  width:48px; height:48px; display:grid; place-items:center;   /* ↑ más grande */
  border-radius:12px; background: color-mix(in oklab, var(--ah-panel2) 88%, #000 12%);
  border:1px solid var(--ah-line-soft); color:#d7d7df; cursor:pointer;
  transition: transform .22s, border .22s;
}
.ah-nav-btn i{ font-size:18px }
.ah-nav-btn:hover{ transform: translateY(-1px); border-color: color-mix(in oklab, var(--ah-a1) 50%, var(--ah-line-soft)) }

/* ===== Buscador (centro) ===== */
.ah-search-group{
  position:relative; width:100%;
  max-width: 820px;               /* <<< más ancho */
  justify-self:center;
}
.ah-search-icon{
  position:absolute; left:18px; top:50%; transform:translateY(-50%);
  color: var(--ah-a1); font-size: 18px; z-index:2;
  text-shadow: 0 0 10px color-mix(in oklab, var(--ah-a1) 70%, transparent);
}
.ah-search-input{
  width:100%;
  padding: 16px 16px 16px 50px;   /* ↑ más alto */
  color:#e9e9f5; font-size: 16px;
  background: color-mix(in oklab, var(--ah-panel) 93%, #fff 7%);
  border: 1.8px solid rgba(168,85,247,.30);
  border-radius: 18px;
  background-clip: padding-box; outline: none;
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.03), 0 10px 24px rgba(0,0,0,.28);
  transition: border .22s, box-shadow .22s, transform .22s;
}
.ah-search-input::placeholder{ color: color-mix(in oklab, var(--ah-dim) 72%, #fff 0%) }
.ah-search-input:focus{
  border-color: var(--ah-a1);
  box-shadow: 0 0 0 3px color-mix(in oklab, var(--ah-a1) 18%, transparent), 0 18px 38px rgba(0,0,0,.35);
  transform: translateY(-1px);
}

/* Resultados */
.ah-search-results{
  position:absolute; top: calc(100% + 12px); left:0; right:0;
  background: linear-gradient(135deg, var(--ah-panel2) 0%, var(--ah-panel) 100%);
  border:1px solid var(--ah-line-soft);
  border-radius: 16px; max-height: 460px; overflow:auto;
  display:none; z-index:10000; box-shadow: 0 24px 60px rgba(0,0,0,.40);
  backdrop-filter: blur(10px);
}
.ah-skel{ padding: 14px 16px; color: var(--ah-dim); font-size: 15px }
.ah-sr-item{
  display:flex; align-items:center; gap:14px;
  padding: 14px 16px; text-decoration:none; color: var(--ah-text);
  border-bottom:1px solid rgba(255,255,255,.05);
  transition: transform .22s, background .22s;
  font-size: 15px;
}
.ah-sr-item:last-child{ border-bottom:none }
.ah-sr-item:hover, .ah-sr-item.is-active{ transform: translateX(8px); background: color-mix(in oklab, var(--ah-a1) 12%, transparent) }
.ah-sr-item img{ width:56px; height:56px; border-radius:12px; object-fit:cover; border:1px solid rgba(255,255,255,.08) }

/* ===== Acciones + Usuario ===== */
.ah-right{ display:flex; align-items:center; gap:12px; justify-self:end }

.ah-btn{
  width:48px; height:48px; display:grid; place-items:center;  /* ↑ más grande */
  border-radius:14px; border:1px solid var(--ah-line-soft);
  color:#fff; background: color-mix(in oklab, var(--ah-panel2) 88%, #000 12%);
  cursor:pointer; transition: transform .22s, border .22s, background .22s;
  font-size: 16px;
}
.ah-btn i{ font-size:18px }
.ah-btn:hover{ border-color: color-mix(in oklab, var(--ah-a1) 55%, var(--ah-line-soft)); transform: translateY(-2px) }

/* Notificaciones (popover) */
.ah-pop{ position:relative }
.ah-popover{
  --ah-pop-shift: 0px;
  position:absolute; top: calc(100% + 12px); left: 50%;
  transform: translate(calc(-50% + var(--ah-pop-shift)), -8px) scale(.96);
  opacity:0; visibility:hidden;
  width: min(380px, calc(100vw - 32px));  /* ↑ más ancho */
  background: linear-gradient(180deg, var(--ah-panel2), var(--ah-panel));
  border:1px solid var(--ah-line-soft); border-radius:16px; box-shadow: 0 24px 60px rgba(0,0,0,.40);
  transition: transform .22s, opacity .22s, visibility .22s;
  z-index: 10001; overflow:hidden;
}
.ah-pop.open .ah-popover{ opacity:1; visibility:visible; transform: translate(calc(-50% + var(--ah-pop-shift)), 0) scale(1) }
.ah-popover-head{ padding:12px 16px; font-weight:800; border-bottom:1px solid rgba(255,255,255,.06) }
.ah-popover-body{ padding:16px }
.ah-empty{ display:grid; place-items:center; gap:8px; color:var(--ah-dim); padding:20px 10px; text-align:center }
.ah-empty i{ font-size:22px }

/* Usuario */
.ah-user{ position:relative }
.ah-user-chip{
  display:flex; align-items:center; gap:12px;
  padding: 10px 14px; min-width: 220px;                 /* ↑ más grande */
  border-radius: 999px; border:1px solid var(--ah-line-soft);
  background: color-mix(in oklab, var(--ah-panel2) 88%, #000 12%);
  color:#fff; cursor:pointer; transition: transform .22s, border .22s;
  box-shadow: 0 10px 28px rgba(0,0,0,.25);
  font-size: 15.5px;
}
.ah-user-chip:hover{ transform: translateY(-1px); border-color: color-mix(in oklab, var(--ah-a1) 50%, var(--ah-line-soft)) }
.ah-chip-avatar{ width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid color-mix(in oklab, var(--ah-a1) 45%, transparent) }
.ah-chip-name{ font-weight:700 }
.ah-user-chip i{ color: var(--ah-a1); transition: transform .22s }
.ah-user.open .ah-user-chip i{ transform: rotate(180deg) }

/* Dropdown usuario (anti-overflow) */
.ah-dropdown{
  --ah-drop-shift: 0px;
  position:absolute; top: calc(100% + 12px); right:0;
  max-width: min(380px, calc(100vw - var(--ah-right-offset) - var(--ah-safe-gap))); /* ↑ */
  background: linear-gradient(180deg, var(--ah-panel2), var(--ah-panel));
  border:1px solid var(--ah-line-soft); border-radius: 16px;
  opacity: 0; visibility: hidden; transform: translate(var(--ah-drop-shift), -10px) scale(.96);
  transition: transform .22s, opacity .22s, visibility .22s;
  z-index: 10001; overflow:hidden; box-shadow: 0 24px 60px rgba(0,0,0,.40);
}
.ah-user.open .ah-dropdown{ opacity:1; visibility:visible; transform: translate(var(--ah-drop-shift), 0) scale(1) }

.ah-profile-header{ padding:16px; color:#fff; background: linear-gradient(135deg, color-mix(in oklab, var(--ah-a1) 70%, #000 0%), color-mix(in oklab, var(--ah-a2) 55%, #000 0%)) }
.ah-profile-info{ display:flex; align-items:center; gap:14px }
.ah-profile-avatar{ width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid rgba(255,255,255,.35) } /* ↑ */
.ah-profile-name{ font-weight:900; font-size: var(--ah-font-lg) }
.ah-profile-email{ color: color-mix(in oklab, var(--ah-text) 70%, #fff 0%); opacity:.9; font-size: var(--ah-font-sm) }

.ah-menu-options{ padding:6px 0 }
.ah-menu-item{ display:flex; align-items:center; gap:12px; width:100%; padding:13px 16px; color:var(--ah-text); background:none; border:none; cursor:pointer; transition: background .22s, transform .22s; font-size: var(--ah-font) }
.ah-menu-item i{ color: var(--ah-a1); font-size: 18px }
.ah-menu-item:hover{ transform: translateX(8px); background: color-mix(in oklab, var(--ah-a1) 12%, transparent) }
.ah-menu-sep{ height:1px; background: linear-gradient(90deg, transparent, var(--ah-line-soft), transparent); margin: 6px 0 }
.ah-logout-btn{ color:#ff7b7b !important }
.ah-logout-btn i{ color:#ff7b7b !important }

/* Toggle idioma */
.ah-lang-item{ gap:12px }
.ah-toggle{ margin-left:auto; background:none; border:none; padding:0; cursor:pointer }
.ah-toggle-track{
  position:relative; display:inline-flex; align-items:center; justify-content:space-between;
  width:78px; height:30px; border-radius:999px; border:1px solid var(--ah-line-soft);
  background: linear-gradient(180deg, var(--ah-panel2), var(--ah-panel));
  padding:0 9px; color:#fff; font-size:12.5px; letter-spacing:.3px;
}
.ah-toggle-label{ opacity:.75; user-select:none }
.ah-toggle-knob{ position:absolute; top:3px; left:3px; width:24px; height:24px; border-radius:50%; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,.35); transition:left .22s }
.ah-toggle[aria-checked="true"] .ah-toggle-knob{ left: calc(100% - 27px) }

/* ===== Historial de búsqueda ===== */
.ah-his-head{
  display:flex; align-items:center; justify-content:space-between;
  padding:10px 12px; font-weight:800; color:#e7d7ff;
  border-bottom:1px solid rgba(255,255,255,.06);
}
.ah-his-head i{ margin-right:8px; color: var(--ah-a1) }
.ah-his-clear{
  background:none; border:1px solid var(--ah-line-soft); color:#d7d7df;
  padding:6px 10px; border-radius:10px; cursor:pointer; font-size:12.5px;
  transition: transform .2s, border-color .2s;
}
.ah-his-clear:hover{ transform: translateY(-1px); border-color: color-mix(in oklab, var(--ah-a1) 55%, var(--ah-line-soft)) }
.ah-his-list{ max-height:420px; overflow:auto }
.ah-his-item{ display:grid; grid-template-columns: 24px 1fr auto; align-items:center; gap:12px }
.ah-his-item .fa-clock{ opacity:.9 }
.ah-his-text{ white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
.ah-his-del{
  background:#2b2d46; color:#fff; border:none; border-radius:9px;
  padding:6px 8px; cursor:pointer; transition: background .2s;
}
.ah-his-del:hover{ background:#3a3d62 }

/* ===== Responsive ===== */
@media (max-width:1200px){
  .ah-inner{ max-width: 1200px }
}
@media (max-width:992px){
  .ah-inner{
    max-width: 100%;
    grid-template-columns: 1fr auto;
    row-gap: 10px;
    padding-left: 16px; padding-right: 16px;
  }
  .ah-search-group{ grid-column: 1 / -1; order: 2; max-width: none; }
  .ah-right{ order:1; justify-self:end }
  .ah-nav{ display:none } /* tablet: ocultamos flechas */
}
@media (max-width:520px){
  .ah-search-input{ padding: 14px 14px 14px 46px; font-size: 15px }
  .ah-btn{ width:44px; height:44px }
  .ah-dropdown{ max-width: calc(100vw - 20px) }
  .ah-chip-name{ display:none }
}

/* Foco accesible */
.ah-search-input:focus, .ah-btn:focus, .ah-nav-btn:focus,
.ah-user-chip:focus, .ah-menu-item:focus, .ah-toggle:focus{
  outline: 3px solid color-mix(in oklab, var(--ah-a1) 55%, transparent);
  outline-offset: 3px;
}
</style>

<script>
/* ================= AURA HEADER JS (prefijo ah-) ================= */
(() => {
  const qs  = (s, r=document) => r.querySelector(s);

  /* Navegación */
  qs('#ahBackBtn')?.addEventListener('click', ()=> history.back());
  qs('#ahForwardBtn')?.addEventListener('click', ()=> history.forward());

  /* Notificaciones (ajuste de borde seguro) */
  const notifBtn   = document.getElementById('ahNotifBtn');
  const notifPanel = document.getElementById('ahNotifPanel');
  const notifWrap  = notifBtn?.closest('.ah-pop');

  const applyPopoverShift = (panel) => {
    const styles = getComputedStyle(document.querySelector('.ah-header'));
    const leftOffset = parseFloat(styles.getPropertyValue('--ah-left-offset')) || 0;
    const rightOffset= parseFloat(styles.getPropertyValue('--ah-right-offset')) || 0;
    const safeGap    = parseFloat(styles.getPropertyValue('--ah-safe-gap')) || 12;

    const rect = panel.getBoundingClientRect();
    const leftLimit  = safeGap + leftOffset;
    const rightLimit = window.innerWidth - rightOffset - safeGap;
    let shift = 0;
    if (rect.right > rightLimit) shift = -(rect.right - rightLimit);
    if (rect.left + shift < leftLimit) shift = leftLimit - rect.left;
    panel.style.setProperty('--ah-pop-shift', `${shift}px`);
  };

  if (notifBtn && notifPanel && notifWrap) {
    const openN = () => {
      notifWrap.classList.add('open');
      notifBtn.setAttribute('aria-expanded','true');
      notifPanel.setAttribute('aria-hidden','false');
      requestAnimationFrame(()=>applyPopoverShift(notifPanel));
    };
    const closeN= () => {
      notifWrap.classList.remove('open');
      notifBtn.setAttribute('aria-expanded','false');
      notifPanel.setAttribute('aria-hidden','true');
    };
    notifBtn.addEventListener('click', (e)=>{
      e.stopPropagation();
      notifWrap.classList.contains('open') ? closeN() : openN();
    });
    document.addEventListener('click', (e)=>{ if (!notifWrap.contains(e.target)) closeN(); });
    window.addEventListener('resize', ()=>{ if (notifWrap.classList.contains('open')) applyPopoverShift(notifPanel); });
  }

  /* Menú de usuario + anti-overflow + idioma */
  const $root = document.querySelector('.ah-user');
  const $btn  = document.getElementById('ahUserBtn');
  const $dd   = document.getElementById('ahUserDropdown');

  function applyDropdownShift() {
    if (!$dd) return;
    const styles = getComputedStyle(document.querySelector('.ah-header'));
    const rightOffset = parseFloat(styles.getPropertyValue('--ah-right-offset')) || 0;
    const safeGap     = parseFloat(styles.getPropertyValue('--ah-safe-gap')) || 12;

    const rect = $dd.getBoundingClientRect();
    const limit = window.innerWidth - rightOffset - safeGap;
    let shift = 0;
    if (rect.right > limit) shift = -(rect.right - limit);
    if (rect.left + shift < safeGap) shift = safeGap - rect.left;
    $dd.style.setProperty('--ah-drop-shift', `${shift}px`);
  }

  if ($root && $btn && $dd) {
    if (!$btn.dataset.bound) {
      $btn.dataset.bound = 'true';
      const open = () => {
        $root.classList.add('open');
        $btn.setAttribute('aria-expanded','true');
        $dd.setAttribute('aria-hidden','false');
        requestAnimationFrame(applyDropdownShift);
      };
      const close= () => {
        $root.classList.remove('open');
        $btn.setAttribute('aria-expanded','false');
        $dd.setAttribute('aria-hidden','true');
      };
      const toggle = () => $root.classList.contains('open') ? close() : open();

      $btn.addEventListener('click', (e)=>{
        e.preventDefault(); e.stopPropagation(); toggle();
      });
      document.addEventListener('click', (e)=>{ if (!$root.contains(e.target)) close(); });
      document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') close(); });
      window.addEventListener('resize', ()=>{ if ($root.classList.contains('open')) applyDropdownShift(); });
      $dd.addEventListener('click', (e)=> e.stopPropagation());

      const $pref = document.getElementById('ahPrefBtn');
      $pref && $pref.addEventListener('click', ()=>{ console.log('Abrir preferencias'); close(); });

      const langSwitch = document.getElementById('ahLangSwitch');
      const i18n = {
        es:{ search:'Buscar canciones, artistas...' },
        en:{ search:'Search tracks, artists...' }
      };
      const applyLangUI = (lang) => {
        langSwitch.setAttribute('aria-checked', lang==='en' ? 'true' : 'false');
        langSwitch.dataset.lang = lang;
        document.querySelector('.ah-search-input').placeholder = i18n[lang].search;
      };
      try { applyLangUI(localStorage.getItem('ahLang') || 'es'); } catch(_){ applyLangUI('es'); }
      langSwitch?.addEventListener('click', async () => {
        const next = (langSwitch?.dataset.lang === 'es') ? 'en' : 'es';
        applyLangUI(next);
        try { localStorage.setItem('ahLang', next); } catch(_){}
        try {
          const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
          await fetch(`/locale/toggle?lang=${next}`, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token}
          });
        } catch(_){}
        window.dispatchEvent(new CustomEvent('lang:toggle', { detail:{ lang: next } }));
      });

      // fallback avatar
      try{
        const imgs = $root.querySelectorAll('.ah-chip-avatar, .ah-profile-avatar');
        const fallback = "{{ asset('img/default-user.png') }}";
        imgs.forEach(img => { if (!img.getAttribute('src')) img.setAttribute('src', fallback); });
      }catch(_){}
    }
  }
})();
</script>


<!-- ======= OVERRIDES para que nada quede encima o debajo (fijo arriba) ======= -->
<style>
  .ah-header{
    position: fixed !important;
    top: 0; left: 0; right: 0;
    z-index: 99999; /* nada por encima */
  }
  .ah-inner{ height: 100%; }
</style>

<script>
  /* Empuja el body según la altura real del header (nada queda debajo) */
  (() => {
    const hdr = document.querySelector('.ah-header');
    if (!hdr) return;
    const apply = () => {
      const h = Math.ceil(hdr.getBoundingClientRect().height);
      document.body.style.paddingTop = h + 'px';
    };
    apply();
    addEventListener('load', apply, { once:true });
    addEventListener('resize', apply);
    new ResizeObserver(apply).observe(hdr);
  })();
</script>

<script>
/* ================== AURA SEARCH (MÚSICA + ARTISTAS + ÁLBUMES) ================== */
(() => {
  const input = document.querySelector('.ah-search-input');
  const resultsBox = document.getElementById('ahSearchResults');
  if (!input || !resultsBox) return;

  const debounce = (fn, ms = 260) => {
    let t;
    return (...a) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...a), ms);
    };
  };

  // Mostrar / ocultar
  const openResults = () => { resultsBox.style.display = 'block'; resultsBox.setAttribute('aria-expanded','true'); };
  const closeResults = () => { resultsBox.style.display = 'none'; resultsBox.setAttribute('aria-expanded','false'); };

  // Renderizar resultados globales
  const renderItems = (list = []) => {
    if (!list.length) {
      resultsBox.innerHTML = `<div class="ah-skel">No se encontraron resultados</div>`;
      return;
    }
    resultsBox.innerHTML = list.map(item => {
      if (item.tipo === 'cancion') {
        return `
          <div class="ah-sr-item ah-sr-song" role="option" tabindex="-1">
            <img src="${item.avatar}" alt="">
            <div>
              <span class="ah-sr-main">${item.nombre}</span>
              <span class="ah-sr-sub">🎵 Canción — ${item.artist || ''}</span>
            </div>
            <button class="ah-hidden-btn" style="display:none"
              data-id="${item.id}" data-src="${item.audio || ''}"
              data-title="${item.nombre}" data-artist="${item.artist || 'Desconocido'}"
              data-cover="${item.avatar}"></button>
          </div>`;
      }
      const sub = item.tipo === 'usuario' ? '👤 Usuario' : '📀 Álbum';
      return `
        <a href="${item.url}" class="ah-sr-item" role="option" tabindex="-1">
          <img src="${item.avatar}" alt="">
          <div>
            <span class="ah-sr-main">${item.nombre}</span>
            <span class="ah-sr-sub">${sub}</span>
          </div>
        </a>`;
    }).join('');
    openResults();

    // Click en canciones → reproducir en el reproductor
    resultsBox.querySelectorAll('.ah-sr-song').forEach(el => {
      if (el.dataset.bound) return;
      el.dataset.bound = 'true';
      el.addEventListener('click', () => {
        const btn = el.querySelector('.ah-hidden-btn');
        if (btn) {
          const audio = document.querySelector('#player audio'); // tu <audio> en el footer
          if (audio) {
            audio.src = btn.dataset.src;
            audio.play();
            if (window.bindEqualizerTo) window.bindEqualizerTo(audio);

            // Opcional: actualizar UI del player
            const cover = document.querySelector('#rightPlayer .cover');
            const title = document.querySelector('#rightPlayer .song-name');
            const artist = document.querySelector('#rightPlayer .song-autor');
            if (cover) cover.src = btn.dataset.cover;
            if (title) title.textContent = btn.dataset.title;
            if (artist) artist.textContent = btn.dataset.artist;
          }
        }
        closeResults();
      });
    });
  };

  // Buscar en backend
  const search = debounce(async () => {
    const q = (input.value || '').trim();
    if (q.length < 2) {
      resultsBox.innerHTML = `<div class="ah-skel">Escribe al menos 2 letras…</div>`;
      openResults();
      return;
    }
    resultsBox.innerHTML = `<div class="ah-skel">Buscando…</div>`;
    openResults();
    try {
      const res = await fetch(`/buscar?q=${encodeURIComponent(q)}`);
      const data = await res.json();
      if (!Array.isArray(data) || !data.length) {
        resultsBox.innerHTML = `<div class="ah-skel">No se encontraron resultados</div>`;
      } else {
        renderItems(data);
      }
    } catch {
      resultsBox.innerHTML = `<div class="ah-skel">Error al buscar</div>`;
    }
  });

  // Eventos
  input.addEventListener('input', search);
  document.addEventListener('click', e => {
    if (!resultsBox.contains(e.target) && e.target !== input) closeResults();
  });

  // Navegación con teclado
  let idx = -1;
  const move = d => {
    const items = [...resultsBox.querySelectorAll('.ah-sr-item')];
    if (!items.length) return;
    idx = (idx + d + items.length) % items.length;
    items.forEach(i => i.classList.remove('is-active'));
    items[idx].classList.add('is-active');
    items[idx].scrollIntoView({ block: 'nearest' });
  };
  input.addEventListener('keydown', e => {
    const items = [...resultsBox.querySelectorAll('.ah-sr-item')];
    if (!items.length) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
    if (e.key === 'ArrowUp')   { e.preventDefault(); move(-1); }
    if (e.key === 'Enter' && idx >= 0) {
      e.preventDefault();
      const t = items[idx];
      if (t.classList.contains('ah-sr-song')) {
        const b = t.querySelector('.ah-hidden-btn'); b && b.click();
      } else if (t.tagName === 'A') {
        window.location.href = t.getAttribute('href');
      }
      closeResults();
    }
    if (e.key === 'Escape') closeResults();
  });
})();
</script>
