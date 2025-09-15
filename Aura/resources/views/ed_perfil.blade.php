<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('ed_perfil.title', ['name' => $user->nombre_artistico ?? 'Invitado']) }}</title>

  <!-- Hints de red (reduce latencia de fuentes/CDN) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

  <!-- Fonts (swap evita FOIT) -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">

  <!-- Font Awesome: carga no-bloqueante -->
  <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

  {{-- CSS + JS principal (un solo @vite con entradas válidas) --}}
  @vite(['resources/css/ed_perfil.css','resources/js/ed_perfil.js'])

  @php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route as RouteFacade;

    // Versionado estable por updated_at (sin time() para no romper caché)
    $verUser     = optional($user->updated_at)->timestamp ?? 0;

    // Banners/avatares
    $bannerSrc   = $user->banner_url ?? asset('img/default-banner.jpg');
    $bannerLow   = $bannerSrc.'?v='.$verUser;
    $bannerHigh  = $bannerSrc.'?v='.$verUser;

    $followersCount = method_exists($user,'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0);
    $listenersCount = (int)($user->oyentes_mensuales ?? 0);

    if (!function_exists('num_format_sp')) {
      function num_format_sp($n){ return is_numeric($n) ? number_format((float)$n,0,',','.') : $n; }
    }

    $bio = trim($user->biografia ?? '') ?: 'Sin biografía por ahora.';

    // URLs auxiliares (con fallback)
    $cuentaUrl =
      (RouteFacade::has('cuenta.index')     ? route('cuenta.index')     :
      (RouteFacade::has('cuenta')           ? route('cuenta')           :
      (RouteFacade::has('settings.account') ? route('settings.account') :
      (RouteFacade::has('account.index')    ? route('account.index')    :
       url('/cuenta')))));

    $loginUrl = (RouteFacade::has('login') ? route('login') : url('/login'));

    $isAuth  = Auth::check();
    $isOwner = $isAuth && Auth::id() === $user->id;
  @endphp

  <!-- Preload banner above-the-fold -->
  <link rel="preload" as="image" href="{{ $bannerLow }}">

  <!-- CSS de performance puntual sin tocar tus hojas -->
  <style>
    /* Evita trabajo de render fuera de pantalla */
    .no-clip, .music-layout, .releases-section, .albums-wrap, .songs-list { content-visibility: auto; contain-intrinsic-size: 800px 600px; }
    /* Minimiza CLS en imágenes conocidas */
    .avatar-img{ width:160px; height:160px; object-fit:cover; }
    .song-thumb img{ width:64px; height:64px; object-fit:cover; }
    .album-card .card-img img{ width:300px; height:300px; object-fit:cover; }
    .release-card .card-img img{ width:240px; height:240px; object-fit:cover; }
    /* Oculta scroll-jank en transiciones si el usuario lo prefiere */
    @media (prefers-reduced-motion: reduce){
      *{ animation: none !important; transition: none !important; }
    }
  </style>
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
        <!-- Banner con LQIP + swap a alta -->
        <div class="profile-banner" data-hires="{{ $bannerHigh }}" style="background-image:url('{{ $bannerLow }}')"></div>
        <div class="hero-scrim" aria-hidden="true"></div>
        <div class="hero-border" aria-hidden="true"></div>

        <!-- Info -->
        <button type="button" class="pf-btn pf-btn--ghost pf-btn--sm info-btn" id="openBio" title="Ver biografía">
          <i class="fa-solid fa-circle-info" aria-hidden="true"></i><span class="sr-only">Ver biografía</span>
        </button>

        <!-- Editar (solo dueño) -->
        @if($isOwner)
        <div class="edit-btn">
          <button class="pf-btn pf-btn--primary pf-btn--sm" id="editBtn">
            <i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span>
          </button>
        </div>
        @endif

        <!-- Título y seguir -->
        <div class="hero-head">
          <div class="text-shield">
            <h1 class="hero-title" id="artistName">{{ $user->nombre_artistico ?? 'Artista' }}</h1>
          </div>

          <div class="follow-under-name">
            @if($isOwner)
              {{-- nada --}}
            @elseif($isAuth)
              @php
                $hasToggle    = RouteFacade::has('follow.toggle');
                $hasFollow    = RouteFacade::has('perfil.follow');
                $hasUnfollow  = RouteFacade::has('perfil.unfollow');
                $isFollowing  = method_exists(Auth::user(), 'isFollowing') ? Auth::user()->isFollowing($user->id) : false;
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

        <!-- Metas -->
        <div class="profile-footer">
          <span class="meta"><i class="fa-solid fa-headphones" aria-hidden="true"></i> {{ num_format_sp($listenersCount) }} oyentes mensuales</span>
          <span class="meta"><i class="fa-solid fa-user-group" aria-hidden="true"></i> {{ num_format_sp($followersCount) }} seguidores</span>
        </div>

        <!-- Avatar -->
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
              $coverUrlBase = $song->cover_url ?? asset('img/default-cancion.png');
              $coverUrl     = $coverUrlBase.'?v='.$songVer;
              $audioUrl     = $song->audio_url ?? null;
              $dur          = $song->duration ?? $song->duracion ?? '0:00';

              // Rutas seguras (evita "Route not defined")
              $likeAction = RouteFacade::has('canciones.like')
                  ? route('canciones.like', $song->id)
                  : (RouteFacade::has('cancion.like')
                      ? route('cancion.like', $song->id)
                      : null);

              $albumShow = RouteFacade::has('album.show')
                  ? route('album.show', $song->album_id ?? 0)
                  : null;

              $songDelete = RouteFacade::has('cancion.destroy')
                  ? route('cancion.destroy', $song->id)
                  : '#';
            @endphp

            <div class="song-row" data-song-id="{{ $song->id }}">
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
                @auth
                  @if($likeAction)
                    <form action="{{ $likeAction }}" method="POST" class="inline-like" data-song-id="{{ $song->id }}">@csrf
                      <button type="submit" class="icon-chip like-btn" aria-pressed="false" title="Me gusta">
                        <i class="fa-regular fa-heart" aria-hidden="true"></i><span class="sr-only">Me gusta</span>
                      </button>
                    </form>
                  @else
                    <button type="button" class="icon-chip like-btn" title="Me gusta no disponible" disabled>
                      <i class="fa-regular fa-heart" aria-hidden="true"></i>
                    </button>
                  @endif
                  <button class="icon-chip add-playlist-btn" title="Agregar a playlist"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                @else
                  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión para dar Me gusta"><i class="fa-regular fa-heart"></i></a>
                  <a href="{{ $loginUrl }}" class="icon-chip" title="Inicia sesión para usar playlists"><i class="fa-solid fa-plus"></i></a>
                @endauth

                <div class="menu-wrap">
                  <button class="icon-chip more-btn" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-ellipsis"></i></button>
                  <ul class="kebab-menu">
                    @auth
                      <li><button class="menu-item" data-action="queue"><i class="fa-solid fa-list-ol"></i> Añadir a cola</button></li>
                      <li><button class="menu-item" data-action="playlist"><i class="fa-solid fa-square-plus"></i> Agregar a playlist</button></li>
                      <li><button class="menu-item" data-action="like"><i class="fa-regular fa-heart"></i> Añadir a Me gusta</button></li>
                      @if($isOwner)
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

              <!-- Botón oculto para tu reproductor -->
              <button class="cancion-item" style="display:none"
                data-id="{{ $song->id }}"
                data-src="{{ $audioUrl }}"
                data-title="{{ $songTitle }}"
                data-artist="{{ $user->nombre_artistico ?? 'Desconocido' }}"
                data-cover="{{ $coverUrl }}"></button>

              @if($isOwner)
                <button class="trash-float open-delete"
                        title="Eliminar"
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

                        $albumHref = RouteFacade::has('album.show')
                          ? route('album.show', $album->id)
                          : url('/album/'.$album->id);

                        $albumDelete = RouteFacade::has('album.destroy')
                          ? route('album.destroy', $album->id)
                          : '#';
                      @endphp

                      <div class="card album-card {{ $isOwner ? 'has-trash' : '' }}">
                        <a href="{{ $albumHref }}" class="card-link">
                          <div class="card-img">
                            <img src="{{ $albumCover }}" alt="Portada"
                                 width="300" height="300"
                                 loading="lazy" decoding="async">
                            <span class="album-play"><i class="fa-solid fa-play"></i></span>
                          </div>
                          <h4 class="album-title">{{ $album->titulo }}</h4>
                          <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre }}</p>
                        </a>

                        @if($isOwner)
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
      $releasesAllUrl = RouteFacade::has('perfil.releasesAll')
          ? route('perfil.releasesAll', $user->id)
          : url('/perfil/'.$user->id.'/lanzamientos');

      $normalizedReleases = collect($lanzamientos)->map(function($it){
        $tipo   = $it['tipo']   ?? $it->tipo   ?? 'album';
        $titulo = $it['titulo'] ?? $it->titulo ?? $it->title ?? null;
        $cover  = $it['cover']  ?? $it->cover  ?? $it->portada ?? $it->cover_path ?? null;
        $coverUrl = $it['cover_url'] ?? ($it->cover_url ?? null);
        $anio   = $it['anio']   ?? $it->anio   ?? (isset($it->created_at) ? optional($it->created_at)->format('Y') : '');
        $ver    = optional($it['updated_at'] ?? ($it->updated_at ?? null))->timestamp ?? 0;
        $titulo = $titulo ?: 'Sin título';
        return ['tipo'=>$tipo,'titulo'=>$titulo,'cover'=>$cover,'cover_url'=>$coverUrl,'anio'=>$anio,'ver'=>$ver,'href'=>$it['href'] ?? ($it->href ?? null), 'delete'=>$it['delete'] ?? ($it->delete ?? null)];
      })
      ->filter(fn($x) => !empty($x['titulo']))
      ->values();

      $relPages = $normalizedReleases->chunk(8); // 4×2 por página
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
                      $cls = 'card release-card' . ($isOwner ? ' has-trash' : '');
                      $relDelete = $item['delete'] ?? '#';
                    @endphp

                    <div class="{{ $cls }}">
                      <div class="card-img">
                        <img src="{{ $rCover }}" alt="Portada"
                             width="240" height="240"
                             loading="lazy" decoding="async">
                        <span class="type-badge">{{ $item['tipo'] === 'album' ? 'Álbum' : 'Canción' }}</span>
                      </div>
                      <h4 title="{{ $item['titulo'] }}">{{ $item['titulo'] }}</h4>
                      <p>{{ $item['anio'] }}</p>

                      @if($isOwner)
                        <button class="trash-float open-delete"
                                title="Eliminar"
                                data-type="{{ $item['tipo'] === 'album' ? 'album' : 'song' }}"
                                data-action="{{ $relDelete }}"
                                data-title="{{ $item['titulo'] }}"
                                data-cover="{{ $rCover }}">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      @endif

                      @if($item['tipo']==='album' && !empty($item['href']))
                        <a class="card-link" href="{{ $item['href'] }}" aria-label="Abrir álbum"></a>
                      @else
                        <button type="button" class="card-link play-release" aria-label="Reproducir"></button>
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

    <!-- ===== MODAL EDITAR PERFIL ===== -->
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
          $perfilUpdate = RouteFacade::has('perfil.update') ? route('perfil.update') : url('/perfil/update');
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

    <!-- ===== MODAL BIOGRAFÍA ===== -->
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

  </main>

  @include('components.footer')

