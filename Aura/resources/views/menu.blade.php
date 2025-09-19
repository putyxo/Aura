<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>AURA</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @vite('resources/css/menu.css')
  @vite('resources/js/menu.js')

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Player fijo derecha + espacio de scroll -->
  <style>
    :root{ --player-width:380px; --player-gap:20px; }
    .main-content{ margin-right: calc(var(--player-width) + var(--player-gap)); padding-bottom: 140px; }
    .player-card{ position:fixed; top:0; right:0; bottom:0; width:var(--player-width); z-index:1300; }
    .header{ padding-right: calc(var(--player-width) + var(--player-gap)); }
    @media (max-width:860px){ :root{ --player-width:0px } .player-card{display:none} .main-content{margin-right:var(--player-gap)} }
  </style>

  @php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Str;

    /* ===== Helpers media (storage local) ===== */
    if (!function_exists('file_url')) {
      function file_url($path) {
        if (!$path) return null;
        return Str::startsWith($path, ['http://','https://','/storage/'])
          ? $path : asset('storage/'.ltrim($path, '/'));
      }
    }
    if (!function_exists('img_or_default')) {
      function img_or_default($path, $default){
        $u = file_url($path);
        return $u ?: asset($default);
      }
    }

    /* ===== DATA desde BD ===== */
    $artistas      = \App\Models\User::where('es_artista',1)->latest()->take(24)->get();
    $heroArtists   = $artistas->take(4);
    $railArtists   = $artistas->slice(4)->values();

    // 1 canción por álbum (al azar) + todas las singles
    $rawSongs = \App\Models\Cancion::with('user')->latest()->take(120)->get();
    $singles  = $rawSongs->filter(fn($s)=>empty($s->album_id));
    $byAlbum  = $rawSongs->filter(fn($s)=>!empty($s->album_id))->groupBy('album_id');
    $picked   = $byAlbum->map(fn($g)=>$g->random())->values();
    $ultimasCanciones = $singles->concat($picked)->sortByDesc('created_at')->take(30)->values();

    $ultimosAlbumes = \App\Models\Album::latest()->take(12)->get();

    /* ===== Rutas ===== */
    $routePerfilShow   = R::has('perfil.show')         ? 'perfil.show' : null;
    $routePerfilEdit   = R::has('perfil.edit')         ? 'perfil.edit' : null;
    $routeAlbumShow    = R::has('album.show')          ? 'album.show'  : null;
    $routeSongShow     = R::has('cancion.show')        ? 'cancion.show': null;
    $routeSongLike     = R::has('canciones.like')      ? 'canciones.like' : null;
    $routePLMine       = R::has('playlists.mine')      ? 'playlists.mine' : null;
    $routePLQuick      = R::has('playlists.quickStore')? 'playlists.quickStore' : null;
    $routePLAddSong    = R::has('playlists.add-song')  ? 'playlists.add-song' : null;

    $likesUrl      = R::has('me.likes')       ? route('me.likes')       : '#';
    $editUrl       = R::has('perfil.edit')    ? route('perfil.edit')    : '#';
    $playlistsUrl  = R::has('playlists.index')? route('playlists.index'): '#';

    $isAuth  = auth()->check();
    $loginUrl = R::has('login') ? route('login') : url('/login');
  @endphp
</head>

