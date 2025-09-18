@vite(['resources/css/header.css', 'resources/js/header.js'])

<header class="ah-header"
        data-fallback-avatar="{{ asset('img/perfil_npc.png') }}"
        data-fallback-song="{{ asset('img/default-cancion.png') }}"
        data-fallback-album="{{ asset('img/default-album.png') }}">
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
    <div class="ah-search-group"
         id="ahSearchGroup"
         data-search-url="{{ route('search.json') }}">
      <span class="ah-search-icon" aria-hidden="true"><i class="fas fa-search"></i></span>
      <input id="ahSearchInput"
             class="ah-search-input"
             type="text"
             placeholder="Buscar canciones, artistas..."
             aria-label="Buscar"
             aria-controls="ahSearchResults"
             autocomplete="off" />
      <div id="ahSearchResults" class="ah-search-results" role="listbox" aria-expanded="false"></div>
    </div>

    <!-- === Acciones + Usuario (derecha) === -->
    <div class="ah-right">
      <!-- Notificaciones -->
      <div class="ah-pop ah-notif">
        <button class="ah-btn" id="ahNotifBtn" type="button" aria-expanded="false" aria-controls="ahNotifPanel" title="Notificaciones">
          <i class="fa-regular fa-bell"></i>
        </button>
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
  @php
      $user = auth()->user();
      if ($user) {
          // fallback a perfil_npc.png si no tiene avatar
          $avatarUrl = img_url($user->avatar ?? 'img/perfil_npc.png', 'img/perfil_npc.png');
          $ver = $user->updated_at?->getTimestamp() ?? time();
          $avatarChip = $avatarUrl . (str_contains($avatarUrl, '?') ? '&' : '?') . 'v=' . $ver;
      } else {
          $avatarChip = asset('img/perfil_npc.png');
      }
  @endphp

  <img class="ah-chip-avatar"
       src="{{ $avatarChip }}"
       alt="{{ $user->nombre_artistico ?? $user->nombre ?? 'Invitado' }}"
       onerror="this.onerror=null;this.src='{{ asset('img/perfil_npc.png') }}';">

  <span class="ah-chip-name">
      {{ $user?->es_artista ? ($user->nombre_artistico ?? $user->nombre) : ($user->nombre ?? 'Invitado') }}
  </span>
  <i class="fa-solid fa-chevron-down"></i>
</button>


        <div class="ah-dropdown" id="ahUserDropdown" aria-hidden="true" role="menu">
          <div class="ah-profile-header">
  <div class="ah-profile-info">
    @php
      $user = auth()->user();
      if ($user) {
          $avatarDrop = img_url($user->avatar ?? 'img/perfil_npc.png', 'img/perfil_npc.png');
          $ver = $user->updated_at?->getTimestamp() ?? time();
          $avatarDrop .= (str_contains($avatarDrop, '?') ? '&' : '?') . 'v=' . $ver;
      } else {
          $avatarDrop = asset('img/perfil_npc.png');
      }
    @endphp

    <img class="ah-profile-avatar"
         src="{{ $avatarDrop }}"
         alt="{{ $user->nombre_artistico ?? $user->nombre ?? 'Invitado' }}"
         onerror="this.onerror=null;this.src='{{ asset('img/perfil_npc.png') }}';">

    <div class="ah-profile-text">
      <div class="ah-profile-name">
        {{ $user?->es_artista ? ($user->nombre_artistico ?? $user->nombre) : ($user->nombre ?? 'Invitado') }}
      </div>

      @if($user?->email)
        <div class="ah-profile-email">{{ $user->email }}</div>
      @endif
    </div>
  </div>
</div>


          <div class="ah-menu-options">
            @auth
  <a href="{{ route('perfil.show', auth()->id()) }}" class="ah-menu-item">
    <i class="fas fa-user"></i><span>Ver mi perfil</span>
  </a>
@endauth

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
 @php
  $eq = optional(auth()->user()?->equalizer);
@endphp

@foreach([60,170,310,600,1000,3000,6000,12000,14000,16000] as $freq)
  <input 
    type="range" 
    min="-12" max="12" step="0.5"
    value="{{ $eq->{'band_'.$freq} ?? 0 }}"
    data-freq="{{ $freq }}"
    class="eq-slider-global"
  >
@endforeach

<input id="global-preamp" type="range" min="-18" max="18" step="0.5"
       value="{{ $eq->preamp ?? 0 }}">

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
