<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Perfil — {{ $user->nombre_artistico ?? 'Invitado' }} · Aura</title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  @vite('resources/css/ed_perfil.css')

  @php
    use Illuminate\Support\Str;

    $bannerLow  = $user->banner ? drive_img_url($user->banner, 640)  . '&v=' . time() : asset('img/default-banner.jpg');
    $bannerHigh = $user->banner ? drive_img_url($user->banner, 1920) . '&v=' . time() : asset('img/default-banner.jpg');

    $followersCount = method_exists($user,'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0);
    $listenersCount = (int)($user->oyentes_mensuales ?? 0);

    if (!function_exists('num_format_sp')) {
      function num_format_sp($n){ return is_numeric($n) ? number_format((float)$n,0,',','.') : $n; }
    }
  @endphp

  <link rel="preload" as="image" href="{{ $bannerLow }}">
</head>
<body>
<div id="page-profile"><!-- SCOPING para aislar estilos del perfil -->

  @include('components.sidebar')
  @include('components.traductor')
  @include('components.header')
    @include('components.fondo')

  <main class="main-content">

    <!-- ===== HERO PERFIL ===== -->
    <section class="profile-hero">
      <div class="profile-banner" data-hires="{{ $bannerHigh }}" style="background-image:url('{{ $bannerLow }}')"></div>
      <div class="banner-overlay"></div>

      @if(Auth::check() && Auth::id() === $user->id)
        <div class="edit-btn">
          <button class="btn btn-primary" id="editBtn"><i class="fa-solid fa-pen"></i><span>Editar</span></button>
        </div>
      @endif

      <div class="profile-header">
        <h1 id="artistName">{{ $user->nombre_artistico ?? 'Artista' }}</h1>

        <!-- Seguir/Dejar de seguir bajo el nombre -->
        <div class="follow-under-name">
          @auth
            @if(Auth::id() !== $user->id)
              @php
                $hasToggle = \Illuminate\Support\Facades\Route::has('follow.toggle');
                $isFollowing = Auth::user()->isFollowing($user->id);
              @endphp
              @if($hasToggle)
                <form action="{{ route('follow.toggle', $user->id) }}" method="POST">@csrf
                  <button type="submit" class="btn follow-pill">
                    <i class="fa-solid {{ $isFollowing ? 'fa-user-minus' : 'fa-user-plus' }}"></i>
                    {{ $isFollowing ? 'Dejar de seguir' : 'Seguir' }}
                  </button>
                </form>
              @else
                @if($isFollowing)
                  <form action="{{ route('perfil.unfollow', $user->id) }}" method="POST">@csrf
                    <button type="submit" class="btn follow-pill"><i class="fa-solid fa-user-minus"></i> Dejar de seguir</button>
                  </form>
                @else
                  <form action="{{ route('perfil.follow', $user->id) }}" method="POST">@csrf
                    <button type="submit" class="btn follow-pill"><i class="fa-solid fa-user-plus"></i> Seguir</button>
                  </form>
                @endif
              @endif
            @endif
          @endauth
        </div>
      </div>

      <div class="profile-footer">
        <span class="meta"><i class="fa-solid fa-headphones"></i> {{ num_format_sp($listenersCount) }} oyentes mensuales</span>
        <span class="meta"><i class="fa-solid fa-user-group"></i> {{ num_format_sp($followersCount) }} seguidores</span>
      </div>

      <div class="avatar-wrap xl">
        @if($user && $user->avatar)
          <img id="avatarPreviewLive" class="avatar-img"
               src="{{ drive_img_url($user->avatar, 500) }}&v={{ time() }}"
               alt="{{ $user->nombre_artistico ?? $user->nombre }}" loading="lazy" decoding="async">
        @else
          <div class="avatar-fallback">{{ strtoupper(substr($user->nombre_artistico ?? $user->nombre ?? 'U',0,1)) }}</div>
        @endif
      </div>
    </section>

    <!-- ===== CONTENIDO MÚSICA ===== -->
    <section class="music-layout no-clip">
      <!-- CANCIONES -->
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

              <div class="song-thumb">
                <img src="{{ $coverUrl }}" alt="Portada" loading="lazy" decoding="async">
              </div>

              <div class="song-info">
                <h4 class="song-title" title="{{ $songTitle }}">{{ $songTitle }}</h4>
                <div class="song-sub"><span class="artist-name">{{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</span></div>
              </div>

              <div class="song-duration">{{ $dur }}</div>

              <div class="song-actions">
                <form action="{{ route('canciones.like', $song->id) }}" method="POST" class="inline-like">@csrf
                  <button type="submit" class="icon-chip" title="Me gusta"><i class="fa-regular fa-heart"></i></button>
                </form>
                <button class="icon-chip" title="Agregar a playlist"><i class="fa-solid fa-plus"></i></button>

                <div class="menu-wrap">
                  <button class="icon-chip more-btn" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-ellipsis"></i></button>
                  <ul class="kebab-menu">
                    <li><button class="menu-item" data-action="queue"><i class="fa-solid fa-list-ol"></i> Añadir a cola</button></li>
                    <li><button class="menu-item" data-action="playlist"><i class="fa-solid fa-square-plus"></i> Agregar a playlist</button></li>
                    <li><button class="menu-item" data-action="like"><i class="fa-regular fa-heart"></i> Añadir a Me gusta</button></li>
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

              @if(Auth::check() && Auth::id() === $user->id)
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

              <div class="card album-card {{ (Auth::check() && Auth::id() === $user->id) ? 'has-trash' : '' }}">
                <a href="{{ route('album.show', $album->id) }}" class="card-link">
                  <div class="card-img">
                    <img src="{{ $albumCover }}" alt="Portada" loading="lazy" decoding="async">
                    <span class="album-play"><i class="fa-solid fa-play"></i></span>
                  </div>
                  <h4 class="album-title">{{ $album->titulo }}</h4>
                  <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre }}</p>
                </a>

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

      $relPages = $normalizedReleases->chunk(8); // 4×2
    @endphp

    <section class="releases-section no-clip">
      <div class="releases-head">
        <h2><i class="fa-solid fa-bolt"></i> Últimos lanzamientos</h2>
        <a href="{{ $releasesAllUrl }}" class="btn-link">Ver todo</a>
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
                      $rCover = $item['cover'] ? drive_img_url($item['cover'], 360) . '&v=' . time() : asset('img/default-album.png');
                    @endphp
                    <div class="card release-card">
                      <div class="card-img">
                        <img src="{{ $rCover }}" alt="Portada" loading="lazy" decoding="async">
                        <span class="type-badge">{{ $item['tipo'] === 'album' ? 'Álbum' : 'Canción' }}</span>
                      </div>
                      <h4>{{ $item['titulo'] }}</h4>
                      <p>{{ $item['anio'] }}</p>
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
              <button id="confirmDeleteBtn" type="submit" class="btn btn-danger">
                <i class="fa-solid fa-trash"></i> Sí, eliminar
              </button>
            </form>
            <button id="cancelDelete" type="button" class="btn btn-secondary">
              <i class="fa-solid fa-xmark"></i> Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== MODAL EDITAR PERFIL ===== -->
    @if(Auth::check() && Auth::id() === $user->id)
    <div class="modal" id="editModal" aria-hidden="true" role="dialog" aria-modal="true">
      <div class="modal-content glass">
        <div class="modal-header">
          <h3><i class="fa-solid fa-user-pen"></i> Editar perfil</h3>
          <button type="button" class="btn btn-ghost small" id="closeEditTop" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form action="{{ route('perfil.update') }}" method="POST" enctype="multipart/form-data">
          @csrf
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
          <div class="modal-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
            <button type="button" class="btn btn-secondary" id="closeEdit"><i class="fa-solid fa-xmark"></i> Cancelar</button>
          </div>
        </form>
      </div>
    </div>
    @endif

  </main>

  @include('components.footer')

</div><!-- /#page-profile -->

<!-- ===== Inicialización robusta (DOM, Turbo, BFCache) ===== -->
<script>
(function(){
  const rootSel = '#page-profile';
  function once(key){
    const r = document.querySelector(rootSel);
    if(!r) return false;
    if(r.dataset[key]) return false;
    r.dataset[key] = '1';
    return true;
  }

  function initProfile(){
    const rootEl = document.querySelector(rootSel);
    if(!rootEl) return;

    // limpia estados residuales
    document.body.classList.remove('blurred','modal-open');

    // Animación hover para filas de canciones
    function initSongHoverEffects() {
      rootEl.querySelectorAll('.song-row').forEach(row => {
        row.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-8px) scale(1.02)';
          this.style.boxShadow = '0 12px 24px rgba(0,0,0,.15)';
          this.style.zIndex = '10';
          this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        row.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0) scale(1)';
          this.style.boxShadow = 'none';
          this.style.zIndex = '1';
        });
      });
    }

    // Inicializar efectos hover
    initSongHoverEffects();

    /* Banner progresivo */
    if (once('banner')) {
      const banner = rootEl.querySelector('.profile-banner');
      if (banner?.dataset.hires){
        const hi = new Image(); hi.src = banner.dataset.hires; hi.decoding = 'async';
        hi.onload = () => { banner.style.backgroundImage = `url('${banner.dataset.hires}')`; banner.classList.add('loaded'); };
      }
    }

    /* Canción clicable */
    if (once('rows')){
      rootEl.querySelectorAll('.song-row').forEach(row => {
        const play = () => row.querySelector('.cancion-item')?.click();
        row.addEventListener('click', (e) => {
          if (e.target.closest('.icon-chip') || e.target.closest('.kebab-menu')) return;
          play();
        });
      });
    }

    /* Menú 3 puntos */
    if (once('kebab')){
      rootEl.querySelectorAll('.more-btn').forEach(btn=>{
        btn.addEventListener('click', e => {
          e.stopPropagation();
          const wrap = btn.closest('.menu-wrap');
          wrap.querySelector('.kebab-menu').classList.toggle('open');
          btn.setAttribute('aria-expanded', wrap.querySelector('.kebab-menu').classList.contains('open'));
        });
      });
      document.addEventListener('click', ()=> {
        rootEl.querySelectorAll('.kebab-menu.open').forEach(m => m.classList.remove('open'));
      });
    }

    /* Carrusel álbumes + altura filas */
    (function albums(){
      const track = document.getElementById('albumsTrack');
      const viewport = document.getElementById('albumsViewport');
      const prev = document.getElementById('albumsPrev');
      const next = document.getElementById('albumsNext');
      const label = document.getElementById('albumsPageLabel');
      if(!track || !viewport) return;
      let page = 0, pages = parseInt(track.dataset.pages || '0', 10);
      const updateAlbums = () => {
        track.style.transform = `translateX(-${page * 100}%)`;
        if (prev) prev.disabled = (page === 0);
        if (next) next.disabled = (page >= pages - 1);
        if (label) label.textContent = pages ? `Página ${page+1} de ${pages}` : '';
      };
      prev?.addEventListener('click', ()=>{ if (page>0) { page--; updateAlbums(); }});
      next?.addEventListener('click', ()=>{ if (page<pages-1) { page++; updateAlbums(); }});
      updateAlbums();

      const syncHeights = () => {
        const vpH = viewport.clientHeight || 0;
        if (!vpH) return;
        const rowH = Math.max(64, Math.floor(vpH / 6));
        document.querySelector(rootSel).style.setProperty('--aurp-song-row-h', rowH + 'px');
      };
      const delayedSync = () => requestAnimationFrame(syncHeights);
      syncHeights();
      window.addEventListener('resize', delayedSync, { passive:true });
      window.addEventListener('load', delayedSync, { once:true });
    })();

    /* Modales editar + previews */
    (function modals(){
      const editModal = document.getElementById('editModal');
      const openEdit  = document.getElementById('editBtn');
      const closeEdit1= document.getElementById('closeEdit');
      const closeEdit2= document.getElementById('closeEditTop');
      const closeEdit = ()=> editModal?.setAttribute('aria-hidden','true');
      openEdit?.addEventListener('click', ()=> editModal?.setAttribute('aria-hidden','false'));
      closeEdit1?.addEventListener('click', closeEdit);
      closeEdit2?.addEventListener('click', closeEdit);
      editModal?.addEventListener('click', (e)=>{ if(e.target===editModal) closeEdit(); });

      const avatarInput = document.getElementById('avatarInput');
      const avatarPrev  = document.getElementById('avatarPreview');
      const avatarLive  = document.getElementById('avatarPreviewLive');
      const bannerInput = document.getElementById('bannerInput');
      const bannerPrev  = document.getElementById('bannerPreview');
      document.querySelector('#page-profile .avatar-edit')?.addEventListener('click', ()=> avatarInput?.click());
      document.querySelector('#page-profile .banner-edit')?.addEventListener('click', ()=> bannerInput?.click());
      avatarInput?.addEventListener('change', ()=> {
        const f = avatarInput.files?.[0]; if(!f) return;
        const url = URL.createObjectURL(f);
        if (avatarPrev) { avatarPrev.src = url; avatarPrev.style.display='block'; }
        if (avatarLive) { avatarLive.src = url; }
      });
      bannerInput?.addEventListener('change', ()=> {
        const f = bannerInput.files?.[0]; if(!f) return;
        const url = URL.createObjectURL(f);
        const banner = document.querySelector('#page-profile .profile-banner');
        if (bannerPrev) { bannerPrev.src = url; bannerPrev.style.display='block'; }
        if (banner)     { banner.style.backgroundImage = `url('${url}')`; }
      });
    })();

    /* Confirm eliminar */
    (function confirmDelete(){
      const cModal = document.getElementById('confirmModal');
      if(!cModal) return;
      const cCover = document.getElementById('confirmCover');
      const cTitle = document.getElementById('confirmTitle');
      const cSub   = document.getElementById('confirmSubtitle');
      const dForm  = document.getElementById('deleteForm');
      const dangerBtn = document.getElementById('confirmDeleteBtn');
      const cancelBtn = document.getElementById('cancelDelete');
      let lastFocused = null;

      const focusableSel = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
      function trapFocus(container, e){
        const f = [...container.querySelectorAll(focusableSel)].filter(el=>!el.disabled && el.offsetParent !== null);
        if (!f.length) return; const first = f[0], last = f[f.length - 1];
        if (e.key === 'Tab'){
          if (e.shiftKey && document.activeElement === first){ last.focus(); e.preventDefault(); }
          else if (!e.shiftKey && document.activeElement === last){ first.focus(); e.preventDefault(); }
        }
      }
      function openConfirm(type, action, title, cover){
        lastFocused = document.activeElement;
        cCover.src = cover || '';
        cTitle.textContent = '¿Deseas eliminar ' + (type === 'album' ? 'este álbum?' : 'esta canción?');
        cSub.textContent   = title || '';
        dForm.action       = action;
        cModal.setAttribute('aria-hidden','false');
        document.body.classList.add('blurred','modal-open');
        dangerBtn.focus();

        const onKey = (e)=>{
          if (e.key === 'Escape'){ closeConfirm(); }
          if (e.key === 'Enter' && cModal.getAttribute('aria-hidden') === 'false' && document.activeElement !== cancelBtn){
            e.preventDefault(); dangerBtn.click();
          }
          trapFocus(cModal, e);
        };
        cModal._escHandler = onKey;
        document.addEventListener('keydown', onKey);
      }
      function closeConfirm(){
        cModal.setAttribute('aria-hidden','true');
        document.body.classList.remove('blurred','modal-open');
        if (cModal._escHandler){
          document.removeEventListener('keydown', cModal._escHandler);
          cModal._escHandler = null;
        }
        lastFocused?.focus?.();
      }
      document.querySelectorAll('#page-profile .open-delete').forEach(btn=>{
        btn.addEventListener('click', (e)=>{
          e.stopPropagation();
          openConfirm(btn.dataset.type, btn.dataset.action, btn.dataset.title, btn.dataset.cover);
        });
      });
      cancelBtn?.addEventListener('click', closeConfirm);
      cModal?.addEventListener('click', (e)=>{ if (e.target === cModal) closeConfirm(); });
    })();
  }

  // Soporta cargas normales, Turbo y back/forward cache
  document.addEventListener('DOMContentLoaded', initProfile);
  document.addEventListener('turbo:load', initProfile);
  document.addEventListener('turbo:render', initProfile);
  window.addEventListener('pageshow', (e)=>{ if(e.persisted) initProfile(); });
})();
</script>

<!-- ===== Medición dinámica de sidebar y reproductor derecho ===== -->
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
  window.addEventListener('resize', measure);
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
</body>
</html>
