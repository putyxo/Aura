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
    @include('components.traductor')

    <main class="main-content">

      <!-- Si no existe, define el helper de vista simple (queda por compatibilidad) -->
      @if (!function_exists('drive_image_view'))
        @php
        function drive_image_view($url) {
            if (!$url) return null;
            if (preg_match('~/d/([^/]+)~', $url, $m)) {
                return "https://drive.google.com/uc?export=view&id={$m[1]}";
            }
            if (preg_match('~[?&]id=([^&]+)~', $url, $m)) {
                return "https://drive.google.com/uc?export=view&id={$m[1]}";
            }
            return $url;
        }
        @endphp
      @endif

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
            @foreach($artistasSeguidos as $artista)
              @php
                // Usa el mismo patrón que en el perfil:
                // Banner: drive_img_url($user->banner, 1920) &v=time()
                // Avatar: drive_img_url($user->avatar, 500) &v=time()
                $bannerUrl = $artista->banner
                  ? drive_img_url($artista->banner, 1920) . '&v=' . time()
                  : asset('img/default-banner.jpg');

                $avatarUrl = $artista->avatar
                  ? drive_img_url($artista->avatar, 500) . '&v=' . time()
                  : asset('img/default-avatar.png');
              @endphp

              <div class="artist-card">
                <div class="card-banner" style="background-image: url('{{ $bannerUrl }}')">
                  <div class="card-overlay"></div>
                </div>

                <div class="card-content">
                  <div class="profile-container">
                    <img src="{{ $avatarUrl }}" alt="{{ $artista->nombre_artistico ?? 'Artista' }}" class="artist-profile">
                  </div>

                  <div class="artist-info">
                    <h3 class="artist-name">{{ $artista->nombre_artistico ?? 'Artista' }}</h3>
                    <p class="artist-followers">{{ $artista->seguidores ?? 0 }} seguidores</p>
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
