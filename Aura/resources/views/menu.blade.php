
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


  <div class="app">
  @include('components.traductor')


<div class="with-sidebar">
  @include('components.sidebar')   {{-- Sidebar fijo a la izquierda --}}
        @include('components.header')
        @include('components.traductor')

  <main class="main-content">

      <section class="playlist-section">
        <h2>Lista de reproducción para ti</h2>
        <div class="playlist-grid">
          <div class="playlist-card green element-glow">
            <p class="subtitle"></p>
            <p class="desc">Mario Rivera, Dakzze, Grupo Algodon y mas...</p>
            <p class="info">Hecho para los salvadoreños y el resto del mundo, + 20 canciones.</p>
            <br>
            <h3 class="title">Mezcla Salvadoreña</h3>
            <img src="../img/Marito-Rivera.jpg" alt="Chica" />
            <br>
            <p class="likes">+ 53K Me gusta</p>
          </div>

          <div class="playlist-card pink">
            <img src="../img/weeknd 2.jpeg" alt="Chica" />
            <div class="overlay-text">
              <h3>Internacionales</h3>
              <p>Conoce lo nuevo</p>
              <span class="emoji">▶️</span>
            </div>
          </div>

          <div class="playlist-card teal">
            <img src="../img/images.jpg" alt="Hombre con micrófono" />
            <div class="overlay-text">
              <h3>Mezcla relajada</h3>
              <p>Solo relájate y escucha</p>
              <span class="emoji">▶️</span>
            </div>
          </div>

          <div class="playlist-card purple">
            <img src="../img/alejo-dii.jpg" alt="Chica" />
            <div class="overlay-text">
              <h3>Exitos de El Salvador</h3>
              <p>Conoce lo mejor del país</p>
              <span class="emoji">▶️</span>
            </div>
          </div>
        </div>
      </section>

      <section class="music-sections">
        <div class="selected-album">
          <h2><span class="emoji">💿</span> Álbum seleccionado</h2>
          <div class="album-info">
            <img src="../img/111xpantia.jpg" alt="Portada del álbum">
            <div>
              <h3>ÁLBUM DE LA SEMANA</h3>
              <p>Nuestro ganador semanal para el 01/09/2025 es:</p>
              <span>111 X PANTIA</span>
            </div>
          </div>
          <br>
          <br>
          <div class="album-info">
            <img src="../img/AM.jpg" alt="Portada del álbum">
            <div>
              <h3>ÁLBUM DE EL MES</h3>
              <p>Nuestro ganador semanal para el 01/09/2025 es:</p>
              <span>AM DE ARCTICS MONKEYS</span>
            </div>
          </div>
        </div>

        <div class="tracks">
          <h2><span class="emoji">🎵</span> Canciones de la semana</h2>
          <ul>
            <li>
              <img class="track-cover" src="../img/company.png" alt="Portada de canción">
              <div class="track-info">
                <span class="track-title">Company</span>
                <div class="track-meta">
                  <span class="icon">🎧</span><span>139M</span>
                </div>
              </div>
              <button class="track-action" title="Me gusta"> 💜 </button>
            </li>
            <li>
              <img class="track-cover" src="../img/ab67616d0000b2732690127e1d6cd0aa35cc353b.jpg" alt="Portada de canción">
              <div class="track-info">
                <span class="track-title">14-14</span>
                <div class="track-meta">
                  <span class="icon">🎧</span><span>911M</span>
                </div>
              </div>
              <button class="track-action" title="Me gusta"> 💜 </button>
            </li>
            <li>
              <img class="track-cover" src="../img/kisao.jpg" alt="Portada de canción">
              <div class="track-info">
                <span class="track-title">Kisao</span>
                <div class="track-meta">
                  <span class="icon">🎧</span><span>817M</span>
                </div>
              </div>
              <button class="track-action" title="Me gusta"> 💜 </button>
            </li>
            <li>
              <img class="track-cover" src="../img/gorillaz-anuncia-album-colaboracion-bad.jpg" alt="Portada de canción">
              <div class="track-info">
                <span class="track-title">Tormenta</span>
                <div class="track-meta">
                  <span class="icon">🎧</span><span>219M</span>
                </div>
              </div>
              <button class="track-action" title="Me gusta"> 💜 </button>
            </li>
          </ul>
        </div>

        <div class="suggested">
          <h2>Álbum sugerido para ti</h2>
          <div class="album-grid">
            <div class="album-box"><img src="../img/felicilandia.jpg"><p>Felicilandia</p></div>
            <div class="album-box"><img src="../img/sayonara.jpg"><p>SAYONARA</p></div>
            <div class="album-box"><img src="../img/bienomal.png"><p>BIEN O MAL</p></div>
            <div class="album-box"><img src="../img/unverano.jpg"><p>Un verano sin ti</p></div>
            <div class="album-box"><img src="../img/1xpantia.jpg"><p>X 100 PRE</p></div>
            <div class="album-box"><img src="../img/fresita.jpg"><p>Pero no te enamores</p></div>
          </div>
        </div>
      </section>

      <section class="artist-carousel">
        <h2>Artistas Destacados</h2>
        <div class="carousel-container">
          <div class="carousel-track">
            <div class="artist-item">
              <img src="../img/alvarotorres.jpg" alt="Artista 1">
              <p>ALVARO TORRES </p>
            </div>
            <div class="artist-item">
              <img src="../img/raros.webp" alt="Artista 2">
              <p> RUCKS Y PARKER</p>
            </div>
            <div class="artist-item">
              <img src="../img/red.jpg" alt="Artista 3">
              <p>LOS RED </p>
            </div>
            <div class="artist-item">
              <img src="../img/tachos.jpg" alt="Artista 4">
              <p>LOS TACHOS </p>
            </div>
            <div class="artist-item">
              <img src="../img/king.jpg" alt="Artista 5">
              <p>KING FLYP</p>
            </div>
            <div class="artist-item">
              <img src="../img/adrenalina.webp" alt="Artista 6">
              <p>ADRENALINA</p>
            </div>
            <div class="artist-item">
              <img src="../img/dddd.webp" alt="Artista 7">
              <p>DAKZZE</p>
            </div>
            <div class="artist-item">
              <img src="../img/Analu-Dada-cortesia.jpg" alt="Artista 8">
              <p>ANALU DADA</p>
            </div>
            <div class="artist-item">
              <img src="../img/raza.jpg" alt="Artista 9">
              <p>LA RAZA BAN</p>
            </div>
            <div class="artist-item">
              <img src="../img/hermanos.png" alt="Artista 10">
              <p>LOS HERMANOS FLORES</p>
            </div>
            <!-- Duplicates for seamless loop -->
            <div class="artist-item">
              <img src="../img/alvarotorres.jpg" alt="Artista 1">
              <p>ALVARO TORRES </p>
            </div>
            <div class="artist-item">
              <img src="../img/raros.webp" alt="Artista 2">
              <p> RUCKS Y PARKER</p>
            </div>
            <div class="artist-item">
              <img src="../img/red.jpg" alt="Artista 3">
              <p>LOS RED </p>
            </div>
            <div class="artist-item">
              <img src="../img/tachos.jpg" alt="Artista 4">
              <p>LOS TACHOS </p>
            </div>
          </div>
        </div>

  </main>
  </div>
    @include('components.footer')
</body>
</html>