<body>
<div class="with-sidebar">
  @include('components.sidebar')
  @include('components.header')
  @include('components.traductor')
  @include('components.fondo')

  <main class="main-content">

    <!-- =============== ARTISTS SPOTLIGHT =============== -->
    <section class="spotlight">
      <div class="spotlight-head">
        <h3 class="spotlight-title">Artists Spotlight</h3>
        <div class="spotlight-accent"></div>
      </div>

      @if($heroArtists->isNotEmpty())
      <div class="spot-row">
        @foreach($heroArtists as $a)
          @php
            $rawBanner = $a->banner ?? $a->banner_path ?? null;
            $rawAvatar = $a->avatar ?? $a->avatar_path ?? null;
            $hasBanner = (bool) $rawBanner;
            $hasAvatar = (bool) $rawAvatar;

            $banner = img_or_default($rawBanner, 'img/default-banner.jpg');
            $avatar = img_or_default($rawAvatar, 'img/perfil_npc.png');
            $name   = $a->nombre_artistico ?? $a->name ?? 'Artista';
            $href   = $routePerfilShow ? route($routePerfilShow, $a->id) : '#';
            $isMine = $isAuth && auth()->id() === $a->id;
            $editH  = ($isMine && $routePerfilEdit) ? route($routePerfilEdit, $a->id) : null;
          @endphp
          <a href="{{ $href }}" class="spot-hero" style="--bg:url('{{ $banner }}')">
            <div class="hero-scrim"></div>

            @unless($hasBanner)
              <span class="missing-tag">Sin banner</span>
            @endunless

            <!-- La misma banda se anima al centro (suave) -->
            <div class="hero-bottom">
              <img class="hero-avatar" src="{{ $avatar }}" alt="{{ $name }}">
              <div class="hero-copy">
                <h4 class="hero-name" title="{{ $name }}">{{ $name }}</h4>
                @unless($hasAvatar)
                  <span class="missing-chip"><i class="fa-regular fa-image"></i> Sin foto</span>
                @endunless
                <div class="hero-actions">
                  <span class="chip"><i class="fa-solid fa-user"></i> Ver perfil</span>
                  @if($editH)
                    <a class="chip ghost" href="{{ $editH }}"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                  @endif
                </div>
              </div>
            </div>

            <div class="corner-tag">Nuevo</div>
          </a>
        @endforeach
      </div>
      @endif

      @if($railArtists->isNotEmpty())
      <div class="x-carousel artist-rail">
        <button class="x-btn prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="x-viewport">
          <div class="x-track">
            @foreach($railArtists as $a)
              @php
                $rawBanner = $a->banner ?? $a->banner_path ?? null;
                $rawAvatar = $a->avatar ?? $a->avatar_path ?? null;
                $hasBanner = (bool) $rawBanner;
                $hasAvatar = (bool) $rawAvatar;

                $banner = img_or_default($rawBanner, 'img/default-banner.jpg');
                $avatar = img_or_default($rawAvatar, 'img/perfil_npc.png');
                $name   = $a->nombre_artistico ?? $a->name ?? 'Artista';
                $href   = $routePerfilShow ? route($routePerfilShow, $a->id) : '#';
              @endphp
              <a class="x-card" href="{{ $href }}" style="background-image:url('{{ $banner }}')">
                <div class="x-glass"></div>
                <img class="x-avatar" src="{{ $avatar }}" alt="{{ $name }}">
                <div class="x-name" title="{{ $name }}">{{ $name }}</div>
                <div class="x-cta">Ver perfil</div>
                @unless($hasBanner)
                  <span class="small-ribbon">Sin banner</span>
                @endunless
              </a>
            @endforeach
          </div>
        </div>
        <button class="x-btn next" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
      @endif
    </section>

    <!-- =============== CONTENIDO PRINCIPAL =============== -->
    <section class="music-sections">
      <!-- 3 ACCIONES (izquierda) -->
      <div class="quick-ctas">
        <div class="section-head"><h2>Acciones rápidas</h2></div>

        <a href="{{ $likesUrl }}" class="cta-tile cta-like">
          <div class="cta-icon"><i class="fa-solid fa-heart"></i></div>
          <div class="cta-copy">
            <h4>Tus me gusta</h4>
            <p>Empieza a guardar canciones que te encanten.</p>
          </div>
          <span class="cta-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="{{ $playlistsUrl }}" class="cta-tile cta-playlist">
          <div class="cta-icon"><i class="fa-solid fa-circle-plus"></i></div>
          <div class="cta-copy">
            <h4>Crea tu playlist</h4>
            <p>Organiza tu música en listas personalizadas.</p>
          </div>
          <span class="cta-arrow"><i class="fa-solid fa-circle-plus"></i></span>
        </a>

        <a href="{{ $editUrl }}" class="cta-tile cta-profile">
          <div class="cta-icon"><i class="fa-solid fa-user-gear"></i></div>
          <div class="cta-copy">
            <h4>Edita tu cuenta</h4>
            <p>Ajusta tu perfil, banner y preferencias.</p>
          </div>
          <span class="cta-arrow"><i class="fa-solid fa-arrow-right"></i></span>
        </a>
      </div>

      <!-- SONGS OF THE WEEK (centro) -->
      <div class="tracks">
        <div class="tracks-head">
          <h2>Songs of the Week</h2>
          <div class="pager">
            <button class="page-btn prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
            <span class="page-indicator"><b>1</b> / <span class="page-total">1</span></span>
            <button class="page-btn next" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
          </div>
        </div>

        <ul id="latestSongs">
          @foreach($ultimasCanciones as $song)
            @php
              $title   = $song->title ?? $song->nombre ?? 'Sin título';
              $artist  = optional($song->user)->nombre_artistico ?? $song->artist_name ?? 'Artista';
              $cover   = img_or_default($song->cover_url ?? $song->cover ?? $song->cover_path ?? null, 'img/default-cancion.png');
              $audio   = file_url($song->audio_url ?? $song->audio ?? $song->audio_path ?? null);
              $dur     = $song->duration ?? $song->duracion ?? '';
              $songId  = $song->id;
              $href    = $routeSongShow ? route($routeSongShow, $songId) : '#';
            @endphp
            <li class="song-row cancion-item"
                data-id="{{ $songId }}"
                data-src="{{ $audio }}"
                data-title="{{ $title }}"
                data-artist="{{ $artist }}"
                data-cover="{{ $cover }}"
                data-duration="{{ $dur }}"
                role="button" tabindex="0">
              <a href="{{ $href }}" class="song-cover-link" aria-label="Abrir {{ $title }}">
                <img class="track-cover" src="{{ $cover }}" alt="Portada de {{ $title }}">
              </a>

              <div class="track-info" data-play>
                <span class="track-title" title="{{ $title }}">{{ $title }}</span>
                <div class="track-meta" title="{{ $artist }} · {{ $dur }}">
                  <span class="meta-artist">{{ $artist }}</span>
                  <span class="meta-dot">·</span>
                  <span class="meta-dur">{{ $dur }}</span>
                </div>
              </div>

              <div class="song-actions">
                @auth
                  @if($routeSongLike)
                    <button class="icon-chip like-btn" data-like="{{ route($routeSongLike, $songId) }}" title="Me gusta">
                      <i class="fa-regular fa-heart"></i>
                    </button>
                  @else
                    <button class="icon-chip" disabled title="Me gusta no disponible"><i class="fa-regular fa-heart"></i></button>
                  @endif

                  <button class="icon-chip add-queue-btn"  title="Añadir a cola"><i class="fa-solid fa-list-ol"></i></button>
                  <button class="icon-chip add-playlist-btn" title="Agregar a playlist"><i class="fa-solid fa-square-plus"></i></button>
                @else
                  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión"><i class="fa-regular fa-heart"></i></a>
                  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión"><i class="fa-solid fa-square-plus"></i></a>
                @endauth

                <div class="menu-wrap">
                  <button class="icon-chip more-btn" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-ellipsis"></i></button>
                  <ul class="kebab-menu">
                    <li><button class="menu-item" data-action="play"><i class="fa-solid fa-play"></i> Reproducir ahora</button></li>
                    <li><button class="menu-item" data-action="queue"><i class="fa-solid fa-list-ol"></i> Añadir a cola</button></li>
                    @auth
                      <li><button class="menu-item" data-action="playlist"><i class="fa-solid fa-square-plus"></i> Agregar a playlist</button></li>
                      <li><button class="menu-item" data-action="like"><i class="fa-regular fa-heart"></i> Me gusta</button></li>
                    @endauth
                  </ul>
                </div>
              </div>
            </li>
          @endforeach
        </ul>
      </div>

      <!-- ÁLBUMES SUGERIDOS GRANDES (derecha) -->
      <div class="suggested">
        <div class="section-head"><h2>Suggested album for you</h2></div>
        <div class="album-grid">
          @foreach($ultimosAlbumes as $al)
            @php
              $title = $al->titulo ?? $al->title ?? 'Sin título';
              $cover = img_or_default($al->cover_url ?? $al->cover ?? $al->cover_path ?? null, 'img/default-album.png');
              $href  = $routeAlbumShow ? route($routeAlbumShow, $al->id) : '#';
            @endphp
            <a class="album-card" href="{{ $href }}">
              <div class="album-art" style="background-image:url('{{ $cover }}')"></div>
              <p class="album-title" title="{{ $title }}">{{ $title }}</p>
            </a>
          @endforeach
        </div>
      </div>
    </section>

  </main>
