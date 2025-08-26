<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>AURA — Favoritos</title>
  @vite('resources/css/follow_artist.css')
</head>
<body>
  <div class="with-sidebar">
    @include('components.sidebar')
        @include('components.header')
    <main class="main-content">
      
@if (!function_exists('drive_image_view'))
    @php
    function drive_image_view($url) {
        if (!$url) return null;

        // /file/d/FILE_ID/
        if (preg_match('~/d/([^/]+)~', $url, $m)) {
            return "https://drive.google.com/uc?export=view&id={$m[1]}";
        }

        // ?id=FILE_ID
        if (preg_match('~[?&]id=([^&]+)~', $url, $m)) {
            return "https://drive.google.com/uc?export=view&id={$m[1]}";
        }

        return $url;
    }
    @endphp
@endif
      <!-- Verifica si hay artistas seguidos -->
      @if($artistasSeguidos->isEmpty())
        <div class="empty-state">
          <div class="empty-box">
            <h2>Aún no se ha seguido ningun artista</h2>
          </div>
        </div>
      @else
        <div class="followed-artists">
          <h2>Artistas que sigues</h2>
          <div class="artists-grid">
            <!-- Itera sobre los artistas seguidos -->
            @foreach($artistasSeguidos as $artista)
              <div class="artist-card">
                <div class="card-banner" style="background-image: url('{{ drive_image_view($artista->banner_image ?? $artista->avatar) }}')">
                  <div class="card-overlay"></div>
                </div>
                <div class="card-content">
                  <div class="profile-container">
                    <img src="{{ drive_image_view($artista->avatar) }}" alt="{{ $artista->nombre_artistico }}" class="artist-profile">
                  </div>
                  <div class="artist-info">
                    <h3 class="artist-name">{{ $artista->nombre_artistico }}</h3>
                    <p class="artist-followers">{{ rand(1000, 1000000) }} seguidores</p>
                  </div>
                  <div class="card-actions">
                    <a href="{{ route('perfil.show', $artista->id) }}" class="profile-btn">Ver perfil</a>
                    <button class="follow-btn following">Siguiendo</button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </main>

    @include('components.footer')
  </div>
</body>
</html>
