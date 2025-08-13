<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AURA — Interfaz</title>
  @vite('resources/css/menu.css')
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  {{-- Fondo Aurora --}}
  @include('components.aurora-background')

  <div class="app">
    <!-- ===== BARRA LATERAL ===== -->
    <aside class="sidebar" id="sidebar">
      <a href="#" class="brand" aria-label="AURA Inicio">
        <div class="logo-clip">
          <img src="../img/Aura_LOGO.png" alt="Logo de AURA" class="logo-img">
        </div>
      </a>
     <nav class="menu">
    <a href="#" class="item active"><i class="fas fa-house"></i><span class="label">Inicio</span></a>
    <a href="menu_album" class="item"><i class="fas fa-heart"></i><span class="label">Favoritos</span></a>
    <a href="menu_artista" class="item"><i class="fas fa-layer-group"></i><span class="label">Biblioteca</span></a>

    {{-- Botón "Subir música" solo si es artista --}}
    @auth
        @if(auth()->user()->es_artista)
            <a href="{{ route('musica.subir') }}" class="item">
                <i class="fas fa-upload"></i>
                <span class="label">Subir Música</span>
            </a>
        @endif
    @endauth

    <div class="menu-sep"></div>
</nav>
    </aside>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <main class="main-content">
    <header class="header">
  <!-- buscador -->
  <div class="header-search-group">
    <span class="search-icon"></span>
    <input class="search" type="text" placeholder="Buscar..." />
    <button class="filter-btn">
      <i class="fa-solid fa-sliders"></i>
      Filtros
    </button>
  </div>

  <!-- barra superior derecha -->
  <div class="topbar">
    <div class="quick-actions" role="toolbar" aria-label="Acciones rápidas">
      <button class="qa-btn" title="Verificados"><i class="fa-regular fa-circle-check"></i></button>
      <button class="qa-btn" title="Mensajes"><i class="fa-regular fa-message"></i></button>
      <button class="qa-btn" title="Ajustes"><i class="fa-solid fa-gear"></i></button>
      <button class="qa-btn" title="Notificaciones"><i class="fa-regular fa-bell"></i></button>
    </div>

<div class="user-menu">
    <button class="user-chip" id="userMenuBtn" title="Cuenta">
        <img class="chip-avatar" src="{{ auth()->user()->avatar ?? 'https://i.ibb.co/album-profile.png' }}" alt="Perfil">
        <span class="chip-name">
            {{ auth()->user()->es_artista ? auth()->user()->nombre_artistico : auth()->user()->nombre }}
        </span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>

    <div class="dropdown-menu" id="userDropdown" style="display: none;">
        <a href="{{ route('ed_perfil') }}" class="dropdown-item">
            <i class="fa-solid fa-user"></i> Perfil
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item logout">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </button>
        </form>
    </div>
</div>

  </div>
