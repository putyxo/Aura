<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AURA — Favoritos</title>
  @vite('resources/css/like.css')
</head>
<body>
@yield('content')
@include('components.traductor')
  <div class="page-container">
    @include('components.sidebar')
        @include('components.header')



    <main class="main-content">

<div class="favorite-card">
  <div class="favorite-cover">
    <img src="img/like_icono.jpg" alt="Favoritos">
  </div>

  <div class="favorite-info">
    <span class="favorite-subtitle">Lista</span>
    <h2 class="favorite-title">Favoritos</h2>
    <span class="favorite-user">Tus me gusta</span>

    <div class="favorite-actions">
      <button class="btn"><i class="fa-solid fa-play"></i> Reproducir</button>
      <button class="btn"><i class="fa-solid fa-shuffle"></i> Aleatorio</button>
    </div>
  </div>
</div>

<section class="likes-section">
  <h3 class="likes-header"><i class="fa-solid fa-heart"></i> Canciones que te gustan</h3>

  <div class="likes-list">
    <ul>
      <li class="like-item">
        <div class="like-info">
          <span class="like-title">Título de canción</span>
          <span class="like-artist">Artista</span>
        </div>
        <button class="like-play"><i class="fa-solid fa-play"></i></button>
      </li>
      <!-- Más items aquí -->
    </ul>

    <!-- Estado vacío -->
    <p class="empty-msg"><i class="fa-solid fa-circle-exclamation"></i> Aún no has dado like a ninguna canción.</p>
  </div>
</section>


   

    </main>

    @include('components.footer')
  </div>
</body>
</html>
