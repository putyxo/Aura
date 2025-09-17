{{-- resources/views/ed_perfil.blade.php --}}
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('ed_perfil.title', ['name' => $user->nombre_artistico ?? 'Invitado']) }}</title>

  <!-- Hints de red -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">

  <!-- Font Awesome no-bloqueante -->
  <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

  {{-- CSS + JS principal --}}
  @vite(['resources/css/ed_perfil.css','resources/js/ed_perfil.js'])

  @php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Str;

    $verUser     = optional($user->updated_at)->timestamp ?? 0;

    $bannerSrc   = $user->banner_url ?? asset('img/default-banner.jpg');
    $bannerLow   = $bannerSrc.'?v='.$verUser;
    $bannerHigh  = $bannerSrc.'?v='.$verUser;

    $followersCount = method_exists($user,'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0);
    $listenersCount = (int)($user->oyentes_mensuales ?? 0);

    if (!function_exists('num_format_sp')) {
      function num_format_sp($n){ return is_numeric($n) ? number_format((float)$n,0,',','.'): $n; }
    }

    $bio = trim($user->biografia ?? '') ?: 'Sin biografía por ahora.';

    $cuentaUrl =
      (R::has('cuenta.index')     ? route('cuenta.index')     :
      (R::has('cuenta')           ? route('cuenta')           :
      (R::has('settings.account') ? route('settings.account') :
      (R::has('account.index')    ? route('account.index')    :
       url('/cuenta')))));

    $loginUrl = (R::has('login') ? route('login') : url('/login'));

    $isAuth  = auth()->check();
    $isOwner = $isAuth && auth()->id() === $user->id;

    // Preload de 1-2 imágenes críticas
    $firstSong = $canciones->first();
    $preSongCover = null;
    if ($firstSong) {
      $tmpCover = $firstSong->cover_url
        ?? ((isset($firstSong->cover) || isset($firstSong->cover_path))
              ? (Str::startsWith(($firstSong->cover ?? $firstSong->cover_path), ['http://','https://'])
                  ? ($firstSong->cover ?? $firstSong->cover_path)
                  : asset('storage/'.ltrim(($firstSong->cover ?? $firstSong->cover_path),'/')))
              : asset('img/default-cancion.png'));
      $preSongCover = $tmpCover.'?v='.(optional($firstSong->updated_at)->timestamp ?? 0);
    }

    $firstAlbum = $albumes->first();
    $preAlbumCover = $firstAlbum
      ? (($firstAlbum->cover_url ?? null) ? ($firstAlbum->cover_url.'?v='.(optional($firstAlbum->updated_at)->timestamp ?? 0)) : null)
      : null;
  @endphp

  <link rel="preload" as="image" href="{{ $bannerLow }}">
  @if($preSongCover)<link rel="preload" as="image" href="{{ $preSongCover }}">@endif
  @if($preAlbumCover)<link rel="preload" as="image" href="{{ $preAlbumCover }}">@endif
</head>
<body>
<div id="page-profile" data-auth="{{ $isAuth ? 1 : 0 }}" data-owner="{{ $isOwner ? 1 : 0 }}">

  @include('components.sidebar')
  @include('components.traductor')
  @include('components.header')
  @include('components.fondo')

  <main class="main-content">

    <!-- ===== HERO PERFIL ===== -->
    <section class="profile-hero a-reveal">
      <div class="hero-wrap">
        <div class="profile-banner" data-hires="{{ $bannerHigh }}" style="background-image:url('{{ $bannerLow }}')"></div>
        <div class="hero-scrim" aria-hidden="true"></div>
        <div class="hero-border" aria-hidden="true"></div>

        <button type="button" class="pf-btn pf-btn--ghost pf-btn--sm info-btn" id="openBio" title="Ver biografía">
          <i class="fa-solid fa-circle-info" aria-hidden="true"></i><span class="sr-only">Ver biografía</span>
        </button>

        @if($isOwner)
        <div class="edit-btn">
          <button class="pf-btn pf-btn--primary pf-btn--sm" id="editBtn">
            <i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span>
          </button>
        </div>
        @endif

        <div class="hero-head">
          <div class="text-shield">
            <h1 class="hero-title" id="artistName">{{ $user->nombre_artistico ?? 'Artista' }}</h1>
          </div>

          <div class="follow-under-name">
            @if($isOwner)
            @elseif($isAuth)
              @php
                $hasToggle    = R::has('follow.toggle');
                $hasFollow    = R::has('perfil.follow');
                $hasUnfollow  = R::has('perfil.unfollow');
                $isFollowing  = method_exists(auth()->user(), 'isFollowing') ? auth()->user()->isFollowing($user->id) : false;
              @endphp
              @if($hasToggle)
                <form action="{{ route('follow.toggle', $user->id) }}" method="POST">@csrf
                  <button type="submit" class="pf-btn follow-pill">
                    <i class="fa-solid {{ $isFollowing ? 'fa-user-minus' : 'fa-user-plus' }}"></i>
                    {{ $isFollowing ? 'Dejar de seguir' : 'Seguir' }}
                  </button>
                </form>
              @elseif(($isFollowing && $hasUnfollow) || (!$isFollowing && $hasFollow))
                <form action="{{ $isFollowing ? route('perfil.unfollow', $user->id) : route('perfil.follow', $user->id) }}" method="POST">@csrf
                  <button type="submit" class="pf-btn follow-pill">
                    <i class="fa-solid {{ $isFollowing ? 'fa-user-minus' : 'fa-user-plus' }}"></i>
                    {{ $isFollowing ? 'Dejar de seguir' : 'Seguir' }}
                  </button>
                </form>
              @else
                <a href="{{ $loginUrl }}" class="pf-btn follow-pill"><i class="fa-solid fa-user-plus"></i> Seguir</a>
              @endif
            @else
              <a href="{{ $loginUrl }}" class="pf-btn follow-pill"><i class="fa-solid fa-user-plus"></i> Seguir</a>
            @endif
          </div>
        </div>

        <div class="profile-footer">
          <span class="meta"><i class="fa-solid fa-headphones" aria-hidden="true"></i> {{ num_format_sp($listenersCount) }} oyentes mensuales</span>
          <span class="meta"><i class="fa-solid fa-user-group" aria-hidden="true"></i> {{ num_format_sp($followersCount) }} seguidores</span>
        </div>

        <div class="avatar-wrap xl">
          @if($user && $user->avatar)
            <img id="avatarPreviewLive" class="avatar-img"
                 src="{{ $user->avatar_url }}?v={{ $verUser }}"
                 alt="{{ $user->nombre_artistico ?? $user->nombre }}"
                 width="160" height="160"
                 loading="eager" fetchpriority="high" decoding="async">
          @else
            <div class="avatar-fallback">{{ strtoupper(substr($user->nombre_artistico ?? $user->nombre ?? 'U',0,1)) }}</div>
          @endif
        </div>
      </div>
    </section>

    <!-- ===== CONTENIDO MÚSICA ===== -->
    <section class="music-layout no-clip">
      <!-- CANCIONES -->
      <div class="music-column left-col no-clip">
        <div class="section-title"><h2><i class="fa-solid fa-music" aria-hidden="true"></i> Canciones</h2></div>

        <div class="songs-list fixed-six" id="songsList">
          @foreach($canciones->take(6) as $song)
            @php
              $songTitle    = $song->title ?? $song->nombre ?? 'Sin título';
              $songVer      = optional($song->updated_at)->timestamp ?? 0;

              // COVER
              $tmpCover = $song->cover_url
                ?? ((isset($song->cover) || isset($song->cover_path))
                      ? (Str::startsWith(($song->cover ?? $song->cover_path), ['http://','https://'])
                          ? ($song->cover ?? $song->cover_path)
                          : asset('storage/'.ltrim(($song->cover ?? $song->cover_path),'/')))
                      : asset('img/default-cancion.png'));
              $coverUrl  = $tmpCover.'?v='.$songVer;

              // AUDIO
              $audioPath = $song->audio_path ?? $song->audio ?? null;
              $audioUrl  = $song->audio_url
                ?? ($audioPath
                      ? (Str::startsWith($audioPath, ['http://','https://','/storage/'])
                          ? $audioPath
                          : asset('storage/'.ltrim($audioPath,'/')))
                      : null);

              $dur = $song->duration ?? $song->duracion ?? '0:00';

              $likeAction = R::has('canciones.like')
                  ? route('canciones.like', $song->id)
                  : (R::has('cancion.like') ? route('cancion.like', $song->id) : null);

              // ✅ solo construir ruta si hay id
              $songDelete = (R::has('cancion.destroy') && !empty($song->id))
                  ? route('cancion.destroy', ['cancion' => $song->id])
                  : '#';

              $isLiked = $isAuth && method_exists($song,'isLikedBy') ? $song->isLikedBy(auth()->user()) : false;
            @endphp

            <div
              class="song-row"
              data-id="{{ $song->id }}"
              data-src="{{ $audioUrl }}"
              data-title="{{ $songTitle }}"
              data-artist="{{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}"
              data-cover="{{ $coverUrl }}"
              role="button" tabindex="0" aria-label="Reproducir {{ $songTitle }}"
            >
              <div class="song-index">{{ $loop->iteration }}</div>

              <div class="song-thumb">
                <img src="{{ $coverUrl }}" alt="Portada"
                     width="64" height="64"
                     loading="lazy" decoding="async">
              </div>

              <div class="song-info">
                <h4 class="song-title" title="{{ $songTitle }}">{{ $songTitle }}</h4>
                <div class="song-sub"><span class="artist-name">{{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</span></div>
              </div>

              <div class="song-duration">{{ $dur }}</div>

              <div class="song-actions">
<<<<<<< HEAD
@auth
  <form action="{{ route('canciones.like', $song->id) }}" method="POST" class="inline-like" data-song-id="{{ $song->id }}">
    @csrf
    <button type="submit" class="icon-chip like-btn" aria-pressed="false" title="Me gusta">
      <i class="fa-regular fa-heart"></i>
    </button>
  </form>
@else
  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión para dar Me gusta"><i class="fa-regular fa-heart"></i></a>
  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión para usar playlists"><i class="fa-solid fa-plus"></i></a>
@endauth


=======
                @auth
                  @if($likeAction)
                    <form action="{{ $likeAction }}" method="POST"
                          class="inline-like"
                          data-song-id="{{ $song->id }}"
                          data-liked="{{ $isLiked ? 1 : 0 }}"
                          data-turbo="false">
                      @csrf
                      <button type="button"
                              class="icon-chip like-btn {{ $isLiked ? 'is-liked' : '' }}"
                              aria-pressed="{{ $isLiked ? 'true' : 'false' }}"
                              title="Me gusta">
                        <i class="fa-{{ $isLiked ? 'solid' : 'regular' }} fa-heart" aria-hidden="true"></i>
                        <span class="sr-only">Me gusta</span>
                      </button>
                    </form>
                  @else
                    <button type="button" class="icon-chip like-btn" title="Me gusta no disponible" disabled>
                      <i class="fa-regular fa-heart" aria-hidden="true"></i>
                    </button>
                  @endif

                  <button class="icon-chip add-playlist-btn" title="Agregar a playlist">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                  </button>
                @else
                  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión para dar Me gusta"><i class="fa-regular fa-heart"></i></a>
                  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión para usar playlists"><i class="fa-solid fa-plus"></i></a>
                @endauth
>>>>>>> recup-ayer

                <div class="menu-wrap">
                  <button class="icon-chip more-btn" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-ellipsis"></i></button>
                  <ul class="kebab-menu">
                    @auth
                      <li><button class="menu-item" data-action="queue"><i class="fa-solid fa-list-ol"></i> Añadir a cola</button></li>
                      <li><button class="menu-item" data-action="playlist"><i class="fa-solid fa-square-plus"></i> Agregar a playlist</button></li>
                      <li><button class="menu-item" data-action="like"><i class="fa-regular fa-heart"></i> Añadir a Me gusta</button></li>
                      @if($isOwner && $songDelete !== '#')
                        <li class="divider"></li>
                        <li>
                          <button class="menu-item danger open-delete"
                                  data-type="song"
                                  data-action="{{ $songDelete }}"
                                  data-title="{{ $songTitle }}"
                                  data-cover="{{ $coverUrl }}">
                            <i class="fa-solid fa-trash"></i> Eliminar
                          </button>
                        </li>
                      @endif
                    @else
                      <li><a class="menu-item" href="{{ $loginUrl }}"><i class="fa-solid fa-right-to-bracket"></i> Inicia sesión para más opciones</a></li>
                    @endauth
                  </ul>
                </div>
              </div>

              @if($isOwner && $songDelete !== '#')
                <!-- Basurero flotante para canciones -->
                <button class="trash-float open-delete"
                        title="Eliminar canción"
                        data-type="song"
                        data-action="{{ $songDelete }}"
                        data-title="{{ $songTitle }}"
                        data-cover="{{ $coverUrl }}">
                  <i class="fa-solid fa-trash"></i>
                </button>
              @endif
            </div>
          @endforeach
        </div>
      </div>

      <!-- ÁLBUMES 2×2 -->
      <div class="music-column right-col no-clip">
        <div class="albums-head">
          <div class="section-title"><h2><i class="fa-solid fa-compact-disc" aria-hidden="true"></i> Álbumes</h2></div>
          <div class="page-label" id="albumsPageLabel"></div>
        </div>

        @php
          $albumsNormalized = $albumes->map(function($a){
            return (object)[
              'id'        => $a->id,
              'titulo'    => $a->titulo ?: ($a->title ?? 'Sin título'),
              'cover_url' => $a->cover_url ?? null,
              'ver'       => optional($a->updated_at)->timestamp ?? 0,
            ];
          });
          $albumPages = $albumsNormalized->chunk(4);
        @endphp

        <div class="albums-wrap no-clip">
          <button class="albums-arrow left" id="albumsPrev" aria-label="Anterior">
            <i class="fa-solid fa-chevron-left"></i>
          </button>

          <div class="albums-viewport" id="albumsViewport">
            <div class="albums-track" id="albumsTrack" data-pages="{{ $albumPages->count() }}">
              @foreach($albumPages as $page)
                <div class="albums-page">
                  <div class="albums-grid-2x2">
                    @foreach($page as $album)
                      @php
                        $albumCoverBase = $album->cover_url ?: asset('img/default-album.png');
                        $albumCover     = $albumCoverBase.'?v='.$album->ver;

                        // ⬇️ CAMBIO: al hacer click -> menu_album?album={id}
                        $albumHref = !empty($album->id)
                          ? route('menu_album', ['album' => $album->id])
                          : '#';

                        // ruta de borrado (sin cambios)
                        $albumDelete = (R::has('profile.albums.destroy') && !empty($album->id))
                          ? route('profile.albums.destroy', ['id' => $album->id])
                          : '#';
                      @endphp

                      <div class="card album-card {{ $isOwner ? 'has-trash' : '' }}">
<<<<<<< HEAD
                        <a href="{{ route('album.show', $album->id) }}" class="card-link">
  <div class="card-img">
    <img src="{{ $albumCover }}" alt="Portada" loading="lazy" decoding="async">
    <span class="album-play"><i class="fa-solid fa-play"></i></span>
  </div>
  <h4 class="album-title">{{ $album->titulo }}</h4>
  <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre }}</p>
</a>
=======
                        <a href="{{ $albumHref }}" class="card-link" data-prefetch="true">
                          <div class="card-img">
                            <img src="{{ $albumCover }}" alt="Portada"
                                 width="300" height="300"
                                 loading="lazy" decoding="async">
                            <span class="album-play"><i class="fa-solid fa-play"></i></span>
                          </div>
                          <h4 class="album-title">{{ $album->titulo }}</h4>
                          <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre }}</p>
                        </a>
