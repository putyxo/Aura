<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — {{ __('likes.title') }}</title>

  <!-- Fuente + Iconos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Vite CSS -->
  @vite(['resources/css/like.css'])
</head>
<body>
<div class="lk-app">
  <div class="lk-with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    @php
      use Illuminate\Support\Facades\Route as R;

      if (!isset($likedSongs) || is_null($likedSongs)) {
          $likedSongs = collect();
      } elseif (is_array($likedSongs)) {
          $likedSongs = collect($likedSongs);
      }

      // Helpers de rutas para "quitar de me gusta"
      $hasDestroy = R::has('likes.destroy');
      $hasUnlike  = R::has('likes.unlike');
      $hasToggle  = R::has('canciones.like'); // toggle JSON {liked:bool}
    @endphp

    <main class="main-content lk-page" data-page="likes">
      <div class="lk-shell">

        <!-- HERO -->
        <section class="lk-hero" aria-label="Tus Me gusta">
          <div class="lk-hero__bg"></div>
          <div class="lk-hero__row">
            <div class="lk-hero__content">
              <div class="lk-hero__icon"><i class="fa-solid fa-heart"></i></div>
              <div>
                <h1 class="lk-hero__title">{{ __('likes.title') }}</h1>
                <p class="lk-hero__sub">
                  <span id="lkCount">{{ number_format($likedSongs->count()) }}</span> {{ __('likes.subtitle') }}
                </p>
              </div>
            </div>

            <div class="lk-hero__actions">
              <div class="lk-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="lkSearch" type="search" placeholder="{{ __('likes.search_placeholder') }}" aria-label="{{ __('likes.search_placeholder') }}" autocomplete="off">
                <button class="lk-clear" id="lkClear" aria-label="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></button>
              </div>
            </div>
          </div>
        </section>

        @if(session('ok'))
          <div class="lk-toast lk-toast--ok" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif

        <!-- GRID -->
        <section class="lk-grid" id="lkGrid" data-count="{{ $likedSongs->count() }}">
          @forelse($likedSongs as $song)
            @php
              $title   = $song->title  ?? $song->titulo  ?? $song->name ?? 'Sin título';
              $artist  = optional($song->user)->nombre_artistico ?? optional($song->user)->nombre ?? 'Artista';

              // COVER (local/externo)
              $coverRaw = $song->cover_path ?? $song->portada ?? $song->imagen ?? null;
              $cover    = img_url($coverRaw, 'img/default-cover.jpg');

              // AUDIO (local/externo)
              $audioRaw = $song->audio_path ?? $song->ruta_audio ?? $song->file_url ?? null;
              $audio    = $audioRaw ? audio_url($audioRaw) : '';

              $durSec    = is_numeric($song->duration ?? $song->duracion ?? null) ? (int)($song->duration ?? $song->duracion) : 0;
              $durText   = $durSec ? gmdate('i:s', max(0, $durSec)) : '--:--';
              $searchKey = mb_strtolower(($title ?? '') . ' ' . $artist, 'UTF-8');

              // Resolver URL y método para "quitar de me gusta"
              if ($hasDestroy) {
                $unlikeUrl = route('likes.destroy', $song->id);
                $unlikeMethod = 'DELETE';
              } elseif ($hasUnlike) {
                $unlikeUrl = route('likes.unlike', $song->id);
                $unlikeMethod = 'POST';
              } elseif ($hasToggle) {
                $unlikeUrl = route('canciones.like', $song->id);
                $unlikeMethod = 'POST';
              } else {
                $unlikeUrl = url('likes/'.$song->id);
                $unlikeMethod = 'DELETE';
              }
            @endphp

            <article
              class="lk-tile"
              title="{{ $title }}"
              data-name="{{ $searchKey }}"
              data-duration="{{ $durSec }}"
              data-song-id="{{ $song->id }}"
              data-title="{{ $title }}"
              data-artist="{{ $artist }}"
              data-cover="{{ $cover }}"
              data-audio="{{ $audio }}"
              data-unlike-url="{{ $unlikeUrl }}"
              data-unlike-method="{{ $unlikeMethod }}"
            >
              <a class="lk-tile__link" aria-label="Abrir {{ $title }}"></a>

              <div class="lk-cover">
                <img src="{{ $cover }}" alt="Portada {{ $title }}" width="260" height="260" loading="lazy" decoding="async"
                     onerror="this.onerror=null;this.src='{{ asset('img/default-cover.jpg') }}';">
                <button type="button" class="lk-play" aria-label="Reproducir {{ $title }}">
                  <i class="fa-solid fa-play"></i>
                </button>
                <button type="button" class="lk-like is-liked" title="Quitar de Me gusta" aria-label="Quitar de Me gusta" data-unlike="{{ $song->id }}">
                  <i class="fa-solid fa-heart"></i>
                </button>

                <!-- 3 puntitos -->
                <button type="button" class="lk-keb" aria-haspopup="menu" aria-expanded="false" title="Más opciones">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="lk-menu" role="menu">
                  <button type="button" data-act="queue"><i class="fa-solid fa-list"></i> {{ __('likes.add_to_queue') }}</button>
                  <button type="button" data-act="playlist" id="add-to-playlist" data-song-id="{{ $song->id }}"><i class="fa-solid fa-plus"></i> {{ __('likes.add_to_playlist') }}</button>
                  <div class="sep" aria-hidden="true"></div>
                  <button type="button" data-act="unlike" class="danger"><i class="fa-solid fa-heart-crack"></i> {{ __('likes.remove_from_favorites') }}</button>
                </div>
              </div>

              <div class="lk-meta">
                <div class="lk-title-row">
                  <div class="lk-title" title="{{ $title }}">{{ $title }}</div>
                  <span class="lk-duration">{{ $durText }}</span>
                </div>
                <div class="lk-artist">{{ $artist }}</div>
              </div>

              <!-- Fallback sin JS -->
              <form class="lk-like-form" method="POST" action="{{ $unlikeUrl }}">
                @csrf
                @if($unlikeMethod === 'DELETE')
                  @method('DELETE')
                @endif
              </form>
            </article>
          @empty
              <div class="lk-empty" style="grid-column:1/-1;">
                <i class="fa-solid fa-heart-crack"></i>
                <h3>{{ __('likes.empty_title') }}</h3>
                <p>{{ __('likes.empty_text') }} <i class="fa-solid fa-heart"></i> {{ __('likes.empty_text') }}</p>
              </div>
          @endforelse
        </section>

      </div>
    </main>

    @include('components.footer')
  </div>
</div>

<!-- ======= MODAL PARA AÑADIR A PLAYLIST ======= -->
@include('components.playlistModal') <!-- Aquí se incluye el modal -->

<script>
(() => {
  // Aquí agregas la lógica para abrir el modal de playlists cuando se hace clic en el botón correspondiente
  const openModalBtn = document.getElementById('add-to-playlist');
  const modal = document.getElementById('playlistModal');

  openModalBtn?.addEventListener('click', () => {
    modal.hidden = false;
    document.body.style.overflow = 'hidden'; // Bloquea el fondo
    modal.classList.add('is-open');
    // Cargar playlists si es necesario
  });

  // Funcionalidad para cerrar el modal
  const closeModalBtns = document.querySelectorAll('[data-close="1"]');
  closeModalBtns.forEach(btn => btn.addEventListener('click', () => {
    modal.classList.remove('is-open');
    modal.hidden = true;
    document.body.style.overflow = '';
  }));
})();
</script>

</body>
</html>
