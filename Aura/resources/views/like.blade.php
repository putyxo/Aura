<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
<<<<<<< HEAD
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Favoritos</title>
  @vite('resources/css/like.css')
=======
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Me gusta</title>

  {{-- Fuente + Iconos --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- Vite CSS --}}
  @vite(['resources/css/like.css'])
>>>>>>> Parte-ubitzo
</head>
<body>
<div class="lk-app">
  <div class="lk-with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    {{-- HOTFIX: garantiza que $likedSongs exista como Collection --}}
    @php
        if (!isset($likedSongs) || is_null($likedSongs)) {
            $likedSongs = collect();
        } elseif (is_array($likedSongs)) {
            $likedSongs = collect($likedSongs);
        }
    @endphp

<<<<<<< HEAD
      <div class="song-row" data-song-id="{{ $song->id }}">
        <div class="song-left">
          <img src="{{ $coverUrl }}" alt="cover">
          <div class="song-info">
            <h4>{{ $song->title }}</h4>
            <p>{{ $song->title }}</p>
=======
    <main class="main-content lk-page" data-page="likes">
      <div class="lk-shell">

        {{-- ================== HERO ================== --}}
        <section class="lk-hero" aria-label="Tus Me gusta">
          <div class="lk-hero__bg"></div>

          <div class="lk-hero__row">
            <div class="lk-hero__content">
              <div class="lk-hero__icon"><i class="fa-solid fa-heart"></i></div>
              <div>
                <h1 class="lk-hero__title">Tus Me gusta</h1>
                <p class="lk-hero__sub">
                  {{ number_format($likedSongs->count()) }} canciones guardadas para volver siempre.
                </p>
              </div>
            </div>

            <div class="lk-hero__actions">
              <div class="lk-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="lkSearch" type="search" placeholder="Buscar canción o artista..." aria-label="Buscar en Me gusta" autocomplete="off">
                <button class="lk-clear" id="lkClear" aria-label="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></button>
              </div>
            </div>
>>>>>>> Parte-ubitzo
          </div>
        </section>

<<<<<<< HEAD
        <!-- Botón de reproducir -->
        <button class="play-song-btn" title="Reproducir" data-id="{{ $song->id }}" data-src="{{ $audioUrl }}" data-title="{{ $song->title }}" data-artist="{{ $song->title }}" data-cover="{{ $coverUrl }}">
          <i class="fa-solid fa-play"></i>
        </button>

        <div class="song-duration">{{ $song->duration ?? '0:00' }}</div>
=======
        {{-- Toast --}}
        @if(session('ok'))
          <div class="lk-toast lk-toast--ok" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif
>>>>>>> Parte-ubitzo

        {{-- ================== GRID ================== --}}
        <section class="lk-grid" id="lkGrid" data-count="{{ $likedSongs->count() }}">

          @php use Illuminate\Support\Str; @endphp

          @forelse($likedSongs as $song)
            @php
              // Campos tolerantes (ES/EN) para diferentes esquemas
              $title  = $song->title  ?? $song->titulo  ?? $song->name ?? 'Sin título';
              $artist = optional($song->user)->nombre_artistico ?? optional($song->user)->nombre ?? 'Artista';

              $cover  = $song->cover_path ?? $song->portada ?? $song->imagen ?? null;
              $audio  = $song->audio_path ?? $song->ruta_audio ?? $song->file_url ?? null;
              $dur    = $song->duration ?? $song->duracion ?? null;

              // Si guardas IDs de Drive en vez de URLs
              if ($cover && is_numeric($cover)) $cover = route('media.drive', ['id' => $cover]);
              if ($audio && is_numeric($audio)) $audio = route('media.drive', ['id' => $audio]);

              $cover = $cover ?: asset('img/default-cover.jpg');
              $searchKey = Str::lower(($title ?? '') . ' ' . $artist);
            @endphp

            <article
              class="lk-tile"
              title="{{ $title }}"
              data-name="{{ $searchKey }}"
              data-duration="{{ $dur ?? 0 }}"
              data-song-id="{{ $song->id }}"
            >
              <a class="lk-tile__link" aria-label="Abrir {{ $title }}"></a>

              <div class="lk-cover">
                <img src="{{ $cover }}" alt="Portada {{ $title }}" width="260" height="260" loading="lazy" decoding="async">
                <button type="button" class="lk-play" aria-label="Reproducir {{ $title }}">
                  <i class="fa-solid fa-play"></i>
                </button>
                <button type="button" class="lk-like is-liked" title="Quitar de Me gusta" aria-label="Quitar de Me gusta" data-unlike="{{ $song->id }}">
                  <i class="fa-solid fa-heart"></i>
                </button>
              </div>

              <div class="lk-meta">
                <div class="lk-title" title="{{ $title }}">{{ $title }}</div>
                <div class="lk-artist">{{ $artist }}</div>
              </div>

              {{-- Mini player mejorado --}}
              <div class="lk-player" aria-label="Mini reproductor">
                <audio preload="none" src="{{ $audio }}"></audio>

                <div class="lk-player__controls">
                  <button class="lk-btn lk-btn--sm lk-btn--primary lk-btn-play" aria-label="Reproducir">
                    <i class="fa-solid fa-play"></i>
                  </button>
                  <button class="lk-btn lk-btn--sm lk-btn--ghost lk-btn-mute" aria-label="Silenciar">
                    <i class="fa-solid fa-volume-high"></i>
                  </button>
                  <div class="lk-time">
                    <span class="lk-time__current">0:00</span>
                    <span class="lk-time__sep">/</span>
                    <span class="lk-time__total">{{ $dur ? gmdate('i:s', max(0,$dur)) : '--:--' }}</span>
                  </div>
                </div>

                <div class="lk-player__bar">
                  <input class="lk-seek" type="range" min="0" max="{{ $dur ?? 0 }}" value="0" step="1" aria-label="Barra de progreso">
                </div>
              </div>

              {{-- Fallback sin JS para quitar Me gusta --}}
              <form class="lk-like-form" method="POST" action="{{ url('likes/'.$song->id) }}">
                @csrf
                @method('DELETE')
              </form>
            </article>
          @empty
            <div class="lk-empty" style="grid-column:1/-1;">
              <i class="fa-solid fa-heart-crack"></i>
              <h3>Aún no tienes canciones en Me gusta</h3>
              <p>Descubre música y pulsa <i class="fa-solid fa-heart"></i> para guardarlas aquí.</p>
            </div>
          @endforelse
        </section>

      </div>
    </main>

    @include('components.footer')
  </div>