>>>>>>> recup-ayer

                        @if($isOwner && $albumDelete !== '#')
                          <button class="trash-float open-delete"
                                  title="Eliminar álbum"
                                  data-type="album"
                                  data-action="{{ $albumDelete }}"
                                  data-title="{{ $album->titulo }}"
                                  data-cover="{{ $albumCover }}">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        @endif
                      </div>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <button class="albums-arrow right" id="albumsNext" aria-label="Siguiente">
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- ===== ÚLTIMOS LANZAMIENTOS ===== -->
    @php
      $releasesAllUrl = R::has('perfil.releasesAll')
          ? route('perfil.releasesAll', $user->id)
          : url('/perfil/'.$user->id.'/lanzamientos');

      $normalizedReleases = collect($lanzamientos)->map(function($it){
        $tipo   = $it['tipo']   ?? $it->tipo   ?? 'album';
        $titulo = $it['titulo'] ?? $it->titulo ?? $it->title ?? null;

        $cover     = $it['cover']  ?? $it->cover  ?? $it->portada ?? $it->cover_path ?? null;
        $coverUrl  = $it['cover_url'] ?? ($it->cover_url ?? null);

        $audioPath = $it['audio'] ?? $it->audio ?? $it->audio_path ?? null;
        $audioUrl  = $it['audio_url'] ?? (
                      $audioPath
                        ? (Str::startsWith($audioPath, ['http://','https://','/storage/'])
                            ? $audioPath
                            : asset('storage/'.ltrim($audioPath,'/')))
                        : null
                    );

        $anio   = $it['anio']   ?? $it->anio   ?? (isset($it->created_at) ? optional($it->created_at)->format('Y') : '');
        $ver    = optional($it['updated_at'] ?? ($it->updated_at ?? null))->timestamp ?? 0;
        $titulo = $titulo ?: 'Sin título';

        return [
          'id'         => $it['id'] ?? ($it->id ?? null),
          'tipo'       => $tipo,
          'titulo'     => $titulo,
          'cover'      => $cover,
          'cover_url'  => $coverUrl,
          'anio'       => $anio,
          'ver'        => $ver,
          'audio_url'  => $audioUrl,
        ];
      })
      ->filter(fn($x) => !empty($x['titulo']))
      ->values();

      $relPages = $normalizedReleases->chunk(8);
    @endphp

    <section class="releases-section no-clip" id="releasesSection">
      <div class="releases-head">
        <h2><i class="fa-solid fa-bolt" aria-hidden="true"></i> Últimos lanzamientos</h2>
        <a href="{{ $releasesAllUrl }}" class="pf-link">Ver todo</a>
      </div>

      <div class="releases-wrap no-clip">
        <button class="releases-arrow left" id="releasesPrev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="releases-viewport">
          <div class="releases-track" id="releasesTrack" data-pages="{{ $relPages->count() }}">
            @foreach($relPages as $rPage)
              <div class="releases-page">
                <div class="releases-grid-4x2">
                  @foreach($rPage as $item)
                    @php
                      if (!empty($item['cover_url'])) {
                        $rCoverBase = $item['cover_url'];
                      } elseif (!empty($item['cover'])) {
                        $rCoverBase = Str::startsWith($item['cover'], ['http://','https://'])
                          ? $item['cover']
                          : asset('storage/' . ltrim($item['cover'], '/'));
                      } else {
                        $rCoverBase = asset('img/default-album.png');
                      }
                      $rCover = $rCoverBase.'?v='.$item['ver'];

                      $isSong = ($item['tipo'] !== 'album');

                      // ⬇️ CAMBIO: abrir menu_album?album={id} para álbumes
                      if (!$isSong) {
                        $relHref = (!empty($item['id']))
                          ? route('menu_album', ['album' => $item['id']])
                          : null;

                        $relDelete = (!empty($item['id']) && R::has('profile.albums.destroy'))
                          ? route('profile.albums.destroy', ['id' => $item['id']])
                          : '#';
                      } else {
                        $relHref = null;
                        $relDelete = (!empty($item['id']) && R::has('cancion.destroy'))
                          ? route('cancion.destroy', ['cancion' => $item['id']])
                          : '#';
                      }
                    @endphp

                    <div class="card release-card{{ $isOwner ? ' has-trash' : '' }}" data-type="{{ $isSong ? 'song' : 'album' }}" data-id="{{ $item['id'] ?? '' }}">
                      <div class="card-img">
                        <img src="{{ $rCover }}" alt="Portada"
                             width="240" height="240"
                             loading="lazy" decoding="async">
                        <span class="type-badge">{{ $isSong ? 'Canción' : 'Álbum' }}</span>
                      </div>
                      <h4 title="{{ $item['titulo'] }}">{{ $item['titulo'] }}</h4>
                      <p>{{ $item['anio'] }}</p>

                      @if($isOwner && $relDelete !== '#')
                        <button class="trash-float open-delete"
                                title="Eliminar"
                                data-type="{{ $isSong ? 'song' : 'album' }}"
                                data-action="{{ $relDelete }}"
                                data-title="{{ $item['titulo'] }}"
                                data-cover="{{ $rCover }}">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      @endif

                      @if(!$isSong && !empty($relHref))
                        <a class="card-link" href="{{ $relHref }}" aria-label="Abrir álbum" data-prefetch="true"></a>
                      @else
                        <button type="button"
                                class="card-link play-release"
                                aria-label="Reproducir"
                                data-id="{{ $item['id'] ?? '' }}"
                                data-src="{{ $item['audio_url'] ?? '' }}"
                                data-title="{{ $item['titulo'] }}"
                                data-artist="{{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}"
                                data-cover="{{ $rCover }}"></button>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>
        <button class="releases-arrow right" id="releasesNext" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
      </div>

      <div class="releases-pager" id="releasesPager"></div>
    </section>

    <!-- ===== MODAL CONFIRMAR ELIMINAR ===== -->
    <div class="confirm-modal" id="confirmModal" aria-hidden="true">
      <div class="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <div class="confirm-media"><img id="confirmCover" alt="" width="240" height="240" loading="lazy" decoding="async"></div>
        <div class="confirm-copy">
          <h3 id="confirmTitle">¿Eliminar?</h3>
          <p id="confirmSubtitle" class="confirm-sub"></p>
          <div class="confirm-warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Esta acción es permanente y no se puede deshacer.
          </div>
          <div class="confirm-actions">
            <form id="deleteForm" action="#" method="POST">@csrf @method('DELETE')
              <button id="confirmDeleteBtn" type="submit" class="pf-btn pf-btn--danger">
                <i class="fa-solid fa-trash"></i> Sí, eliminar
              </button>
            </form>
            <button id="cancelDelete" type="button" class="pf-btn pf-btn--secondary">
              <i class="fa-solid fa-xmark"></i> Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    @if($isOwner)
    <div class="modal" id="editModal" aria-hidden="true" role="dialog" aria-modal="true" inert>
      <div class="modal-content glass wide">
        <div class="modal-header sticky">
          <h3><i class="fa-solid fa-user-pen"></i> Editar perfil</h3>
          <div class="header-actions">
            <a href="{{ $cuentaUrl }}" class="pf-btn pf-btn--ghost pf-btn--sm" title="Abrir apartado Cuenta">
              <i class="fa-solid fa-gear"></i> Cuenta
            </a>
            <button type="button" class="pf-btn pf-btn--ghost pf-btn--sm" id="closeEditTop" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
          </div>
        </div>

        <div class="tabbar sticky" role="tablist">
          <button type="button" class="tab-btn active" data-tab="basic"><i class="fa-solid fa-wrench"></i> Básico</button>
          <button type="button" class="tab-btn" data-tab="advanced"><i class="fa-solid fa-sliders"></i> Configuración avanzada</button>
        </div>

        @php
          $perfilUpdate = R::has('perfil.update') ? route('perfil.update') : url('/perfil/update');
        @endphp
        <form action="{{ $perfilUpdate }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="tab-pane" data-pane="basic">
            <div class="modal-grid">
              <div class="modal-field col">
                <label>Nombre artístico actual</label>
                <div class="locked-input">
                  <input type="text" value="{{ $user->nombre_artistico ?? 'Sin definir' }}" readonly>
                  <i class="fa-solid fa-lock lock-icon"></i>
                </div>
              </div>
              <div class="modal-field col">
                <label>Nuevo nombre artístico</label>
                <input name="nuevo_nombre_artistico" type="text" placeholder="Escribe el nuevo nombre artístico">
              </div>
              <div class="modal-field col">
                <label>Foto de perfil</label>
                <label class="file-preview avatar-edit">
                  <img id="avatarPreview" src="{{ $user->avatar ? ($user->avatar_url.'?v='.$verUser) : '' }}" alt="" width="160" height="160" loading="lazy">
                  <input type="file" id="avatarInput" name="avatar" accept="image/*" hidden>
                  <div class="overlay"><i class="fa-solid fa-camera"></i></div>
                </label>
              </div>
              <div class="modal-field col">
                <label>Banner</label>
                <label class="file-preview banner-edit">
                  <img id="bannerPreview" src="{{ $user->banner ? ($user->banner_url.'?v='.$verUser) : '' }}" alt="" width="600" height="200" loading="lazy">
                  <input type="file" id="bannerInput" name="banner" accept="image/*" hidden>
                  <div class="overlay"><i class="fa-solid fa-camera"></i></div>
                </label>
              </div>
              <div class="modal-field col-2">
                <label>Descripción</label>
                <textarea name="bio" rows="4" placeholder="Escribe una breve biografía...">{{ $user->biografia ?? '' }}</textarea>
              </div>
            </div>
          </div>

          <div class="tab-pane hidden" data-pane="advanced">
            <div class="adv-grid">
              <div class="adv-card">
                <h4><i class="fa-solid fa-palette"></i> Color de acento</h4>
                <div class="field-row">
                  <input type="color" id="accColor" name="theme_accent" value="#7c3aed" aria-label="Color de acento">
                  <span class="hint">Afecta botones y aro del avatar.</span>
                </div>
              </div>

              <div class="adv-card">
                <h4><i class="fa-solid fa-layer-group"></i> Degradado del banner</h4>
                <div class="field-grid">
                  <label>Estilo</label>
                  <select id="gradStyle" name="banner_grad_style">
                    <option value="spotify" selected>Tipo Spotify (oscuro)</option>
                    <option value="soft">Suave</option>
                    <option value="contrast">Alto contraste</option>
                  </select>

                  <label>Opacidad general</label>
                  <input type="range" id="darknessRange" name="banner_darkness" min="0.70" max="0.99" step="0.01" value="0.94">

                  <label>Tinte (RGB)</label>
                  <input type="text" id="tintRgb" name="banner_tint_rgb" value="29,185,84" placeholder="r,g,b">
                  <small class="hint">Por defecto: verde Spotify (29,185,84)</small>
                </div>
              </div>

              <div class="adv-card">
                <h4><i class="fa-solid fa-eye"></i> Vista previa en vivo</h4>
                <div class="preview-note">Los cambios visuales se previsualizan al instante (no se guardan hasta enviar).</div>
                <a href="{{ $cuentaUrl }}" class="pf-btn pf-btn--ghost mt-8"><i class="fa-solid fa-gear"></i> Abrir “Cuenta” (más opciones)</a>
              </div>
            </div>
          </div>

          <div class="modal-actions sticky">
            <button type="submit" class="pf-btn pf-btn--primary"><i class="fa-solid fa-save"></i> Guardar</button>
            <button type="button" class="pf-btn pf-btn--secondary" id="closeEdit"><i class="fa-solid fa-xmark"></i> Cancelar</button>
          </div>
        </form>
      </div>
    </div>
    @endif

    <div class="modal" id="bioModal" aria-hidden="true" role="dialog" aria-modal="true" inert>
      <div class="modal-content bio">
        <div class="modal-header">
          <h3><i class="fa-solid fa-circle-info"></i> Sobre {{ $user->nombre_artistico ?? $user->nombre ?? 'el artista' }}</h3>
          <button type="button" class="pf-btn pf-btn--ghost pf-btn--sm" id="closeBioTop" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="bio-grid">
          <div class="bio-media">
            @if($user && $user->avatar)
              <img src="{{ $user->avatar_url }}?v={{ $verUser }}" alt="Foto de {{ $user->nombre_artistico ?? $user->nombre }}" width="160" height="160" loading="lazy" decoding="async">
            @else
              <div class="bio-fallback">{{ strtoupper(substr($user->nombre_artistico ?? $user->nombre ?? 'U',0,1)) }}</div>
            @endif
          </div>
          <div class="bio-copy">
            <p class="bio-text">{{ $bio }}</p>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="pf-btn pf-btn--secondary" id="closeBio"><i class="fa-solid fa-check"></i> Cerrar</button>
        </div>
      </div>
    </div>
