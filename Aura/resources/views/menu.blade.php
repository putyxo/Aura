<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>AURA</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite('resources/css/menu.css')
  @vite('resources/js/carousel.js')
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{ --player-width:380px; --player-gap:20px; }
    /* empuje por el player */
    .main-content{ margin-right: calc(var(--player-width) + var(--player-gap)); }
    /* player fijo derecha (el resto del styling ya puede ir en tu CSS) */
    .player-card{ position:fixed; top:0; right:0; bottom:0; width:var(--player-width); z-index:1300; }
    /* si tu header es fixed */
    .header{ padding-right: calc(var(--player-width) + var(--player-gap)); }
    @media (max-width:860px){ :root{ --player-width:0px } .player-card{display:none} .main-content{margin-right:var(--player-gap)} }
  </style>
</head>

<body>





<div class="with-sidebar">
  @include('components.sidebar')   {{-- Sidebar fijo a la izquierda --}}
        @include('components.header')
        @include('components.traductor')
        @include('components.fondo')

  <main class="main-content">

      <section class="playlist-section">
        <h2>{{ __('menu.playlist_for_you') }}</h2>
        <div class="playlist-grid">
          <div class="playlist-card green element-glow">
            <p class="subtitle"></p>
            <p class="desc">{{ __('menu.salvadoran_mix_desc') }}</p>
            <p class="info">{{ __('menu.salvadoran_mix_info') }}</p>
            <br>
            <h3 class="title">{{ __('menu.salvadoran_mix') }}</h3>
            <img src="../img/Marito-Rivera.jpg" alt="Chica" />
            <br>
            <p class="likes">+ 53K Me gusta</p>
          </div>

          <div class="playlist-card pink">
            <img src="../img/weeknd 2.jpeg" alt="Chica" />
            <div class="overlay-text">
              <h3>{{ __('menu.international') }}</h3>
              <p>{{ __('menu.international_desc') }}</p>
              <span class="emoji"></span>
            </div>
          </div>

          <div class="playlist-card teal">
            <img src="../img/images.jpg" alt="Hombre con micrófono" />
            <div class="overlay-text">
              <h3>{{ __('menu.relax_mix') }}</h3>
              <p>{{ __('menu.relax_mix_desc') }}</p>
              <span class="emoji"></span>
            </div>
          </div>

          <div class="playlist-card purple">
            <img src="../img/alejo-dii.jpg" alt="Chica" />
            <div class="overlay-text">
              <h3>{{ __('menu.el_salvador_hits') }}</h3>
              <p>{{ __('menu.el_salvador_hits_desc') }}</p>
              <span class="emoji"></span>
            </div>
          </div>
        </div>
      </section>

      <section class="music-sections">
        <div class="selected-album">
          <h2><span class="emoji">💿</span> {{ __('menu.album_selected') }}</h2>
          <div class="album-info">
            <img src="../img/111xpantia.jpg" alt="Portada del álbum">
              <div>
                <h3>{{ __('menu.album_of_week') }}</h3>
                <p>{{ __('menu.winner_text') }}</p>
                <span>111 X PANTIA</span>
              </div>
            </div>
            <br>
            <br>
            <div class="album-info">
              <img src="../img/AM.jpg" alt="Portada del álbum">
              <div>
                <h3>{{ __('menu.album_of_month') }}</h3>
                <p>{{ __('menu.winner_text') }}</p>
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
          </div>
        </div>

  </main>
       </div>
       @include('components.footer')
  </div>

    @stack('scripts')
</body>
</html>

