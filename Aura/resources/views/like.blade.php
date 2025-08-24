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



    <main class="main-content">

      <!-- Sección izquierda: lista / contenido existente -->
      <section class="songs-section">
        <!-- Encabezado visual (opcional, sin lógica) -->
        <header class="favorite-header surface">
          <img src="{{ asset('img/like-cover.png') }}" alt="Favoritos" />
          <div class="playlist-info">
            <span class="playlist-type">Lista</span>
            <h1 class="playlist-title">Canciones que te gustan</h1>
            <span class="playlist-user">Tus me gusta</span>
          </div>
        </header>

        <!-- Acciones visuales (sin funcionalidad nueva) -->
        <div class="favorite-actions">
          <button class="btn-play" aria-label="Reproducir">&#9658;</button>
          <button class="btn-icon">Aleatorio</button>
          <button class="btn-icon">Descargar</button>
        </div>

        <!-- Contenido original del apartado -->
        <div class="empty-state">
          <div class="empty-box">
            <h2>Aún no se ha dado like a nada</h2>
          </div>
        </div>

        <!--
          aki hay q agregar lo de la funcionaliad puty
        -->
      </section>

      <!-- Sección derecha: cola de reproducción (estética, estática) -->
      <aside class="queue-section">
        <h3>Cola de reproducción</h3>
        <div class="queue-list">
          <div class="queue-item">
            <img src="https://via.placeholder.com/56" alt="Carátula">
            <div>
              <p class="q-title">Pista en cola</p>
              <p class="q-artist">Artista</p>
            </div>
          </div>
          <div class="queue-item">
            <img src="https://via.placeholder.com/56" alt="Carátula">
            <div>
              <p class="q-title">Otra pista</p>
              <p class="q-artist">Artista</p>
            </div>
          </div>
          <div class="queue-item">
            <img src="https://via.placeholder.com/56" alt="Carátula">
            <div>
              <p class="q-title">Siguiente</p>
              <p class="q-artist">Artista</p>
            </div>
          </div>
        </div>
      </aside>

    </main>

    @include('components.footer')
  </div>
</body>
</html>
