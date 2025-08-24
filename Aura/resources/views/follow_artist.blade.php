<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>AURA — Favoritos</title>
  <link rel="stylesheet" href="{{ asset('css/follow_artist.css') }}">
</head>
<body>
  <div class="with-sidebar">
    @include('components.sidebar')

    <main class="main-content">
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
          <div class="grid">
            <!-- Itera sobre los artistas seguidos -->
            @foreach($artistasSeguidos as $artista)
              <div class="card">
                <div class="card-img">
                  <!-- Muestra el avatar del artista -->
                  <img src="{{ asset('storage/' . $artista->avatar) }}" alt="{{ $artista->nombre_artistico }}" class="card-avatar">
                </div>
                <h4>{{ $artista->nombre_artistico }}</h4>
                <a href="{{ route('perfil.show', $artista->id) }}" class="btn-primary">Ver perfil</a>
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
