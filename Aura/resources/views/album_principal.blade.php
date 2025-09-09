<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>AURA — Álbumes</title>

    @vite('resources/css/albums.css')
</head>
<body>
  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')


    <main class="main-content">
      <!-- Verifica si hay álbumes -->
      @if($albumes->isEmpty())
        <div class="empty-state">
          <div class="empty-box">
            <h2>No tienes álbumes todavía 📀</h2>
          </div>
        </div>
      @else
        <div class="followed-albums">
          <h2>Mis Álbumes</h2>
          <div class="grid">
            <!-- Itera sobre los álbumes -->
            @foreach($albumes as $album)
              <div class="card">
                <div class="card-img">
                  <!-- Portada del álbum -->
                  <img src="{{ $album->portada ? route('media.drive', ['id' => $album->portada]) : asset('img/default-album.png') }}" 
                       alt="{{ $album->title }}" 
                       class="card-cover">
                </div>
                <h4>{{ $album->title }}</h4>
                <p>Por {{ $album->user->nombre_artistico ?? $album->user->nombre }}</p>
                <a href="#" class="btn-primary">Ver álbum</a>
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
