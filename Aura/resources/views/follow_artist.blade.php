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
    @include('components.fondo')
    <main class="main-content">

      @if($artistasSeguidos->isEmpty())
        <div class="empty-state">
          <div class="empty-box">
            <h2>Aún no se ha seguido ningún artista</h2>
          </div>
        </div>
      @else
        <div class="followed-artists">
          <h2>Artistas que sigues</h2>
          <div class="artists-grid">
            @foreach($artistasSeguidos as $artista)
              @php
                $bannerUrl = $artista->banner
                  ? asset(\Illuminate\Support\Facades\Storage::url($artista->banner))
                  : asset('img/default-banner.jpg');

                $avatarUrl = $artista->avatar
                  ? asset(\Illuminate\Support\Facades\Storage::url($artista->avatar))
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
