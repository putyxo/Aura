@vite(['resources/css/header.css', 'resources/js/header.js'])

<header class="ah-header"
        data-fallback-avatar="{{ asset('img/perfil_npc.png') }}"
        data-fallback-song="{{ asset('img/default-cancion.png') }}"
        data-fallback-album="{{ asset('img/default-album.png') }}">
  <div class="ah-inner">
    <!-- === Navegación (izquierda del buscador) === -->
    <div class="ah-nav">
      
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


    

      <!-- Usuario -->
      <div class="ah-user">
        @auth
          @php
            $user = auth()->user();
            $avatarUrl = img_url($user->avatar ?? null, 'img/perfil_npc.png');
            $ver = $user->updated_at?->getTimestamp() ?? time();
            $avatarChip = $avatarUrl . (str_contains($avatarUrl, '?') ? '&' : '?') . 'v=' . $ver;
          @endphp
          <button class="ah-user-chip" id="ahUserBtn" type="button" aria-expanded="false" aria-controls="ahUserDropdown">
            <img class="ah-chip-avatar"
                 src="{{ $avatarChip }}"
                 alt="{{ $user->nombre_artistico ?? $user->nombre ?? 'Usuario' }}"
                 onerror="this.onerror=null;this.src='{{ asset('img/perfil_npc.png') }}';">
            <span class="ah-chip-name">
              {{ $user->es_artista ? ($user->nombre_artistico ?? 'Artista') : ($user->nombre ?? 'Usuario') }}
            </span>
            <i class="fa-solid fa-chevron-down"></i>
          </button>

          <div class="ah-dropdown" id="ahUserDropdown" aria-hidden="true" role="menu">
            <div class="ah-profile-header">
              <div class="ah-profile-info">
                @php
                  $avatarDrop = img_url($user->avatar ?? null, 'img/perfil_npc.png');
                  $avatarDrop .= (str_contains($avatarDrop, '?') ? '&' : '?') . 'v=' . $ver;
                @endphp
                <img class="ah-profile-avatar"
                     src="{{ $avatarDrop }}"
                     alt="{{ $user->nombre_artistico ?? $user->nombre ?? 'Usuario' }}"
                     onerror="this.onerror=null;this.src='{{ asset('img/perfil_npc.png') }}';">
                <div class="ah-profile-text">
                  <div class="ah-profile-name">
                    {{ $user->es_artista ? ($user->nombre_artistico ?? 'Artista') : ($user->nombre ?? 'Usuario') }}
                  </div>
                  @if($user->email)
                    <div class="ah-profile-email">{{ $user->email }}</div>
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

              @if($user->es_artista)
                <a href="{{ route('musica.subir') }}" class="ah-menu-item">
                  <i class="fas fa-upload"></i><span>Subir música</span>
                </a>
              @endif

              <!-- Idioma (switch ES/EN) -->
             
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
        @endauth

        @guest
          <button class="ah-user-chip" type="button" disabled>
            <img class="ah-chip-avatar"
                 src="{{ asset('img/perfil_npc.png') }}"
                 alt="Invitado">
            <span class="ah-chip-name">Invitado</span>
          </button>
          <div class="ah-guest-actions">
            <a class="ah-btn" href="{{ route('login') }}" title="Iniciar sesión" aria-label="Iniciar sesión">
              <i class="fa-solid fa-right-to-bracket"></i>
            </a>
            <a class="ah-btn" href="{{ route('register') }}" title="Crear cuenta" aria-label="Crear cuenta">
              <i class="fa-regular fa-id-card"></i>
            </a>
          </div>
        @endguest
      </div>
    </div>
  </div>

  <!-- ====== Ecualizador invisible (cargado en todas las páginas) ====== -->
  <div id="global-eq" style="display:none">
    @foreach([60,170,310,600,1000,3000,6000,12000,14000,16000] as $freq)
      <input
        type="range"
        min="-12" max="12" step="0.5"
        value="{{ optional(optional(auth()->user())->equalizer)->{'band_'.$freq} ?? 0 }}"
        data-freq="{{ $freq }}"
        class="eq-slider-global"
      >
    @endforeach

    <input id="global-preamp" type="range" min="-18" max="18" step="0.5"
           value="{{ optional(optional(auth()->user())->equalizer)->preamp ?? 0 }}">
  </div>
</header>

<script>
(() => {
  const FREQS = [60,170,310,600,1000,3000,6000,12000,14000,16000];
  const dbToGain = db => Math.pow(10, db/20);

  const sliders = document.querySelectorAll('.eq-slider-global');
  const preamp = document.getElementById('global-preamp');

  let ac, filters = [], gPreamp, srcNode;

  function ensureCtx(){
    if (ac) return;
    ac = new (window.AudioContext || window.webkitAudioContext)();

    filters = FREQS.map(freq => {
      const f = ac.createBiquadFilter();
      f.type = 'peaking';
      f.frequency.value = freq;
      f.Q.value = 1.0;
      f.gain.value = 0;
      return f;
    });

    gPreamp = ac.createGain();
    gPreamp.gain.value = dbToGain(parseFloat(preamp?.value || '0'));

    for (let i = 0; i < filters.length - 1; i++) filters[i].connect(filters[i + 1]);
    filters[filters.length - 1].connect(gPreamp);
    gPreamp.connect(ac.destination);

    // Conectar al player global (si existe)
    const audio = document.querySelector('#player audio, audio#player');
    if (audio) {
      try {
        srcNode = ac.createMediaElementSource(audio);
        srcNode.connect(filters[0]);
      } catch (e) {
        console.warn("EQ ya conectado");
      }
    }
  }

  function applyInitialValues(){
    ensureCtx();
    sliders.forEach((sl, idx) => {
      const db = parseFloat(sl.value);
      filters[idx].gain.value = db;
    });
    const dbPreamp = parseFloat(preamp?.value || '0');
    gPreamp.gain.value = dbToGain(dbPreamp);
  }

  document.addEventListener('DOMContentLoaded', applyInitialValues);

  // API pública global para reconectar cuando cambies canción
  window.bindEqualizerTo = function(audioEl){
    if (!audioEl) return;
    ensureCtx();
    try {
      const media = ac.createMediaElementSource(audioEl);
      media.connect(filters[0]);
      srcNode = media;
    } catch (e) {
      console.warn("EQ: la fuente ya estaba conectada o no es válida");
    }
  };
})();
</script>
