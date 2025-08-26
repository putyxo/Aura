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

      <div class="song-row">
        <div class="song-left">
          <img src="{{ $coverUrl }}" alt="cover">
          <div class="song-info">
            <h4>{{ $song->title }}</h4>
            <p>{{ $song->title }}</p>
          </div>
        </div>

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
</body>
</html>