<!-- ===== MODAL: AGREGAR A PLAYLIST ===== -->
<div class="modal" id="playlistModal" aria-hidden="true" role="dialog" aria-modal="true" inert>
  <div class="modal-content glass" style="max-width:560px">
    <div class="modal-header">
      <h3><i class="fa-solid fa-square-plus"></i> Agregar a playlist</h3>
      <button type="button" class="pf-btn pf-btn--ghost pf-btn--sm" id="plCloseTop" aria-label="Cerrar">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="pl-body">
      <div class="pl-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input id="plSearch" type="search" placeholder="Buscar playlist...">
      </div>

      <ul id="plList" class="pl-list" aria-live="polite">
        <!-- se rellena por JS -->
      </ul>

      <div class="pl-quick">
        <form id="plQuickForm">
          @csrf
          <input id="plQuickName" type="text" placeholder="Nueva playlist..." maxlength="120">
          <button class="pf-btn pf-btn--primary pf-btn--sm" type="submit">
            <i class="fa-solid fa-plus"></i> Crear y agregar
          </button>
        </form>
      </div>
    </div>

    <div class="modal-actions">
      <button type="button" class="pf-btn pf-btn--secondary" id="plCloseBottom">
        <i class="fa-solid fa-check"></i> Cerrar
      </button>
    </div>
  </div>