</div>

@include('components.footer')
@stack('scripts')

<!-- Toast -->
<div id="toast" class="toast" aria-live="polite" aria-atomic="true"></div>

<!-- Endpoints playlists -->
<div id="playlist-endpoints"
     data-mine="{{ $routePLMine ? route($routePLMine) : '' }}"
     data-quick="{{ $routePLQuick ? route($routePLQuick) : '' }}"
     data-add-pattern="{{ $routePLAddSong ? route($routePLAddSong, ['playlist'=>'__PID__','cancion'=>'__SID__']) : '' }}">
</div>

<!-- Modal Playlists -->
<div id="plModal" class="pl-modal" aria-hidden="true">
  <div class="pl-dialog" role="dialog" aria-modal="true" aria-labelledby="plTitle">
    <div class="pl-head">
      <h3 id="plTitle"><i class="fa-solid fa-square-plus"></i> Agregar a playlist</h3>
      <button class="pl-close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="pl-body">
      <div class="pl-col">
        <h4>Mis playlists</h4>
        <div id="plList" class="pl-list"></div>
      </div>
      <div class="pl-col">
        <h4>Crear rápida</h4>
        <form id="plQuick" class="pl-form">
          <input name="title" type="text" placeholder="Nombre de la playlist" required>
          <button type="submit" class="pl-btn"><i class="fa-solid fa-plus"></i> Crear</button>
        </form>
        <small class="pl-hint">Se creará privada y se añadirá la canción seleccionada.</small>
      </div>
    </div>
  </div>
</div>
</body>
</html>
