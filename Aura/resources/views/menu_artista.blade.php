<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Weeknd - Artist Page</title>
 @vite('resources/css/menu_artista.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
        @yield('content')
@include('components.traductor')
    <div class="app-container">
        <!-- Navigation Bar -->
        <nav class="navbar">
            <div class="nav-controls">
<a href="/menu" class="nav-button">
    <i class="fas fa-chevron-left"></i>
</a>

<a href="/ruta-siguiente" class="nav-button">
    <i class="fas fa-chevron-right"></i>
</a>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Search artist, playlist, song">
                <button class="search-button"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-controls">
                <button class="home-button">Home <i class="fas fa-home"></i></button>
                <div class="user-profile">
                    <span>Natalia</span>
                    <img src="img/foto perfil.jpeg" alt="Perfil de Abhay" class="profile-pic" />
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Artist Header -->
            <div class="artist-header">
                <div class="artist-image">
                    <img src="img/perfil weeknd.jpeg" alt="The Weeknd">
                </div>
                <div class="artist-info">
                    <h1>The Weeknd</h1>
                    <p class="monthly-listeners">112,845,675 Monthly Listeners</p>
                    <button class="follow-button">Follow</button>
                    <button class="play-button"><i class="fas fa-play"></i></button>
                </div>
            </div>

      <!-- Debajo de .artist-info -->
<div class="artist-sections two-col">
  <!-- Columna izquierda: POPULAR -->
  <section class="popular-section">
    <h2>Popular</h2>
    <div class="track-list">
      <!-- tus tracks tal cual -->
      <div class="track">
        <div class="track-left">
          <div class="track-image">
            <img src="img/weeknd 2.jpeg" alt="Starboy">
          </div>
          <div class="track-info">
            <span class="track-name">Starboy</span>
            <span class="track-number">4,030,512,289</span>
          </div>
        </div>
        <div class="track-duration">3:34</div>
      </div>

      <div class="track">
        <div class="track-left">
          <div class="track-image">
            <img src="img/after hours album.jpeg" alt="Blinding Lights">
          </div>
          <div class="track-info">
            <span class="track-name">Blinding Lights</span>
            <span class="track-number">4,967,878,668</span>
          </div>
        </div>
        <div class="track-duration">3:14</div>
      </div>

      <div class="track">
        <div class="track-left">
          <div class="track-image">
            <img src="img/weeknd 2.jpeg" alt="Die For You">
          </div>
          <div class="track-info">
            <span class="track-name">Die For You</span>
            <span class="track-number">2,895,532,052</span>
          </div>
        </div>
        <div class="track-duration">4:18</div>
      </div>

      <div class="track">
        <div class="track-left">
          <div class="track-image">
            <img src="img/the hills cancion.jpeg" alt="The Hills">
          </div>
          <div class="track-info">
            <span class="track-name">The Hills</span>
            <span class="track-number">2,739,891,001</span>
          </div>
        </div>
        <div class="track-duration">4:00</div>
      </div>
    </div>
  </section>

  <!-- Columna derecha: ALBUMS -->
<!-- Columna derecha: ALBUMS + SENCILLOS -->
<section class="albums-section">
  <div class="albums-header">
    <h2>Albums</h2>
    <button class="toggle-singles">Sencillos</button>
  </div>

  <div class="album-grid">
    <div class="album">
      <img src="img/weeknd 2.jpeg" alt="Starboy">
      <span class="album-name">Starboy</span>
    </div>
    <div class="album">
      <img src="img/idol album.jpeg" alt="One of the Girls">
      <span class="album-name">One of the girls</span>
    </div>
    <div class="album">
      <img src="img/trilogy album.jpeg" alt="Trilogy">
      <span class="album-name">Trilogy</span>
    </div>
    <div class="album">
      <img src="img/after hours album.jpeg" alt="After Hours">
      <span class="album-name">After Hours</span>
    </div>
  </div>

  <!-- Apartado oculto de sencillos -->
  <div class="singles-list" style="display:none;">
    <div class="single">
      <img src="img/the hills cancion.jpeg" alt="The Hills">
      <span class="single-name">The Hills</span>
    </div>
    <div class="single">
      <img src="img/weeknd 2.jpeg" alt="Die For You">
      <span class="single-name">Die For You</span>
    </div>
    <div class="single">
      <img src="img/after hours album.jpeg" alt="Save Your Tears">
      <span class="single-name">Save Your Tears</span>
    </div>
  </div>
</section>
