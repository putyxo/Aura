<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Favoritos</title>
  @vite('resources/css/like.css')
</head>
<body>
@yield('content')
@include('components.traductor')
  <div class="page-container">
    @include('components.sidebar')
        @include('components.header')
        @include('components.traductor')
        @include('components.fondo')


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

  <div class="songs-list">
    @forelse($canciones as $song)
      @php
        // Portada
        $coverUrl = $song->cover_url
            ? drive_img_url($song->cover_url, 300)
            : asset('img/default-cancion.png');

        // Audio (drive o directo)
        $rawAudio = $song->audio_url;
        $audioUrl = null;
        if ($rawAudio) {
            if (Str::contains($rawAudio, 'drive.google')) {
                if (preg_match('~/d/([^/]+)~', $rawAudio, $m)) {
                    $id = $m[1];
                } elseif (preg_match('~[?&]id=([^&]+)~', $rawAudio, $m)) {
                    $id = $m[1];
                } else {
                    $id = null;
                }
                $audioUrl = $id ? route('media.drive', ['id' => $id]) : $rawAudio;
            } else {
                $audioUrl = $rawAudio;
            }
        }
      @endphp

      <div class="song-row" data-song-id="{{ $song->id }}">
        <div class="song-left">
          <img src="{{ $coverUrl }}" alt="cover">
          <div class="song-info">
            <h4>{{ $song->title }}</h4>
            <p>{{ $song->title }}</p>
          </div>
        </div>

        <!-- Botón de reproducir -->
        <button class="play-song-btn" title="Reproducir" data-id="{{ $song->id }}" data-src="{{ $audioUrl }}" data-title="{{ $song->title }}" data-artist="{{ $song->title }}" data-cover="{{ $coverUrl }}">
          <i class="fa-solid fa-play"></i>
        </button>

        <div class="song-duration">{{ $song->duration ?? '0:00' }}</div>

        <!-- botón invisible para el reproductor -->
        <button class="cancion-item"
                style="display:none"
                data-id="{{ $song->id }}"
                data-src="{{ $audioUrl }}"
                data-title="{{ $song->title }}"
                data-artist="{{ $song->title }}"
                data-cover="{{ $coverUrl }}">
        </button>

        <!-- Botón para quitar de favoritos -->
        <form action="{{ route('canciones.like', $song->id) }}" method="POST" onsubmit="return confirm('¿Quitar de favoritos?')">
          @csrf
          <button type="submit" class="delete-btn"><i class="fa-solid fa-heart-crack"></i></button>
        </form>
      </div>
    @empty
      <p class="empty-msg"><i class="fa-solid fa-circle-exclamation"></i> Aún no has dado like a ninguna canción.</p>
    @endforelse
  </div>
</section>


   

    </main>

    @include('components.footer')
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Función para formatear tiempo
      function formatTime(seconds) {
        if (!seconds || !isFinite(seconds)) return '--:--';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
      }

      // Animación hover para filas de canciones
      function initSongHoverEffects() {
        document.querySelectorAll('.song-row').forEach(row => {
          row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.boxShadow = '0 12px 24px rgba(0,0,0,.15)';
            this.style.zIndex = '10';
          });

          row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = 'none';
            this.style.zIndex = '1';
          });
        });
      }

      // Calcular duración real de las canciones
      function calculateDurations() {
        document.querySelectorAll('.song-row').forEach(row => {
          const audioUrl = row.querySelector('.play-song-btn')?.dataset.src;
          const durationEl = row.querySelector('.song-duration');

          if (audioUrl && durationEl && durationEl.textContent.trim() === '0:00') {
            const audio = new Audio();
            audio.preload = 'metadata';
            audio.src = audioUrl;

            audio.addEventListener('loadedmetadata', () => {
              const duration = formatTime(audio.duration);
              durationEl.textContent = duration;
              durationEl.style.opacity = '1';

              // Opcional: enviar al servidor para guardar en BD
              const songId = row.dataset.songId;
              if (songId) {
                fetch(`/canciones/${songId}/update-duration`, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                  },
                  body: JSON.stringify({ duration: audio.duration })
                }).catch(() => {
                  // Silenciar errores de actualización
                });
              }
            });

            audio.addEventListener('error', () => {
              durationEl.textContent = '--:--';
            });
          }
        });
      }

      // Botones de reproducir en filas de canciones
      document.querySelectorAll('.play-song-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const data = {
            id: this.dataset.id,
            src: this.dataset.src,
            title: this.dataset.title,
            artist: this.dataset.artist,
            cover: this.dataset.cover
          };
          if (window.AuraPlayer && window.AuraPlayer.play) {
            window.AuraPlayer.play(data);
          }
        });
      });

      // Botón de reproducir en la tarjeta de favoritos
      const favoritePlayBtn = document.querySelector('.favorite-actions .btn:first-child');
      if (favoritePlayBtn) {
        favoritePlayBtn.addEventListener('click', function(e) {
          e.preventDefault();
          const firstSongBtn = document.querySelector('.play-song-btn');
          if (firstSongBtn) {
            firstSongBtn.click();
          }
        });
      }

      // Botón de aleatorio en la tarjeta de favoritos
      const favoriteShuffleBtn = document.querySelector('.favorite-actions .btn:last-child');
      if (favoriteShuffleBtn) {
        favoriteShuffleBtn.addEventListener('click', function(e) {
          e.preventDefault();
          const songBtns = document.querySelectorAll('.play-song-btn');
          if (songBtns.length > 0) {
            const randomIndex = Math.floor(Math.random() * songBtns.length);
            songBtns[randomIndex].click();
          }
        });
      }

      // Inicializar efectos hover
      initSongHoverEffects();

      // Calcular duraciones al cargar la página
      calculateDurations();
    });
  </script>

</body>
</html>