</div><!-- /#page-profile -->

<!-- NO dupliques @vite de JS aquí: ya cargó arriba -->

<!-- ===== Medición dinámica de sidebar y reproductor (throttle + idle) ===== -->
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

<!-- ===== Lazy-hydration ligera (banner swap + modales) ===== -->
<script>
(function () {
  const run = (fn) => (window.requestIdleCallback ? requestIdleCallback(fn, {timeout: 1500}) : setTimeout(fn, 0));

  // Swap a banner en alta una vez decodificada (evita salto)
  run(() => {
    const b = document.querySelector('.profile-banner');
    if (!b) return;
    const hires = b.getAttribute('data-hires');
    if (!hires) return;
    const img = new Image();
    img.decoding = 'async';
    img.src = hires;
    img.onload = () => { b.style.backgroundImage = `url('${hires}')`; };
  });

  // Modales: quita inert solo al abrir (menos trabajo inicial)
  run(() => {
    const editBtn = document.getElementById('editBtn');
    const editModal = document.getElementById('editModal');
    const closeEdit = document.getElementById('closeEdit');
    const closeEditTop = document.getElementById('closeEditTop');

    function openModal(m){ m?.removeAttribute('inert'); m?.setAttribute('aria-hidden','false'); }
    function closeModal(m){ m?.setAttribute('aria-hidden','true'); m?.setAttribute('inert',''); }

    if (editBtn && editModal){
      editBtn.addEventListener('click', () => openModal(editModal), {passive:true});
      closeEdit && closeEdit.addEventListener('click', () => closeModal(editModal), {passive:true});
      closeEditTop && closeEditTop.addEventListener('click', () => closeModal(editModal), {passive:true});
      editModal.addEventListener('click', (e) => { if (e.target === editModal) closeModal(editModal); }, {passive:true});
    }

    const bioBtn = document.getElementById('openBio');
    const bioModal = document.getElementById('bioModal');
    const closeBio = document.getElementById('closeBio');
    const closeBioTop = document.getElementById('closeBioTop');
    if (bioBtn && bioModal){
      bioBtn.addEventListener('click', () => openModal(bioModal), {passive:true});
      closeBio && closeBio.addEventListener('click', () => closeModal(bioModal), {passive:true});
      closeBioTop && closeBioTop.addEventListener('click', () => closeModal(bioModal), {passive:true});
      bioModal.addEventListener('click', (e) => { if (e.target === bioModal) closeModal(bioModal); }, {passive:true});
    }
  });
})();
</script>
</body>
</html>
