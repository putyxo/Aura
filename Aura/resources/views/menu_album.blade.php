<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Album Card</title>
  @vite('resources/css/menu_album.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  @yield('content')
  @include('components.traductor')
  @include('components.footer')

  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')

    <main class="main-content">

      {{-- ====== CARD DEL ÁLBUM ====== --}}
      <div class="album-card">
        <!-- Botón Editar en la esquina -->
        <button class="edit-btn"><i class="fa-solid fa-pen"></i> Editar</button>

        <div class="cover traducible">
          <img src="{{ $album->cover_path ? Storage::url($album->cover_path) : 'https://via.placeholder.com/250x250.png?text=Cover' }}" 
               alt="Portada Álbum">
        </div>

        <div class="info traducible">
          <span class="subtitle traducible">Álbum</span>
          <h2 class="title">{{ $album->title }}</h2>
          <span class="update">Lanzado en {{ $album->release_date ?? 'N/A' }}</span>
          <p class="description traducible">{{ $album->genre ?? '(Sin género)' }}</p>

          <div class="actions">
            <button class="btn play traducible"><i class="fa-solid fa-play"></i> Reproducir</button>
            <button class="btn shuffle traducible"><i class="fa-solid fa-shuffle"></i> Aleatorio</button>
          </div>
        </div>
      </div>

      {{-- ====== LISTA DE CANCIONES ====== --}}
      <section class="album-songs">
        <h3 class="songs-header"><i class="fa-solid fa-music"></i> Canciones de este álbum</h3>

        <div class="songs-list">
          @if($album->songs && $album->songs->count())
            <ul>
              @foreach($album->songs as $song)
                <li class="song-item">
                  <div class="song-info">
                    <span class="song-title">{{ $song->title }}</span>
                    <span class="song-artist">{{ $song->artist ?? 'Artista desconocido' }}</span>
                  </div>
                  <button class="song-play"><i class="fa-solid fa-play"></i></button>
                </li>
              @endforeach
            </ul>
          @else
            <p class="empty-msg traducible"><i class="fa-solid fa-circle-exclamation"></i> Este álbum aún no tiene canciones.</p>
          @endif
        </div>
      </section>
    </main>
  </div>
</body>
</html>
