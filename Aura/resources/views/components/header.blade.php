@vite('resources/css/header.css')

<header class="header">
  <!-- Buscador -->
  <div class="header-search-group">
    <span class="search-icon"></span>
    <input class="search" type="text" placeholder="Buscar..." />
@
    <!-- 🔎 Caja de resultados -->
    <div id="searchResults" class="search-results"></div>
  </div>

  <!-- Acciones rápidas -->
  <div class="quick-actions" role="toolbar" aria-label="Acciones rápidas">
    @auth
      @if(auth()->user()->es_artista)
        <a class="qa-btn" href="{{ route('musica.subir') }}" title="Subir música">
          <i class="fas fa-upload"></i>
        </a>
      @endif
    @endauth
    <button class="qa-btn" type="button" title="Traductor"><i class="fa-solid fa-language"></i></button>
    <button class="qa-btn" type="button" title="Ajustes"><i class="fa-solid fa-gear"></i></button>
    <button class="qa-btn" type="button" title="Notificaciones"><i class="fa-regular fa-bell"></i></button>
  </div>

  <!-- Usuario -->
  <div class="user-menu">
    <button class="user-chip" id="userMenuBtn" type="button" aria-expanded="false">
      <img class="chip-avatar"
           src="@if(auth()->user()->avatar)
                   {{ drive_img_url(auth()->user()->avatar, 100) }}&v={{ time() }}
                 @else
                   {{ asset('img/default-user.png') }}
                 @endif"
           alt="{{ auth()->user()->nombre_artistico ?? auth()->user()->nombre }}">
      <span class="chip-name">
        {{ auth()->user()->es_artista ? auth()->user()->nombre_artistico : auth()->user()->nombre }}
      </span>
      <i class="fa-solid fa-chevron-down"></i>
    </button>

    <div class="dropdown-menu" id="userDropdown" aria-hidden="true">
      <div class="profile-grid">
        <div class="avatar-wrap">
          <img class="profile-avatar"
               src="@if(auth()->user()->avatar)
                       {{ drive_img_url(auth()->user()->avatar, 300) }}&v={{ time() }}
                     @else
                       {{ asset('img/default-user.png') }}
                     @endif"
               alt="{{ auth()->user()->nombre_artistico ?? auth()->user()->nombre }}">
        </div>
        <div class="id-block">
          <div class="profile-name">
            {{ auth()->user()->es_artista ? auth()->user()->nombre_artistico : auth()->user()->nombre }}
          </div>
          @if(auth()->user()->email)
            <div class="profile-email">{{ auth()->user()->email }}</div>
          @endif
        </div>
      </div>

      <!-- Configuración -->
      <div class="center-config">
        <a href="{{ route('perfil.show', auth()->id()) }}" class="center-btn">
          <i class="fa-solid fa-gear"></i><span>Configuración</span>
        </a>
      </div>

      <!-- Salir -->
      <ul class="dropdown-list list-bottom">
        <li>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-wide">
              <i class="fa-solid fa-right-from-bracket"></i><span>Salir</span>
            </button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</header>

<script>
/* === Buscador === */
const searchInput = document.querySelector('.search');
const resultsBox  = document.getElementById('searchResults');

function bindSearchSongs() {
  document.querySelectorAll('.song-result').forEach(el => {
    if (el.dataset.bound) return;
    el.dataset.bound = "true";
    el.addEventListener('click', () => {
      const hiddenBtn = el.querySelector('.cancion-item');
      if (hiddenBtn) hiddenBtn.click(); // dispara el reproductor normal
    });
  });
}

if (searchInput && resultsBox) {
  searchInput.addEventListener('input', async () => {
    const q = searchInput.value.trim();
    if (q.length < 2) {
      resultsBox.innerHTML = "<div class='search-item'>Escribe al menos 2 letras...</div>";
      resultsBox.style.display = "block";
      return;
    }

    try {
      const res  = await fetch(`/buscar?q=${encodeURIComponent(q)}`);
      const data = await res.json();

      if (!Array.isArray(data) || data.length === 0) {
        resultsBox.innerHTML = "<div class='search-item'>No se encontraron resultados</div>";
      } else {
        resultsBox.innerHTML = data.map(item => {
          if (item.tipo === 'cancion') {
            return `
              <div class="search-item song-result"
                   data-id="${item.id}">
                <img src="${item.avatar}" alt="canción">
                <div>
                  <strong>${item.nombre}</strong><br>
                  <small>🎵 Canción — ${item.artist || ''}</small>
                </div>
                <!-- Botón oculto -->
                <button class="cancion-item" style="display:none"
                        data-id="${item.id}"
                        data-src="${item.audio || ''}"
                        data-title="${item.nombre}"
                        data-artist="${item.artist || 'Desconocido'}"
                        data-cover="${item.avatar}">
                </button>
              </div>
            `;
          }
          return `
            <a href="${item.url}" class="search-item">
              <img src="${item.avatar}" alt="${item.tipo}">
              <div>
                <strong>${item.nombre}</strong><br>
                <small>${item.tipo === 'usuario' ? '👤 Usuario' : '📀 Álbum'}</small>
              </div>
            </a>
          `;
        }).join('');
        bindSearchSongs();
      }

      resultsBox.style.display = "block";
    } catch (err) {
      console.error("Error en búsqueda:", err);
      resultsBox.innerHTML = "<div class='search-item'>Error al buscar</div>";
      resultsBox.style.display = "block";
    }
  });

  document.addEventListener('click', (e) => {
    if (!resultsBox.contains(e.target) && e.target !== searchInput) {
      resultsBox.style.display = "none";
    }
  });
}
</script>