</div>

  </main>

  @include('components.footer')

</div><!-- /#page-profile -->

<!-- ===== Scripts ligeros: medición, banner, UI, like, audio ===== -->
<script>
(function () {
  const rootEl = document.getElementById('page-profile') || document.documentElement;
  const sidebar = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
  const rightPlayer = document.getElementById('rightPlayer');
  const setVar = (n, px) => rootEl.style.setProperty(n, Math.max(0, Math.round(px)) + 'px');

  let rafId = null;
  function measureNow(){
    if (sidebar) { setVar('--aurp-sb-w', sidebar.getBoundingClientRect().width || 90); } else { setVar('--aurp-sb-w', 0); }
    if (rightPlayer) { setVar('--aurp-rp-w', rightPlayer.getBoundingClientRect().width || 360); } else { setVar('--aurp-rp-w', 0); }
    rafId = null;
  }
  function measure(){ if (!rafId) rafId = requestAnimationFrame(measureNow); }

  window.addEventListener('load', measure, { once:true });
  window.addEventListener('resize', measure, { passive:true });

  if (window.ResizeObserver){
    const ro = new ResizeObserver(measure);
    sidebar && ro.observe(sidebar);
    rightPlayer && ro.observe(rightPlayer);
  }
  const mo = new MutationObserver(measure);
  sidebar && mo.observe(sidebar, {attributes:true, attributeFilter:['class','style']});
  document.body && mo.observe(document.body, {attributes:true, attributeFilter:['class']});
  measure();
})();
</script>

