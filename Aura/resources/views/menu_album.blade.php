{{-- resources/views/menu_album.blade.php --}}
<!doctype html>
<html lang="es">
<head>
<<<<<<< HEAD
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Álbumes — {{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</title>

    <!-- Fuentes y estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite('resources/css/menu_album.css')
=======
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Álbum</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  @vite(['resources/css/menu_album.css','resources/js/menu_album.js'])
>>>>>>> recup-ayer
</head>

@php
  use Illuminate\Support\Str;

  // Esperado: $user, $albumes (collection). $album (opcional).
  $selectedAlbum = $album ?? null;
  if (!$selectedAlbum && isset($albumes) && request()->filled('album')) {
    $selectedAlbum = $albumes->firstWhere('id', (int)request('album'));
  }
  if (!$selectedAlbum && isset($albumes) && $albumes->count()) {
    $selectedAlbum = $albumes->first();
  }

  $isOwner = auth()->check() && isset($user) && auth()->id() === (int)($user->id ?? 0);

  // Helpers de url
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

<body>
<<<<<<< HEAD
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')

    @php
        $bannerHigh = $user->banner ? drive_img_url($user->banner, 1920) . '&v=' . time() : asset('img/default-banner.jpg');
    @endphp

    <main class="main-content">
        <!-- Banner -->
        <div class="user-banner" style="background-image: url('{{ $bannerHigh }}'); margin-top: 90px; padding-top: 150px;">
            <div class="banner-overlay"></div>

            <div class="user-info">
                <h2 class="user-name">{{ $user->nombre_artistico ?? $user->nombre ?? 'Invitado' }}</h2>
                <p class="user-followers">{{ $followersCount ?? 0 }} seguidores</p>
            </div>
        </div>

        <!-- Título flotante debajo del banner -->
        <div class="albums-head" style="margin-top: 20px;">
            <div class="section-title">
                <h2><i class="fa-solid fa-compact-disc"></i> Álbumes</h2>
            </div>
            <div class="page-label" id="albumsPageLabel"></div>
        </div>

        <!-- Contenedor álbumes -->
        <div class="albums-wrap no-clip">
            <!-- Flecha izquierda -->
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

                                        $tracksData = $album->songs->map(function($song) {
                                            return [
                                                'title' => $song->titulo,
                                                'audio_url' => $song->audio_path ?: ''
                                            ];
                                        })->toArray();
                                        $tracksJson = json_encode($tracksData);
                                    @endphp

                                    <div class="card album-card {{ (Auth::check() && Auth::id() === $user->id) ? 'has-trash' : '' }}"
                                         data-title="{{ $album->titulo }}"
                                         data-tracks='{{ $tracksJson }}'>
                                        <div class="card-link">
                                            <div class="card-img">
                                                <img src="{{ $albumCover }}" alt="Portada" loading="lazy" decoding="async">
                                                <span class="album-play"><i class="fa-solid fa-play"></i></span>
                                            </div>
                                            <h4 class="album-title" style="cursor: pointer;">{{ $album->titulo }}</h4>
                                            <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</p>
                                        </div>

                                        @if(Auth::check() && Auth::id() === $user->id)
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

            <!-- Flecha derecha -->
            <button class="albums-arrow right" id="albumsNext" aria-label="Siguiente">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </main>

    @include('components.footer')

    <!-- Modal canciones -->
    <div id="albumModal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <h2 id="modal-album-title"><i class="fa-solid fa-compact-disc"></i> Álbum</h2>
            <ul id="modal-album-tracks"></ul>
        </div>
    </div>

    <!-- Script navegación y modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navegación álbumes
            const track = document.getElementById('albumsTrack');
            const prev = document.getElementById('albumsPrev');
            const next = document.getElementById('albumsNext');
            const label = document.getElementById('albumsPageLabel');
            if (track) {
                let page = 0, pages = parseInt(track.dataset.pages || '0', 10);
                function update() {
                    track.style.transform = `translateX(-${page * 100}%)`;
                    prev.disabled = (page === 0);
                    next.disabled = (page >= pages - 1);
                    label.textContent = pages ? `Página ${page + 1} de ${pages}` : '';
                }
                prev?.addEventListener('click', () => { if (page > 0) { page--; update(); } });
                next?.addEventListener('click', () => { if (page < pages - 1) { page++; update(); } });
                update();
            }

            // Modal canciones
            const modal = document.getElementById('albumModal');
            const modalTitle = document.getElementById('modal-album-title');
            const modalTracks = document.getElementById('modal-album-tracks');
            const modalClose = modal.querySelector('.modal-close');

            if (modal && modalTitle && modalTracks && modalClose) {
                // Modal para el título principal "Álbumes"
                const albumsTitle = document.querySelector('.section-title h2');
                if (albumsTitle) {
                    albumsTitle.addEventListener('click', (e) => {
                        e.preventDefault();
                        modalTitle.innerHTML = `<i class="fa-solid fa-compact-disc"></i> Canciones de Álbumes que te gustaron`;
                        modalTracks.innerHTML = "";

                        // Obtener todas las canciones de álbumes que el usuario ha marcado con "like"
                        // Aquí asumimos que hay un endpoint o variable JS que contiene estas canciones
                        // Como no tenemos acceso directo, mostraremos un mensaje placeholder

                        // TODO: Reemplazar con la lógica real para obtener canciones liked por el usuario
                        

                        if (likedSongsData.length > 0) {
                            likedSongsData.forEach((song, index) => {
                                const title = song.title || song.titulo || 'Canción';
                                const albumTitle = song.album ? song.album.titulo : 'Álbum desconocido';
                                const li = document.createElement('li');
                                const audioUrl = song.audio_path || song.audio_url || '';
                                li.innerHTML = `<span>${index + 1}. ${title} - <em>${albumTitle}</em></span> <i class="fa-solid fa-play play-icon" data-title="${title} - ${albumTitle}" data-audio="${audioUrl}"></i>`;
                                modalTracks.appendChild(li);
                            });
                        } else {
                            const li = document.createElement('li');
                            li.innerHTML = `<span>No tienes canciones de álbumes que te gustaron</span>`;
                            modalTracks.appendChild(li);
                        }

                        modal.classList.add('active');
                    });
                }

                // Modal para imágenes de álbumes (click en la portada)
                document.querySelectorAll('.album-card .card-img').forEach(imgElement => {
                    imgElement.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const card = imgElement.closest('.album-card');
                        const title = card.getAttribute('data-title');
                        const tracks = JSON.parse(card.getAttribute('data-tracks') || '[]');

                        modalTitle.innerHTML = `<i class="fa-solid fa-compact-disc"></i> ${title}`;
                        modalTracks.innerHTML = "";

                        if (tracks.length > 0) {
                            tracks.forEach((track, i) => {
                                const li = document.createElement('li');
                                const songTitle = track.title || track.titulo || 'Canción';
                                const audioUrl = track.audio_url || track.audio_path || '';
                                li.innerHTML = `<span>${i+1}. ${songTitle}</span> <i class="fa-solid fa-play play-icon" data-title="${songTitle}" data-audio="${audioUrl}"></i>`;
                                modalTracks.appendChild(li);
                            });
                        } else {
                            const li = document.createElement('li');
                            li.innerHTML = `<span>No hay canciones disponibles</span>`;
                            modalTracks.appendChild(li);
                        }

                        modal.classList.add('active');
                    });
                });

                modalClose.addEventListener('click', () => modal.classList.remove('active'));
                modal.addEventListener('click', e => {
                    if (e.target === modal) modal.classList.remove('active');
                });

                // Reproducir canción al hacer click en el icono play dentro del modal
                let currentAudio = null;

                document.addEventListener('click', (e) => {
                    if (e.target.classList.contains('play-icon')) {
                        const songTitle = e.target.getAttribute('data-title');
                        const audioUrl = e.target.getAttribute('data-audio');

                        // Detener audio anterior si existe
                        if (currentAudio) {
                            currentAudio.pause();
                            currentAudio.currentTime = 0;
                        }

                        if (audioUrl && audioUrl.trim() !== '') {
                            try {
                                // Crear nuevo elemento de audio
                                currentAudio = new Audio(audioUrl);

                                // Manejar eventos de audio
                                currentAudio.addEventListener('loadstart', () => {
                                    console.log(`Cargando canción: ${songTitle}`);
                                });

                                currentAudio.addEventListener('canplay', () => {
                                    console.log(`Canción lista para reproducir: ${songTitle}`);
                                });

                                currentAudio.addEventListener('play', () => {
                                    console.log(`Reproduciendo: ${songTitle}`);
                                    // Cambiar icono a pausa
                                    e.target.className = 'fa-solid fa-pause play-icon';
                                    e.target.setAttribute('data-playing', 'true');
                                });

                                currentAudio.addEventListener('pause', () => {
                                    console.log(`Pausado: ${songTitle}`);
                                    // Cambiar icono a play
                                    e.target.className = 'fa-solid fa-play play-icon';
                                    e.target.removeAttribute('data-playing');
                                });

                                currentAudio.addEventListener('ended', () => {
                                    console.log(`Canción terminada: ${songTitle}`);
                                    // Resetear icono
                                    e.target.className = 'fa-solid fa-play play-icon';
                                    e.target.removeAttribute('data-playing');
                                });

                                currentAudio.addEventListener('error', (error) => {
                                    console.error(`Error al reproducir ${songTitle}:`, error);
                                    alert(`Error al reproducir la canción "${songTitle}". Verifica que el archivo de audio exista.`);
                                });

                                // Reproducir la canción
                                currentAudio.play().catch(error => {
                                    console.error('Error al intentar reproducir:', error);
                                    alert(`No se pudo reproducir la canción "${songTitle}". Puede que el navegador bloquee la reproducción automática.`);
                                });

                            } catch (error) {
                                console.error('Error al crear elemento de audio:', error);
                                alert(`Error al intentar reproducir la canción "${songTitle}".`);
                            }
                        } else {
                            alert(`La canción "${songTitle}" no tiene un archivo de audio disponible.`);
                        }
                    }
                });
            }
        });
    </script>
