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


<script>
// ===================== USER MENU DROPDOWN =====================
(function initUserMenu(){
  const root      = document.querySelector('.user-menu');
  const btn       = document.getElementById('userMenuBtn');
  const dropdown  = document.getElementById('userDropdown');

  if (!root || !btn || !dropdown) return;

  // Evita doble-bind al recargar con PJAX
  if (btn.dataset.bound === 'true') return;
  btn.dataset.bound = 'true';

  function openMenu(){
    root.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    dropdown.setAttribute('aria-hidden', 'false');
  }
  function closeMenu(){
    root.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    dropdown.setAttribute('aria-hidden', 'true');
  }
  function toggleMenu(){
    if (root.classList.contains('open')) closeMenu(); else openMenu();
  }

  // Click en el chip: abrir/cerrar
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggleMenu();
  });

  // Cerrar al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!root.contains(e.target)) closeMenu();
  });

  // Cerrar con Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });

  // Si el dropdown tiene links, no dejes que el click cierre antes de tiempo
  dropdown.addEventListener('click', (e) => {
    // Permite clicks normales en enlaces y botones, pero no cierres por burbujeo
    e.stopPropagation();
  });

  // Soporte para PJAX: re-inicializar tras navegación parcial
  if (window.$ && $.pjax) {
    $(document).on('pjax:success', function(){ 
      // Re-intenta inicializar por si el header se re-renderizó
      setTimeout(initUserMenu, 0);
    });
  }

  // (Opcional) Autocargar avatar si usas un campo en el backend
  try {
    const avatarEls = root.querySelectorAll('.chip-avatar, .profile-avatar');
    const fallback  = "{{ asset('img/default-avatar.png') }}";
    // Si el backend ya imprime src, no hace falta esto.
    avatarEls.forEach(img => {
      if (!img.getAttribute('src')) img.setAttribute('src', fallback);
    });
  } catch (_) {}
})();
</script>