<<<<<<< HEAD
<!-- Código de "like" y "añadir a cola" -->
<script>
document.querySelectorAll('.like-btn').forEach(button => {
  const songId = button.closest('form').dataset.songId;

  // Verificar el estado del "like" al cargar la página
  fetch(`/canciones/${songId}/liked`)
    .then(response => response.json())
    .then(data => {
      if (data.liked) {
        button.innerHTML = '<i class="fa-solid fa-heart" style="color:#aa029c"></i>';  // Estado de "like" activado
      } else {
        button.innerHTML = '<i class="fa-regular fa-heart"></i>';  // Estado de "like" desactivado
      }
    })
    .catch(err => console.error('Error al verificar el estado del like:', err));

  // Alternar "like" cuando se haga clic en el botón
  button.addEventListener('click', (e) => {
    e.preventDefault();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    fetch(`/canciones/${songId}/like`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.liked) {
        button.innerHTML = '<i class="fa-solid fa-heart" style="color:#aa029c"></i>';
      } else {
        button.innerHTML = '<i class="fa-regular fa-heart"></i>';
      }
    })
    .catch(err => console.error('Error al alternar el me gusta:', err));
=======
<script>
(function () {
  const run = (fn) => (window.requestIdleCallback ? requestIdleCallback(fn, {timeout: 1500}) : setTimeout(fn, 0));
  run(() => {
    const b = document.querySelector('.profile-banner');
    if (!b) return;
    const hires = b.getAttribute('data-hires');
    if (!hires) return;
    const img = new Image();
    img.decoding = 'async';
    img.src = hires;
    img.onload = () => { b.style.backgroundImage = `url('${hires}')`; b.classList.add('loaded'); };
  });
})();
</script>

<script>
(function(){
  const $ = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  /* ===== Sincronizar altura canciones ↔ álbumes 2×2 ===== */
  function syncSongsBoxHeight(){
    const host = document.getElementById('page-profile');
    const viewport = document.getElementById('albumsViewport');
    if (!host || !viewport) return;
    host.style.setProperty('--aurp-songs-box-h', `${viewport.getBoundingClientRect().height}px`);
  }
  window.addEventListener('load', syncSongsBoxHeight, { once:true });
  window.addEventListener('resize', syncSongsBoxHeight, { passive:true });
  if (window.ResizeObserver) {
    const ro = new ResizeObserver(syncSongsBoxHeight);
    const vp = document.getElementById('albumsViewport');
    vp && ro.observe(vp);
  }

  /* ===== Menú 3 puntos ===== */
  function closeMenus(except=null){
    $$('.menu-wrap .kebab-menu.open').forEach(m=>{
      if (except && m===except) return;
      m.classList.remove('open');
      m.parentElement?.querySelector('.more-btn')?.setAttribute('aria-expanded','false');
    });
  }
  document.addEventListener('click', (e) => {
    const moreBtn = e.target.closest('.more-btn');
    if (moreBtn) {
      const wrap = moreBtn.closest('.menu-wrap');
      const menu = wrap?.querySelector('.kebab-menu');
      if (!menu) return;
      const willOpen = !menu.classList.contains('open');
      closeMenus();
      menu.classList.toggle('open', willOpen);
      moreBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      return;
    }
    if (!e.target.closest('.menu-wrap')) closeMenus();
  });
  document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') closeMenus(); });

  /* ===== Like (UI optimista) ===== */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.inline-like .like-btn');
    if (!btn) return;
    e.preventDefault();
    const form = btn.closest('form.inline-like');
    const icon = btn.querySelector('i');
    const wasLiked = btn.classList.contains('is-liked');

    btn.classList.toggle('is-liked', !wasLiked);
    btn.setAttribute('aria-pressed', (!wasLiked).toString());
    icon.classList.toggle('fa-regular', wasLiked);
    icon.classList.toggle('fa-solid', !wasLiked);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();
      const finalLiked = !!data.liked;
      btn.classList.toggle('is-liked', finalLiked);
      btn.setAttribute('aria-pressed', String(finalLiked));
      icon.classList.toggle('fa-regular', !finalLiked);
      icon.classList.toggle('fa-solid', finalLiked);
    } catch (err) {
      btn.classList.toggle('is-liked', wasLiked);
      btn.setAttribute('aria-pressed', String(wasLiked));
      icon.classList.toggle('fa-regular', !wasLiked);
      icon.classList.toggle('fa-solid', wasLiked);
      console.error('Like error:', err);
    }
>>>>>>> recup-ayer
  });

  /* ===== Confirmar eliminar (modal) ===== */
  const confirmModal   = $('#confirmModal');
  const confirmCover   = $('#confirmCover');
  const confirmSubtitle= $('#confirmSubtitle');
  const deleteForm     = $('#deleteForm');
  const cancelDelete   = $('#cancelDelete');

  function openConfirm({action, title, cover}){
    if (!confirmModal) return;
    deleteForm?.setAttribute('action', action || '#');
    if (confirmCover) confirmCover.src = cover || '';
    if (confirmSubtitle) confirmSubtitle.textContent = title ? `“${title}”` : '';
    document.body.classList.add('blurred');
    confirmModal.setAttribute('aria-hidden','false');
  }
  function closeConfirm(){
    if (!confirmModal) return;
    document.body.classList.remove('blurred');
    confirmModal.setAttribute('aria-hidden','true');
  }

  document.addEventListener('click', (e) => {
    const delBtn = e.target.closest('.open-delete');
    if (delBtn) {
      e.preventDefault();
      const action = delBtn.dataset.action || '#';
      if (action === '#') return;
      openConfirm({
        action,
        title:  delBtn.dataset.title,
        cover:  delBtn.dataset.cover
      });
      return;
    }
    if (confirmModal && confirmModal.getAttribute('aria-hidden')==='false' && e.target === confirmModal) {
      closeConfirm();
    }
    if (cancelDelete && e.target === cancelDelete) {
      e.preventDefault();
      closeConfirm();
    }
  });
  document.addEventListener('keydown', (e) => { if (e.key==='Escape') closeConfirm(); });

  /* ===== Reproducción rápida ===== */
  const playerCard = document.getElementById('rightPlayer');
  const sharedAudio = playerCard ? (playerCard.querySelector('audio') || new Audio()) : new Audio();
  if (playerCard && !playerCard.querySelector('audio')) {
    sharedAudio.preload = 'metadata';
    playerCard.appendChild(sharedAudio);
  } else {
    sharedAudio.preload = 'metadata';
  }

  function setPlayerUI({title, artist, cover}){
    if (!playerCard) return;
    const coverImg = playerCard.querySelector('.cover');
    const nameEl   = playerCard.querySelector('.song-name');
    const autorEl  = playerCard.querySelector('.song-autor');
    coverImg && cover && (coverImg.src = cover);
    nameEl  && (nameEl.textContent = title || 'Sin título');
    autorEl && (autorEl.textContent = artist || 'Artista');
  }

  async function playSrc(src, meta){
    if (!src) return;
    try {
      if (window.AuraQueue?.externalPlay) {
        window.AuraQueue.externalPlay({ id: meta?.id, src, title: meta?.title, artist: meta?.artist, cover: meta?.cover });
        return;
      }
      if (window.AuraPlayer?.play) {
        window.AuraPlayer.play({ id: meta?.id, src, title: meta?.title, artist: meta?.artist, cover: meta?.cover });
        return;
      }
      if (sharedAudio.src !== src) {
        sharedAudio.preload = 'metadata';
        sharedAudio.src = src;
      }
      setPlayerUI(meta || {});
      await sharedAudio.play();
    } catch (err) { console.error('Play error:', err); }
  }

  // Click fila canción
  document.addEventListener('click', (e) => {
    const row = e.target.closest('.song-row');
    if (!row) return;
    if (e.target.closest('.song-actions, .menu-wrap, form, button, a, input, .trash-float')) return;
    playSrc(row.getAttribute('data-src'), {
      id: row.getAttribute('data-id'),
      title: row.getAttribute('data-title'),
      artist: row.getAttribute('data-artist'),
      cover: row.getAttribute('data-cover')
    });
  });

  // Lanzamientos → reproducir
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.play-release');
    if (!btn) return;
    playSrc(btn.dataset.src, {
      id: btn.dataset.id,
      title: btn.dataset.title,
      artist: btn.dataset.artist,
      cover: btn.dataset.cover
    });
  });

  // Prewarm de audio al pasar el mouse por una canción
  const prewarmed = new Set();
  document.addEventListener('pointerenter', (e) => {
    const row = e.target.closest('.song-row');
    if (!row) return;
    const src = row.getAttribute('data-src');
    if (!src || prewarmed.has(src)) return;
    const a = new Audio();
    a.preload = 'metadata';
    a.src = src;
  }, { passive:true });

  // Prefetch de páginas de álbum
  const hovered = new Set();
  document.addEventListener('pointerenter', (e) => {
    const a = e.target.closest('a[data-prefetch="true"]');
    if (!a || hovered.has(a.href)) return;
    hovered.add(a.href);
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = a.href;
    document.head.appendChild(link);
  }, {passive:true});

  /* ===== Carruseles básicos ===== */
  function initCarousel(trackSel, prevSel, nextSel, labelSel, pagerSel){
    const track = $(trackSel); if (!track) return;
    const pages = parseInt(track.dataset.pages || '1', 10) || 1;
    const prev  = $(prevSel);
    const next  = $(nextSel);
    const label = labelSel ? $(labelSel) : null;
    const pager = pagerSel ? $(pagerSel) : null;
    let page = 0;

    function paintPager(){
      if (!pager) return;
      pager.innerHTML = '';
      for (let i=0; i<pages; i++){
        const dot = document.createElement('div');
        dot.className = 'dot' + (i===page ? ' active' : '');
        pager.appendChild(dot);
      }
    }
    function update(){
      track.style.transform = `translateX(-${page * 100}%)`;
      prev && (prev.disabled = page <= 0);
      next && (next.disabled = page >= pages - 1);
      label && (label.textContent = pages > 1 ? `${page+1} / ${pages}` : '');
      paintPager();
    }
    prev && prev.addEventListener('click', () => { if (page>0){ page--; update(); }});
    next && next.addEventListener('click', () => { if (page<pages-1){ page++; update(); }});
    update();
  }

  initCarousel('#albumsTrack', '#albumsPrev', '#albumsNext', '#albumsPageLabel', null);
  initCarousel('#releasesTrack', '#releasesPrev', '#releasesNext', null, '#releasesPager');

})();
</script>

</body>
</html>