=======
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
              <a class="btn btn--primary" href="{{ route('musica.subir') }}"><i class="fa-solid fa-upload"></i> Subir música</a>
            @endif
            @if(Route::has('perfil.show') && isset($user))
              <a class="btn btn--ghost" href="{{ route('perfil.show', $user->id) }}"><i class="fa-solid fa-user"></i> Volver al perfil</a>
            @endif
          </div>
        </header>

        <div class="ma-grid">
          {{-- ================== IZQUIERDA ================== --}}
          <aside class="ma-left">
            <h2 class="sec-ttl"><i class="fa-solid fa-compact-disc"></i> Álbum</h2>

            @if($selectedAlbum)
              @php
                $alTitle  = $selectedAlbum->title ?? $selectedAlbum->titulo ?? 'Álbum sin título';
                $alCoverR = $selectedAlbum->cover_path ?? $selectedAlbum->portada ?? null;
                $alCover  = $imgUrl($alCoverR, asset('img/default-album.png'));
                $songs    = $selectedAlbum->songs ?? collect();
                if (is_array($songs)) $songs = collect($songs);

                // Rutas “seguras”
                $albumUpdateUrl  = Route::has('albums.update') ? route('albums.update', $selectedAlbum->id) : url('/albums/'.$selectedAlbum->id);
                $destroyAlbumUrl = Route::has('profile.albums.destroy') ? route('profile.albums.destroy', $selectedAlbum->id) : url('/albums/'.$selectedAlbum->id.'/delete');

                // Para dataset (sin closures en @json)
                $songsForJs = [];
                foreach ($songs as $s) {
                  $songsForJs[] = [
                    'id'       => $s->id,
                    'title'    => $s->title ?? $s->titulo ?? 'Sin título',
                    'artist'   => (optional($s->user)->nombre_artistico
                                  ?? optional($s->user)->nombre
                                  ?? optional($selectedAlbum->user)->nombre_artistico
                                  ?? optional($selectedAlbum->user)->nombre
                                  ?? 'Artista'),
                    'cover'    => $imgUrl($s->cover_path ?? $s->portada ?? $alCoverR, asset('img/default-cover.jpg')),
                    'audio'    => $audioUrl($s->audio_path ?? $s->ruta_audio ?? $s->file_url ?? ''),
                    'duration' => (int)($s->duration ?? 0),
                  ];
                }
              @endphp

              <section class="card cover-card"
                       id="albumPanel"
                       data-album-id="{{ $selectedAlbum->id }}"
                       data-update-url="{{ $albumUpdateUrl }}">
                <div class="cover-wrap">
                  <img id="albumCoverImg"
                       src="{{ $alCover }}"
                       alt="Portada de {{ $alTitle }}"
                       onerror="this.onerror=null;this.src='{{ asset('img/default-album.png') }}'">
                  @if($isOwner)
                    <button class="chip chip--overlay" id="btnChangeCover" title="Cambiar portada">
                      <i class="fa-solid fa-camera"></i> Cambiar portada
                    </button>
                    <input type="file" id="albumCoverInput" accept="image/*" hidden>
                  @endif
                </div>

                <div class="meta">
                  <label class="lbl">Nombre del álbum</label>
                  <div class="inline-edit" id="albumTitleInline">
                    <input class="txt" id="albumTitleInput"
                           value="{{ $alTitle }}"
                           data-original="{{ $alTitle }}"
                           {{ $isOwner ? '' : 'readonly' }}>
                    @if($isOwner)
                      <button class="chip" id="btnSaveTitle" title="Guardar nombre"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                      <span class="save-state" id="albumTitleState" aria-live="polite"></span>
                    @endif
                  </div>
                </div>

                <div class="actions">
                  @if($isOwner)
                    <form class="inline" method="POST" action="{{ $destroyAlbumUrl }}"
                          onsubmit="return confirm('¿Eliminar el álbum «{{ $alTitle }}» y sus canciones?');">
                      @csrf @method('DELETE')
                      <button class="btn btn--danger"><i class="fa-solid fa-trash"></i> Eliminar</button>
                    </form>
                  @endif

                  <button class="btn btn--ghost" id="btnAddQueue"><i class="fa-solid fa-list"></i> Añadir a cola</button>
                  <button class="btn btn--ghost" id="btnLikeAll"><i class="fa-regular fa-heart"></i> Dar like a todas</button>
                </div>

                {{-- dataset con canciones para JS --}}
                <div id="albumSongsData" data-songs='@json($songsForJs)'></div>
              </section>
            @else
              <div class="empty">
                <i class="fa-solid fa-compact-disc"></i>
                <p>No hay álbum seleccionado.</p>
              </div>
            @endif

            {{-- Otros álbumes (switch rápido) --}}
            @if(isset($albumes) && $albumes->count() > 1)
              <h3 class="sec-ttl sub"><i class="fa-solid fa-layer-group"></i> Otros álbumes</h3>
              <div class="mini-list">
                @foreach($albumes as $a)
                  @continue($selectedAlbum && $a->id === $selectedAlbum->id)
                  @php
                    $t = $a->title ?? $a->titulo ?? 'Álbum';
                    $c = $imgUrl($a->cover_path ?? $a->portada ?? null, asset('img/default-album.png'));
                    $href = route('menu_album', ['album'=>$a->id]);
                  @endphp
                  <a class="mini" href="{{ $href }}">
                    <img src="{{ $c }}" alt=""><span class="ellip">{{ $t }}</span>
                  </a>
                @endforeach
              </div>
            @endif
          </aside>

          {{-- ================== DERECHA ================== --}}
          <section class="ma-right">
            <h2 class="sec-ttl"><i class="fa-solid fa-music"></i> Canciones del álbum</h2>

            @php
              $likeStateUrl = Route::has('canciones.liked') ? route('canciones.liked', 0) : url('/canciones/0/liked');
              $likeToggleUrl = Route::has('api.canciones.like.toggle') ? route('api.canciones.like.toggle', 0)
                              : (Route::has('canciones.like.toggle') ? route('canciones.like.toggle', 0) : url('/api/canciones/0/like/toggle'));
              $songDeleteUrl = Route::has('cancion.destroy') ? route('cancion.destroy', 0) : url('/cancion/0');
              $songUpdateUrl = Route::has('canciones.update') ? route('canciones.update', 0)
                              : (Route::has('cancion.update') ? route('cancion.update', 0) : url('/canciones/0'));
            @endphp

            @if($selectedAlbum)
              @php
                $songs = $selectedAlbum->songs ?? collect();
                if (is_array($songs)) $songs = collect($songs);
              @endphp

              <table class="tbl" id="songsTable"
                     data-like-state="{{ $likeStateUrl }}"
                     data-like-toggle="{{ $likeToggleUrl }}"
                     data-song-delete="{{ $songDeleteUrl }}"
                     data-song-update="{{ $songUpdateUrl }}">
                <thead>
                  <tr>
                    <th class="col-idx">#</th>
                    <th class="col-title">Título</th>
                    <th class="col-artist">Artista</th>
                    <th class="col-dur"><i class="fa-regular fa-clock"></i></th>
                    <th class="col-act">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($songs as $i => $s)
                    @php
                      $sid     = $s->id;
                      $stitle  = $s->title ?? $s->titulo ?? 'Sin título';
                      $sartist = optional($s->user)->nombre_artistico ?? optional($s->user)->nombre
                                ?? optional($selectedAlbum->user)->nombre_artistico ?? optional($selectedAlbum->user)->nombre ?? 'Artista';
                      $scoverR = $s->cover_path ?? $s->portada ?? ($selectedAlbum->cover_path ?? $selectedAlbum->portada ?? null);
                      $scover  = $imgUrl($scoverR, asset('img/default-cover.jpg'));
                      $saudioR = $s->audio_path ?? $s->ruta_audio ?? $s->file_url ?? '';
                      $saudio  = $audioUrl($saudioR);
                      $sDur    = (int)($s->duration ?? 0);
                      $sDurTxt = $sDur > 0
                        ? ($sDur >= 3600 ? sprintf('%d:%02d:%02d', intdiv($sDur,3600), intdiv($sDur%3600,60), $sDur%60)
                                         : sprintf('%d:%02d', intdiv($sDur,60), $sDur%60))
                        : '--:--';
                    @endphp

                    <tr class="row"
                        data-id="{{ $sid }}"
                        data-title="{{ $stitle }}"
                        data-artist="{{ $sartist }}"
                        data-cover="{{ $scover }}"
                        data-audio="{{ $saudio }}"
                        data-duration="{{ $sDur }}">
                      <td class="col-idx">
                        <button class="play-mini" title="Reproducir"><i class="fa-solid fa-play"></i></button>
                        <span class="idx">{{ $i+1 }}</span>
                        <img class="thumb" src="{{ $scover }}" alt="">
                      </td>

                      <td class="col-title">
                        <div class="inline-edit">
                          <input class="txt song-title"
                                 value="{{ $stitle }}"
                                 data-original="{{ $stitle }}"
                                 {{ $isOwner ? '' : 'readonly' }}>
                          @if($isOwner)
                            <button class="chip save-song" title="Guardar"><i class="fa-solid fa-floppy-disk"></i></button>
                            <span class="save-state song-state" aria-live="polite"></span>
                          @endif
                        </div>
                        <div class="sub ellip">{{ $sartist }}</div>
                      </td>

                      <td class="col-artist ellip">{{ $sartist }}</td>
                      <td class="col-dur"><span class="dur">{{ $sDurTxt }}</span></td>

                      <td class="col-act">
                        <button class="chip like-song" title="Me gusta"><i class="fa-regular fa-heart"></i></button>
                        <button class="chip add-queue" title="Añadir a cola"><i class="fa-solid fa-list"></i></button>
                        @if($isOwner)
                          <button class="chip delete-song danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="5">
                      <div class="empty"><i class="fa-solid fa-music"></i><p>Este álbum no tiene canciones.</p></div>
                    </td></tr>
                  @endforelse
                </tbody>
              </table>
            @endif
          </section>
        </div>
      </div>
    </main>

    {{-- Modal de confirmación de cambios (in-page) --}}
    <div id="changesModal" class="modal" aria-hidden="true">
      <div class="modal__backdrop"></div>
      <div class="modal__card" role="dialog" aria-modal="true" aria-labelledby="chgTitle">
        <h3 id="chgTitle">Tienes cambios sin guardar</h3>
        <p class="modal__desc">¿Deseas guardar los siguientes cambios antes de salir?</p>
        <ul id="changesList" class="modal__list"></ul>
        <div class="modal__actions">
          <button class="btn btn--ghost" id="btnDiscard">Cancelar</button>
          <button class="btn btn--primary" id="btnSaveAndLeave"><i class="fa-solid fa-floppy-disk"></i> Guardar y salir</button>
        </div>
      </div>
    </div>

    @include('components.footer')
  </div>
</div>
>>>>>>> recup-ayer
</body>
</html>