</header>


      <section class="playlist-section">
        <h2>Lista de reproducción para ti</h2>
        <div class="playlist-grid">
          <div class="playlist-card green element-glow">
            <p class="subtitle">LISTA DE REPRODUCCIÓN</p>
            <p class="desc">Wiz Khalifa, Post Malone, Soulja Boy y más</p>
            <p class="info">Hecho para Guilherme Dourado - 50 canciones, 3h 56min</p>
            <h3 class="title">Mezcla diaria 3</h3>
            <img src="../img/ChatGPT Image 10 ago 2025, 01_21_55.png" alt="Chica" />
            <p class="likes">+ 53K Me gusta</p>
          </div>

          <div class="playlist-card pink">
            <img src="../img/ChatGPT Image 10 ago 2025, 01_21_55.png" alt="Chica" />
            <div class="overlay-text">
              <h3>Mezcla relajada</h3>
              <p>Solo relájate y escucha</p>
              <span class="emoji">▶️</span>
            </div>
          </div>

          <div class="playlist-card teal">
            <img src="../img/ChatGPT Image 10 ago 2025, 01_27_09.png" alt="Hombre con micrófono" />
            <div class="overlay-text">
              <h3>Mezcla relajada</h3>
              <p>Solo relájate y escucha</p>
              <span class="emoji">▶️</span>
            </div>
          </div>

          <div class="playlist-card purple">
            <img src="../img/ChatGPT Image 10 ago 2025, 01_21_55.png" alt="Chica" />
            <div class="overlay-text">
              <h3>Mezcla relajada</h3>
              <p>Solo relájate y escucha</p>
              <span class="emoji">▶️</span>
            </div>
          </div>
        </div>
      </section>

      <section class="music-sections">
        <div class="selected-album">
          <h2><span class="emoji">💿</span> Álbum seleccionado</h2>
          <div class="album-info">
            <img src="https://i.ibb.co/0C9yyJ8/album-cover.jpg" alt="Portada del álbum">
            <div>
              <h3>Melodías del mañana</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit ut aliquam, venenatis</p>
              <span>64 canciones – 16 horas</span>
            </div>
          </div>
        </div>

        <div class="tracks">
          <h2><span class="emoji">🎵</span> Canciones de la semana</h2>
          <ul>
            <li>
              <img class="track-cover" src="https://i.ibb.co/SsXsw4r/rnb.png" alt="Portada de canción">
              <div class="track-info">
                <span class="track-title">Thế Giới Của Em</span>
                <div class="track-meta">
                  <span class="icon">🎧</span><span>311k</span>
                </div>
              </div>
              <button class="track-action" title="Me gusta">💚</button>
            </li>
            <li>
              <img class="track-cover" src="https://i.ibb.co/2hXmsv3/trap1.png" alt="Portada de canción">
              <div class="track-info">
                <span class="track-title">Phố Không Em</span>
                <div class="track-meta">
                  <span class="icon">🎧</span><span>311k</span>
                </div>
              </div>
              <button class="track-action" title="Me gusta">💚</button>
            </li>
            <li>
              <img class="track-cover" src="https://i.ibb.co/hFFDLmp/crush.png" alt="Portada de canción">
              <div class="track-info">
                <span class="track-title">Chạm</span>
                <div class="track-meta">
                  <span class="icon">🎧</span><span>311k</span>
                </div>
              </div>
              <button class="track-action" title="Me gusta">💚</button>
            </li>
            <li>
              <img class="track-cover" src="https://i.ibb.co/yqTCJby/pop.png" alt="Portada de canción">
              <div class="track-info">
                <span class="track-title">Muộn Màng Là Từ Lúc</span>
                <div class="track-meta">
                  <span class="icon">🎧</span><span>311k</span>
                </div>
              </div>
              <button class="track-action" title="Me gusta">💚</button>
            </li>
          </ul>
        </div>

        <div class="suggested">
          <h2>Álbum sugerido para ti</h2>
          <div class="album-grid">
            <div class="album-box"><img src="https://i.ibb.co/SsXsw4r/rnb.png"><p>Mezcla R&B</p></div>
            <div class="album-box"><img src="https://i.ibb.co/2hXmsv3/trap1.png"><p>Mezcla Trap</p></div>
            <div class="album-box"><img src="https://i.ibb.co/2hXmsv3/trap1.png"><p>Mezcla Trap</p></div>
            <div class="album-box"><img src="https://i.ibb.co/hFFDLmp/crush.png"><p>Tengo un crush en ti</p></div>
            <div class="album-box"><img src="https://i.ibb.co/yqTCJby/pop.png"><p>Mezcla Pop</p></div>
            <div class="album-box"><img src="https://i.ibb.co/nPGzPLJ/rap.png"><p>Mezcla Rap</p></div>
          </div>
        </div>
      </section>
    </main>

  @include('components.footer')

  </div>
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('userMenuBtn');
    const menu = document.getElementById('userDropdown');

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', () => {
        menu.style.display = 'none';
    });
});
</script>