</div>

<<<<<<< HEAD
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

=======
{{-- ======= JS mínimo (inline) para el mini-player y buscador ======= --}}
<script>
(() => {
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const tiles  = $$('.lk-tile');
  const search = $('#lkSearch');
  const clear  = $('#lkClear');

  let current = null; // audio activo

  const fmt = (sec) => {
    sec = Math.max(0, Math.floor(sec || 0));
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
  };

  // Filtro de búsqueda
  if (search) {
    const apply = () => {
      const q = (search.value || '').trim().toLowerCase();
      tiles.forEach(t => {
        const hay = t.dataset.name || '';
        t.style.display = hay.includes(q) ? '' : 'none';
      });
    };
    search.addEventListener('input', apply);
    clear.addEventListener('click', () => { search.value=''; apply(); search.focus(); });
  }

  // Controles por tarjeta
  tiles.forEach(tile => {
    const audio    = $('audio', tile);
    const btnPlay  = $('.lk-btn-play', tile);
    const btnMute  = $('.lk-btn-mute', tile);
    const btnHeart = $('.lk-like', tile);
    const coverBtn = $('.lk-play', tile);
    const seek     = $('.lk-seek', tile);
    const tCur     = $('.lk-time__current', tile);
    const tTot     = $('.lk-time__total', tile);

    const togglePlay = () => {
      if (!audio) return;
      if (current && current !== audio) {
        current.pause();
        const prev = current.closest('.lk-tile');
        if (prev) $('.lk-btn-play i', prev)?.classList.replace('fa-pause','fa-play');
        if (prev) $('.lk-play i', prev)?.classList.replace('fa-pause','fa-play');
      }
      if (audio.paused) {
        audio.play().catch(() => {});
        current = audio;
        btnPlay?.querySelector('i')?.classList.replace('fa-play','fa-pause');
        coverBtn?.querySelector('i')?.classList.replace('fa-play','fa-pause');
      } else {
        audio.pause();
        btnPlay?.querySelector('i')?.classList.replace('fa-pause','fa-play');
        coverBtn?.querySelector('i')?.classList.replace('fa-pause','fa-play');
      }
    };

    btnPlay?.addEventListener('click', togglePlay);
    coverBtn?.addEventListener('click', togglePlay);

    btnMute?.addEventListener('click', () => {
      audio.muted = !audio.muted;
      const icon = btnMute.querySelector('i');
      if (audio.muted) { icon.classList.replace('fa-volume-high','fa-volume-xmark'); }
      else { icon.classList.replace('fa-volume-xmark','fa-volume-high'); }
    });

    audio?.addEventListener('timeupdate', () => {
      if (seek && !seek.dataset.lock) {
        seek.max   = audio.duration || seek.max || 0;
        seek.value = audio.currentTime || 0;
      }
      if (tCur) tCur.textContent = fmt(audio.currentTime);
      if (tTot && (audio.duration || 0) > 0) tTot.textContent = fmt(audio.duration);
    });

    seek?.addEventListener('input', () => {
      seek.dataset.lock = '1';
      if (!isNaN(seek.value)) audio.currentTime = +seek.value;
      if (tCur) tCur.textContent = fmt(seek.value);
    });
    seek?.addEventListener('change', () => { delete seek.dataset.lock; });

    audio?.addEventListener('ended', () => {
      btnPlay?.querySelector('i')?.classList.replace('fa-pause','fa-play');
      coverBtn?.querySelector('i')?.classList.replace('fa-pause','fa-play');
      if (seek) seek.value = 0;
      if (tCur) tCur.textContent = '0:00';
    });

    // Fallback sin fetch: submit del form para quitar like
    btnHeart?.addEventListener('click', (e) => {
      e.preventDefault();
      const form = $('.lk-like-form', tile);
      if (form) form.submit();
    });
  });
})();
</script>
>>>>>>> Parte-ubitzo
</body>
</html>
