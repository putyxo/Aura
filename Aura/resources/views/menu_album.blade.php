{{-- resources/views/menu_album.blade.php --}}
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Álbum</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- Importa sólo para esta vista; selectors namespaced para no chocar --}}
  @vite(['resources/css/menu_album.css'])
  @vite(['resources/js/app.js'])
</head>

@php
  use Illuminate\Support\Str;

  // Espera: $album con ->user y ->songs cargados por el controlador
  $selectedAlbum = $album ?? null;
  $user          = $user ?? optional($selectedAlbum)->user;

  $isAuth  = auth()->check();
  $isOwner = $isAuth && isset($user) && auth()->id() === (int)($user->id ?? 0);
  $loginUrl = (Route::has('login') ? route('login') : url('/login'));

  // Helpers de URL seguros (aceptan http/https o rutas locales de storage)
  $imgUrl = function ($raw, $fallback) {
    if (!$raw) return $fallback;
    return Str::startsWith($raw, ['http://','https://','/storage/'])
      ? $raw
      : asset('storage/'.ltrim($raw,'/'));
  };
  $audioUrl = function ($raw) {
    if (!$raw) return '';
    return Str::startsWith($raw, ['http://','https://','/storage/'])
      ? $raw
      : asset('storage/'.ltrim($raw,'/'));
  };
@endphp

