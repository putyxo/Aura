<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $playlist->nombre }} — Playlist</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  @vite(['resources/css/playlist_card.css'])
  @vite(['resources/js/app.js'])

  @php
    use Illuminate\Support\Str;

    $isAuth   = auth()->check();
    $isOwner  = $isAuth && (int)auth()->id() === (int)($playlist->user_id ?? 0);
    $loginUrl = route('login');

    // Portada de la playlist
    $coverUrl = $playlist->cover_url ?? asset('img/default-album.png');

    // ==== URL base para quitar canciones (usa cancion=0 y luego se reemplaza) ====
    if (\Illuminate\Support\Facades\Route::has('playlists.remove-song')) {
      $removeBase = route('playlists.remove-song', ['playlist' => $playlist->id, 'cancion' => 0]);
    } else {
      // Fallback por si no existe el name (asegúrate de tener la ruta nombrada)
      $removeBase = url("/playlists/{$playlist->id}/remove-song/0");
    }
  @endphp
</head>
<body data-page="playlist-card">
<div id="page-playlist" data-auth="{{ $isAuth ? 1 : 0 }}" data-owner="{{ $isOwner ? 1 : 0 }}">

  @include('components.sidebar')
  @include('components.traductor')
  @include('components.header')
  @include('components.fondo')

  <main class="main-content pl-page">
    <!-- ===== HERO ===== -->
    <section class="pl-hero">
      <div class="pl-cover">
        <img src="{{ $coverUrl }}" alt="Portada de {{ $playlist->nombre }}">
      </div>
      <div class="pl-meta">
        <h1 class="pl-title">{{ $playlist->nombre }}</h1>
        <p class="pl-sub">{{ $playlist->descripcion ?? 'Sin descripción' }}</p>
        <span class="pl-update">Actualizado {{ $playlist->updated_at->diffForHumans() }}</span>
      </div>
    </section>

    <!-- ===== LISTA DE CANCIONES ===== -->
    <section class="pl-songs">
      <h2><i class="fa-solid fa-music"></i> Canciones</h2>

      @if($playlist->songs && $playlist->songs->count())
        <div class="songs-list">
          @foreach($playlist->songs as $song)
            @php
              $songTitle  = $song->title ?? $song->nombre ?? 'Sin título';
              $songArtist = $song->artista
                           ?? optional($song->user)->nombre_artistico
                           ?? optional($song->user)->nombre
                           ?? 'Artista';

              // Portada de la canción (fallbacks)
              $cover = $song->cover_url
                    ?? ($song->cover_path
                          ? (Str::startsWith($song->cover_path, ['http://','https://'])
                              ? $song->cover_path
                              : asset('storage/'.ltrim($song->cover_path,'/')))
                          : ($playlist->cover_url ?? asset('img/default-cancion.png')));

              // Audio de la canción (si existe)
              $audio = $song->audio_url
                    ?? ($song->audio_path
                          ? (Str::startsWith($song->audio_path, ['http://','https://','/storage/'])
                              ? $song->audio_path
                              : asset('storage/'.ltrim($song->audio_path,'/')))
                          : null);

              $dur = $song->duration ?? $song->duracion ?? '0:00';

              $likeAction = route('canciones.like', $song->id);

              // URL final "quitar de playlist" (reemplaza /0 por /{id})
              $removeUrl = str_replace('/0', '/'.$song->id, $removeBase);

              $isLiked = $isAuth && method_exists($song,'isLikedBy')
                ? $song->isLikedBy(auth()->user())
                : false;
            @endphp

            <div class="song-row"
                 data-id="{{ $song->id }}"
                 data-src="{{ $audio }}"
                 data-title="{{ $songTitle }}"
                 data-artist="{{ $songArtist }}"
                 data-cover="{{ $cover }}">
              <div class="song-index">{{ $loop->iteration }}</div>

              <div class="song-thumb">
                <img src="{{ $cover }}" alt="Portada" width="64" height="64" loading="lazy" decoding="async">
              </div>

              <div class="song-info">
                <h4 class="song-title" title="{{ $songTitle }}">{{ $songTitle }}</h4>
                <span class="song-artist">{{ $songArtist }}</span>
              </div>

              <div class="song-duration">{{ $dur }}</div>

              <div class="song-actions">
                @auth
                  <!-- Like -->
                  <form action="{{ $likeAction }}" method="POST"
                        class="inline-like"
                        data-song-id="{{ $song->id }}"
                        data-liked="{{ $isLiked ? 1 : 0 }}">
                    @csrf
                    <button type="button"
                            class="icon-chip like-btn {{ $isLiked ? 'is-liked':'' }}"
                            aria-pressed="{{ $isLiked ? 'true':'false' }}"
                            title="Me gusta">
                      <i class="fa-{{ $isLiked ? 'solid':'regular' }} fa-heart"></i>
                    </button>
                  </form>

                  <!-- Quitar de la playlist (solo dueño) -->
                  @if($isOwner)
                  <form action="{{ $removeUrl }}" method="POST" class="inline-remove" data-song-id="{{ $song->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="icon-chip remove-pl-btn danger" title="Quitar de la playlist">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </form>
                  @endif
                @else
                  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión para dar Me gusta">
                    <i class="fa-regular fa-heart"></i>
                  </a>
                @endauth

                <!-- Menú 3 puntos -->
                <div class="menu-wrap">
                  <button class="icon-chip more-btn" aria-haspopup="true" aria-expanded="false" title="Más">
                    <i class="fa-solid fa-ellipsis"></i>
                  </button>
                  <ul class="kebab-menu">
                    <li>
                      <button class="menu-item" data-action="queue">
                        <i class="fa-solid fa-list-ol"></i> Añadir a cola
                      </button>
                    </li>
                    <li>
                      <button class="menu-item" data-action="like">
                        <i class="fa-regular fa-heart"></i> Añadir a Me gusta
                      </button>
                    </li>
                    @if($isOwner)
                      <li class="divider"></li>
                      <li>
                        <button class="menu-item danger remove-from-pl"
                                data-action="{{ $removeUrl }}"
                                data-id="{{ $song->id }}"
                                data-title="{{ $songTitle }}">
                          <i class="fa-solid fa-xmark"></i> Quitar de la playlist
                        </button>
                      </li>
                    @endif
                  </ul>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="pl-empty">
          <i class="fa-solid fa-music"></i>
          <p>No hay canciones en esta playlist</p>
        </div>
      @endif
    </section>
  </main>

  @include('components.footer')
</div>

<!-- ===== JS de la vista (ligero y embebido) ===== -->
</body>
</html>
