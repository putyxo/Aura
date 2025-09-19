{{-- resources/views/ed_perfil.blade.php --}}
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('ed_perfil.title', ['name' => $user->nombre_artistico ?? 'Invitado']) }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

  @vite(['resources/css/ed_perfil.css'])
  @vite(['resources/js/app.js'])

  @php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Str;

    $verUser     = optional($user->updated_at)->timestamp ?? 0;

    $bannerSrc   = $user->banner_url ?? asset('img/default-banner.jpg');
    $bannerLow   = $bannerSrc.'?v='.$verUser;
    $bannerHigh  = $bannerSrc.'?v='.$verUser;

    $followersCount = method_exists($user,'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0);

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

    $isAuth   = auth()->check();
    $isOwner  = $isAuth && auth()->id() === $user->id;
    $isArtist = (bool)($user->es_artista ?? 0);
    $artistMsg = $isOwner ? 'No eres un artista' : 'No es un artista';

    // Preloads opcionales
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

    // ✅ Avatar por defecto
    $avatarFinal = ($user && !empty($user->avatar))
      ? ($user->avatar_url.'?v='.$verUser)
      : asset('img/perfil_npc.png');
  @endphp

  <link rel="preload" as="image" href="{{ $bannerLow }}">
  @if($preSongCover)<link rel="preload" as="image" href="{{ $preSongCover }}">@endif
  @if($preAlbumCover)<link rel="preload" as="image" href="{{ $preAlbumCover }}">@endif
</head>
<body data-page="perfil">
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
            <h1 class="hero-title" id="artistName">
              {{ $isArtist ? ($user->nombre_artistico ?? ($user->nombre ?? 'Usuario')) : ($user->nombre ?? 'Usuario') }}
            </h1>
          </div>

          <div class="follow-under-name">
            {{-- ✅ Solo seguir si el perfil es artista y no eres tú --}}
            @if($isArtist && !$isOwner)
              @php
                $hasToggle    = R::has('follow.toggle');
                $hasFollow    = R::has('perfil.follow');
                $hasUnfollow  = R::has('perfil.unfollow');
                $isFollowing  = $isAuth && method_exists(auth()->user(), 'isFollowing') ? auth()->user()->isFollowing($user->id) : false;
              @endphp

              @if($isAuth)
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
                @endif
              @else
                <a href="{{ $loginUrl }}" class="pf-btn follow-pill"><i class="fa-solid fa-user-plus"></i> Seguir</a>
              @endif
            @endif
          </div>
        </div>

        <div class="profile-footer">
          {{-- ❌ Oyentes mensuales quitado --}}
          <span class="meta"><i class="fa-solid fa-user-group" aria-hidden="true"></i> {{ num_format_sp($followersCount) }} seguidores</span>
        </div>

        <div class="avatar-wrap xl">
          <img id="avatarPreviewLive" class="avatar-img"
               src="{{ $avatarFinal }}"
               alt="{{ $user->nombre_artistico ?? $user->nombre ?? 'Usuario' }}"
               width="160" height="160"
               loading="eager" fetchpriority="high" decoding="async">
        </div>
      </div>
    </section>

    {{-- ===== CONTENIDO (si no es artista, sólo mensaje) ===== --}}
    @if(!$isArtist)
      <section class="not-artist-block">
        <div class="empty-state card glass"
             style="display:flex;gap:.75rem;align-items:center;justify-content:center;min-height:220px;border-radius:18px;">
          <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
          <span>{{ $artistMsg }}</span>
        </div>
      </section>
    @else
      <!-- ===== MUSIC LAYOUT (Canciones + Álbumes) ===== -->
      <section class="music-layout no-clip">
        <!-- CANCIONES -->
        <div class="music-column left-col no-clip">
          <div class="section-title"><h2><i class="fa-solid fa-music" aria-hidden="true"></i> Canciones</h2></div>

          <div class="songs-list fixed-six" id="songsList">
            @foreach($canciones->take(6) as $song)
              @php
                $songTitle    = $song->title ?? $song->nombre ?? 'Sin título';
                $songVer      = optional($song->updated_at)->timestamp ?? 0;

                $tmpCover = $song->cover_url
                  ?? ((isset($song->cover) || isset($song->cover_path))
                        ? (Str::startsWith(($song->cover ?? $song->cover_path), ['http://','https://'])
                            ? ($song->cover ?? $song->cover_path)
                            : asset('storage/'.ltrim(($song->cover ?? $song->cover_path),'/')))
                        : asset('img/default-cancion.png'));
                $coverUrl  = $tmpCover.'?v='.$songVer;

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

                $songDelete = (R::has('cancion.destroy') && !empty($song->id))
                    ? route('cancion.destroy', ['cancion' => $song->id])
                    : '#';

                $isLiked = $isAuth && method_exists($song,'isLikedBy') ? $song->isLikedBy(auth()->user()) : false;
              @endphp

              <div class="song-row"
                   data-id="{{ $song->id }}"
                   data-src="{{ $audioUrl }}"
                   data-title="{{ $songTitle }}"
                   data-artist="{{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}"
                   data-cover="{{ $coverUrl }}"
                   role="button" tabindex="0" aria-label="Reproducir {{ $songTitle }}">
                <div class="song-index">{{ $loop->iteration }}</div>

                <div class="song-thumb">
                  <img src="{{ $coverUrl }}" alt="Portada" width="64" height="64" loading="lazy" decoding="async">
                </div>

                <div class="song-info">
                  <h4 class="song-title" title="{{ $songTitle }}">{{ $songTitle }}</h4>
                  <div class="song-sub"><span class="artist-name">{{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</span></div>
                </div>

                <div class="song-duration">{{ $dur }}</div>

                <div class="song-actions">
                  @auth
                    @if($likeAction)
                      <form action="{{ $likeAction }}" method="POST"
                            class="inline-like"
                            data-song-id="{{ $song->id }}"
                            data-liked="{{ $isLiked ? 1 : 0 }}"
                            data-turbo="false">
                        @csrf
                        <button type="button" class="icon-chip like-btn {{ $isLiked ? 'is-liked' : '' }}"
                                aria-pressed="{{ $isLiked ? 'true' : 'false' }}" title="Me gusta">
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
                  {{-- Basurero flotante para canciones --}}
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

                          $albumHref = !empty($album->id)
                            ? (R::has('album.show') ? route('album.show', ['id' => $album->id]) : url('/albums/'.$album->id))
                            : '#';

                          $albumDelete = (R::has('profile.albums.destroy') && !empty($album->id))
                            ? route('profile.albums.destroy', ['id' => $album->id])
                            : '#';
                        @endphp

                        <div class="card album-card {{ $isOwner ? 'has-trash' : '' }}">
                          <a href="{{ $albumHref }}" class="card-link" data-prefetch="true">
                            <div class="card-img">
                              <img src="{{ $albumCover }}" alt="Portada" width="300" height="300" loading="lazy" decoding="async">
                              <span class="album-play"><i class="fa-solid fa-play"></i></span>
                            </div>
                            <h4 class="album-title">{{ $album->titulo }}</h4>
                            <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre }}</p>
                          </a>

                          @if($isOwner && $albumDelete !== '#')
                            {{-- Basurero flotante para álbumes --}}
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
    @endif
  </main>

  @include('components.footer')

</div><!-- /#page-profile -->

{{-- ===== MODAL CONFIRMAR ELIMINAR ===== --}}
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

{{-- ===== MODAL EDITAR PERFIL (solo dueño) ===== --}}
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

    @php $perfilUpdate = R::has('perfil.update') ? route('perfil.update') : url('/perfil/update'); @endphp
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
              <img id="avatarPreview" src="{{ $user->avatar ? ($user->avatar_url.'?v='.$verUser) : asset('img/perfil_npc.png') }}" alt="" width="160" height="160" loading="lazy">
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

{{-- ===== MODAL BIO ===== --}}
<div class="modal" id="bioModal" aria-hidden="true" role="dialog" aria-modal="true" inert>
  <div class="modal-content bio">
    <div class="modal-header">
      <h3><i class="fa-solid fa-circle-info"></i> Sobre {{ $user->nombre_artistico ?? $user->nombre ?? 'el artista' }}</h3>
    <button type="button" class="pf-btn pf-btn--ghost pf-btn--sm" id="closeBioTop" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="bio-grid">
      <div class="bio-media">
        <img src="{{ $avatarFinal }}" alt="Foto de {{ $user->nombre_artistico ?? $user->nombre ?? 'Usuario' }}" width="160" height="160" loading="lazy" decoding="async">
      </div>
      <div class="bio-copy"><p class="bio-text">{{ $bio }}</p></div>
    </div>
    <div class="modal-actions">
      <button type="button" class="pf-btn pf-btn--secondary" id="closeBio"><i class="fa-solid fa-check"></i> Cerrar</button>
    </div>
  </div>
</div>

</body>
</html>
