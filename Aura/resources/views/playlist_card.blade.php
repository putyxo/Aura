<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Playlist Card</title>
  @vite('resources/css/playlist_card.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  @include('components.footer')

  <div class="with-sidebar">
    @include('components.sidebar')

    <main class="main-content">

   {{-- ====== CARD DE LA PLAYLIST ====== --}}
      <div class="playlist-card">
        <div class="cover">
          <img src="{{ $playlist->cover_url ?? 'https://via.placeholder.com/250x250.png?text=Cover' }}" 
               alt="Portada Playlist">
        </div>

        <div class="info">
          <span class="subtitle">Playlist pública</span>
          <h2 class="title">{{ $playlist->nombre }}</h2>
          <span class="update">Actualizado {{ $playlist->updated_at->diffForHumans() }}</span>
          <p class="description">{{ $playlist->descripcion ?? '(Sin descripción)' }}</p>
          <div class="actions">
            <button class="btn play"><i class="fa-solid fa-play"></i> Reproducir</button>
            <button class="btn shuffle"><i class="fa-solid fa-shuffle"></i> Aleatorio</button>
          </div>
        </div>
      </div>

      {{-- ====== LISTA DE CANCIONES ====== --}}
      <section class="playlist-songs">
        <div class="songs-header">
          <input type="text" class="song-search" placeholder="Buscar canción...">
        </div>

        <div class="songs-list">
          @if($playlist->songs && $playlist->songs->count())
            <ul>
              @foreach($playlist->songs as $song)
                <li class="song-item">
                  <span class="song-title">{{ $song->titulo }}</span>
                  <span class="song-artist">{{ $song->artista }}</span>
                  <button class="song-play"><i class="fa-solid fa-play"></i></button>
                </li>
              @endforeach
            </ul>
          @else
            <p class="empty-msg">🎵 Aún no tienes canciones en esta playlist.</p>
          @endif
        </div>
      </section>
    </main>
  </div>

  <script>
  // Escuchar clicks en los tiles de playlist
  document.querySelectorAll('.tile').forEach(tile => {
    tile.addEventListener('click', () => {
      const nombre = tile.dataset.nombre;
      const cover  = tile.dataset.cover;
      const desc   = tile.dataset.desc;
      const updated= tile.dataset.updated ?? 'ACTUALIZADO RECIENTEMENTE';
      const sub    = tile.dataset.subtitle ?? '';

      // Insertar en el card
      document.getElementById('detailTitle').textContent = nombre;
      document.getElementById('detailSubtitle').textContent = sub;
      document.getElementById('detailUpdate').textContent = updated;
      document.getElementById('detailDescription').textContent = desc || '(Sin descripción)';
      document.getElementById('detailCover').src = cover || 'https://via.placeholder.com/250x250.png?text=Cover';

      // Mostrar card
      document.getElementById('playlistDetail').hidden = false;
    });
  });
  </script>
</body>
</html>
