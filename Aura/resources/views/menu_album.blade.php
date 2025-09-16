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

  @vite(['resources/css/menu_album.css','resources/js/menu_album.js'])
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
</body>
</html>
