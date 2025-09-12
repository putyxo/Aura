<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Perfil — {{ $user->nombre_artistico ?? 'Invitado' }} · Aura</title>

  <!-- Hints de red -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" referrerpolicy="no-referrer">

  {{-- CSS --}}
  @vite(['resources/css/ed_perfil.css', 'resources/js/ed-perfil.js'])

  @php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route as RouteFacade;

    $bannerLow  = $user->banner ? drive_img_url($user->banner, 640)  . '&v=' . time() : asset('img/default-banner.jpg');
    $bannerHigh = $user->banner ? drive_img_url($user->banner, 1920) . '&v=' . time() : asset('img/default-banner.jpg');

    $followersCount = method_exists($user,'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0);
    $listenersCount = (int)($user->oyentes_mensuales ?? 0);

    if (!function_exists('num_format_sp')) {
      function num_format_sp($n){ return is_numeric($n) ? number_format((float)$n,0,',','.') : $n; }
    }

    $bio = trim($user->biografia ?? '') ?: 'Sin biografía por ahora.';

    // URLs auxiliares
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

  <link rel="preload" as="image" href="{{ $bannerLow }}">
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

        <!-- Info -->
        <button type="button" class="pf-btn pf-btn--ghost pf-btn--sm info-btn" id="openBio" title="Ver biografía">
          <i class="fa-solid fa-circle-info"></i>
        </button>

        <!-- Editar (solo dueño) -->
        @if($isOwner)
        <div class="edit-btn">
          <button class="pf-btn pf-btn--primary pf-btn--sm" id="editBtn">
            <i class="fa-solid fa-pen"></i><span>Editar</span>
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
                $hasToggle = \Illuminate\Support\Facades\Route::has('follow.toggle');
                $isFollowing = Auth::user()->isFollowing($user->id);
              @endphp
              @if($hasToggle)
                <form action="{{ route('follow.toggle', $user->id) }}" method="POST">@csrf
                  <button type="submit" class="pf-btn follow-pill">
                    <i class="fa-solid {{ $isFollowing ? 'fa-user-minus' : 'fa-user-plus' }}"></i>
                    {{ $isFollowing ? 'Dejar de seguir' : 'Seguir' }}
                  </button>
                </form>
              @else
                @if($isFollowing)
                  <form action="{{ route('perfil.unfollow', $user->id) }}" method="POST">@csrf
                    <button type="submit" class="pf-btn follow-pill"><i class="fa-solid fa-user-minus"></i> Dejar de seguir</button>
                  </form>
                @else
                  <form action="{{ route('perfil.follow', $user->id) }}" method="POST">@csrf
                    <button type="submit" class="pf-btn follow-pill"><i class="fa-solid fa-user-plus"></i> Seguir</button>
                  </form>
                @endif
              @endif
            @else
              <a href="{{ $loginUrl }}" class="pf-btn follow-pill"><i class="fa-solid fa-user-plus"></i> Seguir</a>
            @endif
          </div>
        </div>

        <!-- Metas -->
        <div class="profile-footer">
          <span class="meta"><i class="fa-solid fa-headphones"></i> {{ num_format_sp($listenersCount) }} oyentes mensuales</span>
          <span class="meta"><i class="fa-solid fa-user-group"></i> {{ num_format_sp($followersCount) }} seguidores</span>
        </div>

        <!-- Avatar -->
        <div class="avatar-wrap xl">
          @if($user && $user->avatar)
            <img id="avatarPreviewLive" class="avatar-img"
                 src="{{ drive_img_url($user->avatar, 500) }}&v={{ time() }}"
                 alt="{{ $user->nombre_artistico ?? $user->nombre }}" loading="lazy" decoding="async" fetchpriority="low">
          @else
            <div class="avatar-fallback">{{ strtoupper(substr($user->nombre_artistico ?? $user->nombre ?? 'U',0,1)) }}</div>
          @endif
        </div>
      </div>
    </section>

    <!-- ===== CONTENIDO MÚSICA ===== -->
    <section class="music-layout no-clip">
      <!-- CANCIONES (mantener fondo) -->
      <div class="music-column left-col no-clip">
        <div class="section-title"><h2><i class="fa-solid fa-music"></i> Canciones</h2></div>

        <div class="songs-list fixed-six" id="songsList">
          @foreach($canciones->take(6) as $song)
            @php
              $songTitle = $song->title ?? $song->nombre ?? 'Sin título';
              $songCover = $song->cover_url ?? $song->portada ?? $song->cover_path ?? null;
              $coverUrl  = $songCover ? drive_img_url($songCover, 240) . '&v=' . time() : asset('img/default-cancion.png');

              $rawAudio  = $song->audio_url ?? $song->audio ?? null;
              $audioUrl  = null;
              if ($rawAudio) {
                if (Str::contains($rawAudio, 'drive.google')) {
                  if (preg_match('~/d/([^/]+)~', $rawAudio, $m))      $id = $m[1];
                  elseif (preg_match('~[?&]id=([^&]+)~', $rawAudio, $m)) $id = $m[1];
                  else $id = null;
                  $audioUrl = $id ? route('media.drive', ['id'=>$id]) : $rawAudio;
                } else { $audioUrl = $rawAudio; }
              }
              $dur   = $song->duration ?? $song->duracion ?? '0:00';
            @endphp

            <div class="song-row" data-song-id="{{ $song->id }}">
              <div class="song-index">{{ $loop->iteration }}</div>

              <div class="song-thumb"><img src="{{ $coverUrl }}" alt="Portada" loading="lazy" decoding="async"></div>

              <div class="song-info">
                <h4 class="song-title" title="{{ $songTitle }}">{{ $songTitle }}</h4>
                <div class="song-sub"><span class="artist-name">{{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</span></div>
              </div>

              <div class="song-duration">{{ $dur }}</div>

              <div class="song-actions">
                @auth
                  <form action="{{ route('canciones.like', $song->id) }}" method="POST" class="inline-like" data-song-id="{{ $song->id }}">@csrf
                    <button type="submit" class="icon-chip like-btn" aria-pressed="false" title="Me gusta">
                      <i class="fa-regular fa-heart"></i>
                    </button>
                  </form>
                  <button class="icon-chip add-playlist-btn" title="Agregar a playlist"><i class="fa-solid fa-plus"></i></button>
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
                                  data-action="{{ route('cancion.destroy', $song->id) }}"
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
                        data-action="{{ route('cancion.destroy', $song->id) }}"
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
          <div class="section-title"><h2><i class="fa-solid fa-compact-disc"></i> Álbumes</h2></div>
          <div class="page-label" id="albumsPageLabel"></div>
        </div>

        @php
          $albumsNormalized = $albumes->map(function($a){
            return (object)[
              'id'      => $a->id,
              'titulo'  => $a->titulo ?: ($a->title ?? 'Sin título'),
              'portada' => $a->portada ?? $a->cover_path ?? null,
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
                        $albumCover = $album->portada
                          ? drive_img_url($album->portada, 360) . '&v=' . time()
                          : asset('img/default-album.png');
                      @endphp

                      <div class="card album-card {{ $isOwner ? 'has-trash' : '' }}">
                        <a href="{{ route('album.show', $album->id) }}" class="card-link">
                          <div class="card-img">
                            <img src="{{ $albumCover }}" alt="Portada" loading="lazy" decoding="async">
                            <span class="album-play"><i class="fa-solid fa-play"></i></span>
                          </div>
                          <h4 class="album-title">{{ $album->titulo }}</h4>
                          <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre }}</p>
                        </a>

                        @if($isOwner)
                          <button class="trash-float open-delete"
                                  title="Eliminar álbum"
                                  data-type="album"
                                  data-action="{{ route('album.destroy', $album->id) }}"
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
      $releasesAllUrl = \Illuminate\Support\Facades\Route::has('perfil.releasesAll')
          ? route('perfil.releasesAll', $user->id)
          : url('/perfil/'.$user->id.'/lanzamientos');

      $normalizedReleases = collect($lanzamientos)->map(function($it){
        $tipo   = $it['tipo']   ?? $it->tipo   ?? 'album';
        $titulo = $it['titulo'] ?? $it->titulo ?? $it->title ?? null;
        $cover  = $it['cover']  ?? $it->cover  ?? $it->portada ?? $it->cover_path ?? null;
        $anio   = $it['anio']   ?? $it->anio   ?? (isset($it->created_at) ? optional($it->created_at)->format('Y') : '');
        $titulo = $titulo ?: 'Sin título';
        return ['tipo'=>$tipo,'titulo'=>$titulo,'cover'=>$cover,'anio'=>$anio];
      })
      ->filter(fn($x) => !empty($x['titulo']))
      ->values();

      $relPages = $normalizedReleases->chunk(8); // 4×2 por página
    @endphp

    <section class="releases-section no-clip" id="releasesSection">
      <div class="releases-head">
        <h2><i class="fa-solid fa-bolt"></i> Últimos lanzamientos</h2>
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
                      $rCover = $item['cover_url'] ?? ($item['cover'] ? (function_exists('drive_img_url') ? drive_img_url($item['cover'], 240) : $item['cover']) . '&v=' . time() : asset('img/default-album.png'));
                      $cls = 'card release-card' . ($isOwner ? ' has-trash' : '');
                    @endphp

                    <div class="{{ $cls }}">
                      <div class="card-img">
                        <img src="{{ $rCover }}" alt="Portada" loading="lazy" decoding="async">
                        <span class="type-badge">{{ $item['tipo'] === 'album' ? 'Álbum' : 'Canción' }}</span>
                      </div>
                      <h4 title="{{ $item['titulo'] }}">{{ $item['titulo'] }}</h4>
                      <p>{{ $item['anio'] }}</p>

                      @if($isOwner)
                        <button class="trash-float open-delete"
                                title="Eliminar"
                                data-type="{{ $item['tipo'] === 'album' ? 'album' : 'song' }}"
                                data-action="{{ $item['delete'] ?? '#' }}"
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
        <div class="confirm-media"><img id="confirmCover" alt=""></div>
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
    <div class="modal" id="editModal" aria-hidden="true" role="dialog" aria-modal="true">
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

        <form action="{{ route('perfil.update') }}" method="POST" enctype="multipart/form-data">
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
                  <img id="avatarPreview" src="{{ $user->avatar ? route('media.drive',['id'=>$user->avatar]).'?v='.time() : '' }}" alt="">
                  <input type="file" id="avatarInput" name="avatar" accept="image/*" hidden>
                  <div class="overlay"><i class="fa-solid fa-camera"></i></div>
                </label>
              </div>
              <div class="modal-field col">
                <label>Banner</label>
                <label class="file-preview banner-edit">
                  <img id="bannerPreview" src="{{ $user->banner ? route('media.drive',['id'=>$user->banner]).'?v='.time() : '' }}" alt="">
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
    <div class="modal" id="bioModal" aria-hidden="true" role="dialog" aria-modal="true">
      <div class="modal-content bio">
        <div class="modal-header">
          <h3><i class="fa-solid fa-circle-info"></i> Sobre {{ $user->nombre_artistico ?? $user->nombre ?? 'el artista' }}</h3>
          <button type="button" class="pf-btn pf-btn--ghost pf-btn--sm" id="closeBioTop" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="bio-grid">
          <div class="bio-media">
            @if($user && $user->avatar)
              <img src="{{ drive_img_url($user->avatar, 600) }}&v={{ time() }}" alt="Foto de {{ $user->nombre_artistico ?? $user->nombre }}" loading="lazy" decoding="async">
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

{{-- JS principal por Vite (NO quité nada) --}}
@vite('resources/js/ed_perfil.js')

<!-- ===== Medición dinámica de sidebar y reproductor derecho (igual que tenías) ===== -->
<script>
(function () {
  const rootEl = document.getElementById('page-profile') || document.documentElement;
  const sidebar = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
  const rightPlayer = document.getElementById('rightPlayer');

  function setVar(name, px){
    rootEl.style.setProperty(name, Math.max(0, Math.round(px)) + 'px');
  }
  function measure(){
    if (sidebar) {
      const w = sidebar.getBoundingClientRect().width;
      setVar('--aurp-sb-w', w > 0 ? w : 90);
    } else {
      setVar('--aurp-sb-w', 0);
    }
    if (rightPlayer) {
      const w = rightPlayer.getBoundingClientRect().width;
      setVar('--aurp-rp-w', w > 0 ? w : 360);
    } else {
      setVar('--aurp-rp-w', 0);
    }
  }
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

<!-- (tu snippet extra para abrir/cerrar el modal — lo mantengo para que no se pierda nada) -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  if (window.__extraEditModalWired) return; // evita doble wiring
  window.__extraEditModalWired = true;

  const editBtn = document.getElementById("editBtn");
  const cancelBtn = document.getElementById("cancelEdit");
  const modal = document.getElementById("editModal");

  if (!editBtn || !modal) return;

  editBtn.addEventListener("click", () => {
    modal.setAttribute("aria-hidden", "false");
  });

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      modal.setAttribute("aria-hidden", "true");
    });
  }

  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.setAttribute("aria-hidden", "true");
    }
  });
});
</script>
</body>
</html>