<body data-page="menu-album">
<div class="page">
  <div class="with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    <main class="main-content ma-page" data-owner="{{ $isOwner ? 1 : 0 }}">
      <div class="ma-shell">

        <header class="ma-head">
          <div class="ma-title">
            <h1>Álbum</h1>
            @isset($user)
              <p class="ma-sub">Artista: <strong>{{ $user->nombre_artistico ?? $user->nombre ?? '—' }}</strong></p>
            @endisset
          </div>

          <div class="ma-actions">
            @if($isOwner && Route::has('musica.subir'))
              <a class="ma-btn ma-btn--primary" href="{{ route('musica.subir') }}"><i class="fa-solid fa-upload"></i> Subir música</a>
            @endif
            @if(Route::has('perfil.show') && isset($user))
              <a class="ma-btn ma-btn--ghost guard-link" href="{{ route('perfil.show', $user->id) }}"><i class="fa-solid fa-user"></i> Volver al perfil</a>
            @endif
          </div>
        </header>

        <div class="ma-grid">
          {{-- ================== IZQUIERDA ================== --}}
          <aside class="ma-left">
            <h2 class="ma-sec-ttl"><i class="fa-solid fa-compact-disc"></i> Álbum</h2>

            @if($selectedAlbum)
              @php
                $alTitle   = $selectedAlbum->title ?? $selectedAlbum->titulo ?? 'Álbum sin título';

                // Portada del álbum (soporta cover_url/cover_path/portada) + bust de caché
                $alCoverRaw = $selectedAlbum->cover_url
                           ?? $selectedAlbum->cover_path
                           ?? $selectedAlbum->portada
                           ?? null;
                $alCoverBase = $imgUrl($alCoverRaw, asset('img/default-album.png'));
                $alCover     = $alCoverBase.'?v='.(optional($selectedAlbum->updated_at)->timestamp ?? 0);

                $songs     = $selectedAlbum->songs ?? collect();
                if (is_array($songs)) $songs = collect($songs);

                // Rutas actualización/eliminación
                $albumUpdateUrl  = Route::has('albums.update') ? route('albums.update', $selectedAlbum->id) : url('/albums/'.$selectedAlbum->id);
                $destroyAlbumUrl = Route::has('profile.albums.destroy') ? route('profile.albums.destroy', $selectedAlbum->id) : url('/albums/'.$selectedAlbum->id);

                // Para botón “like a todas” calculamos si todas están likeadas
                $allLiked = false;
                if ($isAuth && $songs->count()) {
                  $allLiked = $songs->every(function($s){
                    return method_exists($s,'isLikedBy') ? $s->isLikedBy(auth()->user()) : false;
                  });
                }

                // Dataset canciones para JS (mismas reglas de portada que la tabla)
                $songsForJs = [];
                foreach ($songs as $s) {
                  $songCoverRaw = $s->cover_url
                               ?? $s->cover_path
                               ?? $s->portada
                               ?? $alCoverRaw;

                  $songCover = $imgUrl($songCoverRaw, $alCoverBase ?: asset('img/default-cancion.png'));
                  $songCover = $songCover.'?v='.(optional($s->updated_at)->timestamp ?? (optional($selectedAlbum->updated_at)->timestamp ?? 0));

                  $songsForJs[] = [
                    'id'       => $s->id,
                    'title'    => $s->title ?? $s->titulo ?? 'Sin título',
                    'artist'   => (optional($s->user)->nombre_artistico
                                  ?? optional($s->user)->nombre
                                  ?? optional($selectedAlbum->user)->nombre_artistico
                                  ?? optional($selectedAlbum->user)->nombre
                                  ?? 'Artista'),
                    'cover'    => $songCover,
                    'audio'    => $audioUrl($s->audio_url ?? $s->audio_path ?? $s->ruta_audio ?? $s->file_url ?? ''),
                    'duration' => (int)($s->duration ?? 0),
                  ];
                }
              @endphp

              <section class="ma-card ma-cover-card"
                       id="albumPanel"
                       data-album-id="{{ $selectedAlbum->id }}"
                       data-update-url="{{ $albumUpdateUrl }}">
                <div class="ma-cover-wrap">
                  <img id="albumCoverImg"
                       src="{{ $alCover }}"
                       alt="Portada de {{ $alTitle }}"
                       loading="lazy" decoding="async"
                       onerror="this.onerror=null;this.src='{{ asset('img/default-album.png') }}'">

                  @if($isOwner)
                    <button class="ma-chip ma-chip--overlay" id="btnChangeCover" title="Cambiar portada">
                      <i class="fa-solid fa-camera"></i> Cambiar portada
                    </button>
                    <input type="file" id="albumCoverInput" accept="image/*" hidden>
                  @endif
                </div>

                <div class="ma-meta">
                  <label class="ma-lbl">Nombre del álbum</label>

                  @if($isOwner)
                    <div class="ma-inline-edit" id="albumTitleInline">
                      <input class="ma-txt" id="albumTitleInput"
                             value="{{ $alTitle }}"
                             data-original="{{ $alTitle }}">
                      <button class="ma-chip ma-chip--save" id="btnSaveTitle" title="Guardar nombre">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                      </button>
                      <span class="ma-save-state" id="albumTitleState" aria-live="polite"></span>
                    </div>
                  @else
                    <div class="ma-title-static ma-ellip" title="{{ $alTitle }}">{{ $alTitle }}</div>
                  @endif
                </div>

                <div class="ma-actions">
                  @if($isOwner)
                    <form class="ma-inline" method="POST" action="{{ $destroyAlbumUrl }}"
                          onsubmit="return confirm('¿Eliminar el álbum «{{ $alTitle }}» y sus canciones?');">
                      @csrf @method('DELETE')
                      <button class="ma-btn ma-btn--danger"><i class="fa-solid fa-trash"></i> Eliminar</button>
                    </form>
                  @endif

                  <button class="ma-btn ma-btn--ghost" id="btnAddQueue"><i class="fa-solid fa-list"></i> Añadir a cola</button>

                  @if($isAuth)
                    <button class="ma-btn ma-btn--ghost" id="btnLikeAll" data-mode="{{ $allLiked ? 'unlike' : 'like' }}">
                      @if($allLiked)
                        <i class="fa-solid fa-heart"></i> Quitar like a todas
                      @else
                        <i class="fa-regular fa-heart"></i> Dar like a todas
                      @endif
                    </button>
                  @else
                    <a href="{{ $loginUrl }}" class="ma-btn ma-btn--ghost"><i class="fa-regular fa-heart"></i> Dar like a todas</a>
                  @endif
                </div>

                {{-- dataset con canciones para JS --}}
                <div id="albumSongsData" data-songs='@json($songsForJs)'></div>
              </section>
            @else
              <div class="ma-empty">
                <i class="fa-solid fa-compact-disc"></i>
                <p>No hay álbum seleccionado.</p>
              </div>
            @endif
          </aside>

          {{-- ================== DERECHA ================== --}}
          <section class="ma-right">
            <h2 class="ma-sec-ttl"><i class="fa-solid fa-music"></i> Canciones del álbum</h2>

            @php
              // URLs que consume el JS
              $likeToggleUrl  = Route::has('api.canciones.like.toggle') ? route('api.canciones.like.toggle', 0)
                                : (Route::has('canciones.like') ? route('canciones.like', 0) : url('/canciones/0/like'));
              $songDeleteUrl  = Route::has('cancion.destroy') ? route('cancion.destroy', 0) : url('/cancion/0');
              $songUpdateUrl  = Route::has('canciones.update') ? route('canciones.update', 0)
                                : (Route::has('cancion.update') ? route('cancion.update', 0) : url('/canciones/0'));
            @endphp

            @if($selectedAlbum)
              @php
                $songs = $selectedAlbum->songs ?? collect();
                if (is_array($songs)) $songs = collect($songs);

                // Prepara portada base del álbum para fallbacks y onerror
                $albumCoverRaw  = $selectedAlbum->cover_url
                               ?? $selectedAlbum->cover_path
                               ?? $selectedAlbum->portada
                               ?? null;
                $albumCoverBase = $imgUrl($albumCoverRaw, asset('img/default-album.png'));
                $albumCoverV    = $albumCoverBase.'?v='.(optional($selectedAlbum->updated_at)->timestamp ?? 0);
              @endphp

              <table class="ma-tbl" id="songsTable"
                     data-like-toggle="{{ $likeToggleUrl }}"
                     data-song-delete="{{ $songDeleteUrl }}"
                     data-song-update="{{ $songUpdateUrl }}">
                <thead>
                  <tr>
                    <th class="ma-col-idx">#</th>
                    <th class="ma-col-title">Título</th>
                    <th class="ma-col-artist">Artista</th>
                    <th class="ma-col-dur"><i class="fa-regular fa-clock"></i></th>
                    <th class="ma-col-act">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($songs as $i => $s)
                    @php
                      $sid     = $s->id;
                      $stitle  = $s->title ?? $s->titulo ?? 'Sin título';
                      $sartist = optional($s->user)->nombre_artistico ?? optional($s->user)->nombre
                                ?? optional($selectedAlbum->user)->nombre_artistico ?? optional($selectedAlbum->user)->nombre ?? 'Artista';

                      // Portada canción: cover_url > cover_path > portada > portada del álbum
                      $scoverRaw = $s->cover_url
                                ?? $s->cover_path
                                ?? $s->portada
                                ?? $albumCoverRaw;

                      $scoverBase = $imgUrl($scoverRaw, $albumCoverBase ?: asset('img/default-cancion.png'));
                      $scoverV    = $scoverBase.'?v='.(optional($s->updated_at)->timestamp ?? (optional($selectedAlbum->updated_at)->timestamp ?? 0));

                      // Audio
                      $saudioR = $s->audio_url ?? $s->audio_path ?? $s->ruta_audio ?? $s->file_url ?? '';
                      $saudio  = $audioUrl($saudioR);

                      // Duración
                      $sDur    = (int)($s->duration ?? 0);
                      $sDurTxt = $sDur > 0
                        ? ($sDur >= 3600 ? sprintf('%d:%02d:%02d', intdiv($sDur,3600), intdiv($sDur%3600,60), $sDur%60)
                                         : sprintf('%d:%02d', intdiv($sDur,60), $sDur%60))
                        : '--:--';

                      // Estado like inicial
                      $isLiked = $isAuth && method_exists($s,'isLikedBy') ? $s->isLikedBy(auth()->user()) : false;

                      // Acción like (reutilizamos rutas conocidas)
                      $likeAction = Route::has('canciones.like')
                          ? route('canciones.like', $sid)
                          : (Route::has('cancion.like') ? route('cancion.like', $sid) : null);
                    @endphp

                    <tr class="ma-row"
                        role="button" tabindex="0"
                        aria-label="Reproducir {{ $stitle }}"
                        data-id="{{ $sid }}"
                        data-title="{{ $stitle }}"
                        data-artist="{{ $sartist }}"
                        data-cover="{{ $scoverV }}"
                        data-audio="{{ $saudio }}"
                        data-duration="{{ $sDur }}"
                        data-liked="{{ $isLiked ? 1 : 0 }}">
                      <td class="ma-col-idx">
                        <button class="ma-play-mini" title="Reproducir"><i class="fa-solid fa-play"></i></button>
                        <span class="ma-idx">{{ $i+1 }}</span>
                        <img class="ma-thumb"
                             src="{{ $scoverV }}"
                             alt="Portada"
                             width="64" height="64"
                             loading="lazy" decoding="async"
                             onerror="this.onerror=null;this.src='{{ $albumCoverV }}'">
                      </td>

                      <td class="ma-col-title">
                        @if($isOwner)
                          <div class="ma-inline-edit">
                            <input class="ma-txt ma-song-title"
                                   value="{{ $stitle }}"
                                   data-original="{{ $stitle }}">
                            <button class="ma-chip ma-chip--save ma-save-song" title="Guardar"><i class="fa-solid fa-floppy-disk"></i></button>
                            <span class="ma-save-state ma-song-state" aria-live="polite"></span>
                          </div>
                        @else
                          <div class="ma-title-static ma-ellip" title="{{ $stitle }}">{{ $stitle }}</div>
                          <div class="ma-subtext ma-ellip">{{ $sartist }}</div>
                        @endif
                      </td>

                      <td class="ma-col-artist ma-ellip">{{ $sartist }}</td>
                      <td class="ma-col-dur"><span class="ma-dur">{{ $sDurTxt }}</span></td>

                      <td class="ma-col-act">
                        @auth
                          @if($likeAction)
                            <form action="{{ $likeAction }}" method="POST"
                                  class="ma-inline-like"
                                  data-song-id="{{ $sid }}">
                              @csrf
                              <button type="button"
                                      class="ma-chip like-song {{ $isLiked ? 'is-liked' : '' }}"
                                      aria-pressed="{{ $isLiked ? 'true' : 'false' }}"
                                      title="Me gusta">
                                <i class="fa-{{ $isLiked ? 'solid' : 'regular' }} fa-heart"></i>
                              </button>
                            </form>
                          @else
                            <button type="button" class="ma-chip like-song" title="Me gusta" disabled>
                              <i class="fa-regular fa-heart"></i>
                            </button>
                          @endif

                          <button class="ma-chip add-queue" title="Añadir a cola"><i class="fa-solid fa-list"></i></button>

                          @if($isOwner)
                            <button class="ma-chip delete-song danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                          @endif
                        @else
                          <a href="{{ $loginUrl }}" class="ma-chip" title="Inicia sesión para dar Me gusta"><i class="fa-regular fa-heart"></i></a>
                          <a href="{{ $loginUrl }}" class="ma-chip" title="Inicia sesión para usar la cola"><i class="fa-solid fa-list"></i></a>
                        @endauth
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="5">
                      <div class="ma-empty"><i class="fa-solid fa-music"></i><p>Este álbum no tiene canciones.</p></div>
                    </td></tr>
                  @endforelse
                </tbody>
              </table>
            @endif
          </section>
        </div>
      </div>
    </main>

    @include('components.footer')
  </div>
</div>
</body>
</html>
