<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Me gusta</title>

  {{-- Fuente + Iconos --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- Vite CSS --}}
  @vite(['resources/css/like.css'])
</head>
<body>
<div class="lk-app">
  <div class="lk-with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    {{-- HOTFIX: garantiza que $likedSongs exista como Collection --}}
@php
  use Illuminate\Support\Str;

  $title  = $song->title  ?? $song->titulo  ?? $song->name ?? 'Sin título';
  $artist = optional($song->user)->nombre_artistico ?? optional($song->user)->nombre ?? 'Artista';

  // --- COVER robusto (ID de Drive, URL de Drive o URL común) ---
  $coverRaw = $song->cover_path ?? $song->portada ?? $song->imagen ?? null;
  $cover    = asset('img/default-cover.jpg');

  if ($coverRaw) {
      $coverId = null;

      if (!Str::startsWith($coverRaw, ['http://','https://'])) {
          // Probable ID de Drive crudo (alfanumérico largo)
          $coverId = $coverRaw;
      } elseif (Str::contains($coverRaw, 'drive.google')) {
          if (preg_match('~/d/([^/]+)~', $coverRaw, $m))       $coverId = $m[1];
          elseif (preg_match('~[?&]id=([^&]+)~', $coverRaw, $m)) $coverId = $m[1];
      }

      if ($coverId) {
          // Si tienes helper de imágenes de Drive, úsalo para servir versión ligera
          if (function_exists('drive_img_url')) {
              $cover = drive_img_url($coverId, 260) . '&v=' . time();
          } else {
              $cover = route('media.drive', ['id' => $coverId]);
          }
      } else {
          $cover = $coverRaw;
      }
  }

  // --- AUDIO robusto (ID de Drive, URL de Drive o URL común) ---
  $audioRaw = $song->audio_path ?? $song->ruta_audio ?? $song->file_url ?? null;
  $audio    = null;

  if ($audioRaw) {
      $audioId = null;

      if (!Str::startsWith($audioRaw, ['http://','https://'])) {
          $audioId = $audioRaw;
      } elseif (Str::contains($audioRaw, 'drive.google')) {
          if (preg_match('~/d/([^/]+)~', $audioRaw, $m))        $audioId = $m[1];
          elseif (preg_match('~[?&]id=([^&]+)~', $audioRaw, $m))  $audioId = $m[1];
      }

      $audio = $audioId ? route('media.drive', ['id' => $audioId]) : $audioRaw;
  }

  $dur       = $song->duration ?? $song->duracion ?? null;
  $searchKey = Str::lower(($title ?? '') . ' ' . $artist);
@endphp


    <main class="main-content lk-page" data-page="likes">
      <div class="lk-shell">

        {{-- ================== HERO ================== --}}
        <section class="lk-hero" aria-label="Tus Me gusta">
          <div class="lk-hero__bg"></div>

          <div class="lk-hero__row">
            <div class="lk-hero__content">
              <div class="lk-hero__icon"><i class="fa-solid fa-heart"></i></div>
              <div>
                <h1 class="lk-hero__title">Tus Me gusta</h1>
                <p class="lk-hero__sub">
                  <span id="lkCount">{{ number_format($likedSongs->count()) }}</span> canciones guardadas para volver siempre.
                </p>
              </div>
            </div>

            <div class="lk-hero__actions">
              <div class="lk-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="lkSearch" type="search" placeholder="Buscar canción o artista..." aria-label="Buscar en Me gusta" autocomplete="off">
                <button class="lk-clear" id="lkClear" aria-label="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></button>
              </div>
            </div>
          </div>
        </section>

        {{-- Toast --}}
        @if(session('ok'))
          <div class="lk-toast lk-toast--ok" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif

        {{-- ================== GRID ================== --}}
        <section class="lk-grid" id="lkGrid" data-count="{{ $likedSongs->count() }}">

         @php
  $title  = $song->title  ?? $song->titulo  ?? $song->name ?? 'Sin título';
  $artist = optional($song->user)->nombre_artistico ?? optional($song->user)->nombre ?? 'Artista';

  // --- COVER robusto (ID/URL de Drive o URL común) ---
  $coverRaw = $song->cover_path ?? $song->portada ?? $song->imagen ?? null;
  $cover    = asset('img/default-cover.jpg');

  if ($coverRaw) {
      $coverId = null;

      if (!\Illuminate\Support\Str::startsWith($coverRaw, ['http://','https://'])) {
          // Probable ID crudo de Drive
          $coverId = $coverRaw;
      } elseif (\Illuminate\Support\Str::contains($coverRaw, 'drive.google')) {
          if (preg_match('~/d/([^/]+)~', $coverRaw, $m))       $coverId = $m[1];
          elseif (preg_match('~[?&]id=([^&]+)~', $coverRaw, $m)) $coverId = $m[1];
      }

      if ($coverId) {
          $cover = function_exists('drive_img_url')
              ? drive_img_url($coverId, 260) . '&v=' . time()
              : route('media.drive', ['id' => $coverId]);
      } else {
          $cover = $coverRaw;
      }
  }

  // --- AUDIO robusto (ID/URL de Drive o URL común) ---
  $audioRaw = $song->audio_path ?? $song->ruta_audio ?? $song->file_url ?? null;
  $audio    = null;

  if ($audioRaw) {
      $audioId = null;

      if (!\Illuminate\Support\Str::startsWith($audioRaw, ['http://','https://'])) {
          $audioId = $audioRaw;
      } elseif (\Illuminate\Support\Str::contains($audioRaw, 'drive.google')) {
          if (preg_match('~/d/([^/]+)~', $audioRaw, $m))        $audioId = $m[1];
          elseif (preg_match('~[?&]id=([^&]+)~', $audioRaw, $m))  $audioId = $m[1];
      }

      $audio = $audioId ? route('media.drive', ['id' => $audioId]) : $audioRaw;
  }

  $dur       = $song->duration ?? $song->duracion ?? null;
  $searchKey = \Illuminate\Support\Str::lower(($title ?? '') . ' ' . $artist);
@endphp


            <article
              class="lk-tile"
              title="{{ $title }}"
              data-name="{{ $searchKey }}"
              data-duration="{{ $dur ?? 0 }}"
              data-song-id="{{ $song->id }}"
            >
              <a class="lk-tile__link" aria-label="Abrir {{ $title }}"></a>

              <div class="lk-cover">
                <img src="{{ $cover }}" alt="Portada {{ $title }}" width="260" height="260" loading="lazy" decoding="async">
                <button type="button" class="lk-play" aria-label="Reproducir {{ $title }}">
                  <i class="fa-solid fa-play"></i>
                </button>
                <button type="button" class="lk-like is-liked" title="Quitar de Me gusta" aria-label="Quitar de Me gusta" data-unlike="{{ $song->id }}">
                  <i class="fa-solid fa-heart"></i>
                </button>
              </div>

              <div class="lk-meta">
                <div class="lk-title" title="{{ $title }}">{{ $title }}</div>
                <div class="lk-artist">{{ $artist }}</div>
              </div>

              {{-- Mini player mejorado --}}
              <div class="lk-player" aria-label="Mini reproductor">
                <audio preload="none" src="{{ $audio }}"></audio>

                <div class="lk-player__controls">
                  <button class="lk-btn lk-btn--sm lk-btn--primary lk-btn-play" aria-label="Reproducir">
                    <i class="fa-solid fa-play"></i>
                  </button>
                  <button class="lk-btn lk-btn--sm lk-btn--ghost lk-btn-mute" aria-label="Silenciar">
                    <i class="fa-solid fa-volume-high"></i>
                  </button>
                  <div class="lk-time">
                    <span class="lk-time__current">0:00</span>
                    <span class="lk-time__sep">/</span>
                    <span class="lk-time__total">{{ $dur ? gmdate('i:s', max(0,$dur)) : '--:--' }}</span>
                  </div>
                </div>

                <div class="lk-player__bar">
                  <input class="lk-seek" type="range" min="0" max="{{ $dur ?? 0 }}" value="0" step="1" aria-label="Barra de progreso">
                </div>
              </div>

              {{-- Fallback sin JS para quitar Me gusta --}}
              <form class="lk-like-form" method="POST" action="{{ url('likes/'.$song->id) }}">
                @csrf
                @method('DELETE')
              </form>
            </article>
          @empty
            <div class="lk-empty" style="grid-column:1/-1;">
              <i class="fa-solid fa-heart-crack"></i>
              <h3>Aún no tienes canciones en Me gusta</h3>
              <p>Descubre música y pulsa <i class="fa-solid fa-heart"></i> para guardarlas aquí.</p>
            </div>
          @endforelse
        </section>

      </div>
    </main>

    @include('components.footer')
  </div>
</div>

{{-- ======= JS (inline) mini-player + buscador + SYNC con footer ======= --}}
<script>
(() => {
  // ===== Utilidades DOM =====
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const grid    = $('#lkGrid');
  const search  = $('#lkSearch');
  const clear   = $('#lkClear');
  const countEl = $('#lkCount');

  // Defaults
  const DEF_COVER = "{{ asset('img/default-cover.jpg') }}";
  const CSRF      = (document.querySelector('meta[name="csrf-token"]')?.content) || '{{ csrf_token() }}';
  const USER_ID   = @json(Auth::id());
  const UID       = (USER_ID ?? 'guest');

  // ===== Claves de almacenamiento =====
  const STORE_LIKES   = `aura_likes_v1_${UID}`;
  const STORE_SEARCH  = `aura_likes_search_v1_${UID}`;
  const STORE_SCROLL  = `aura_likes_scroll_v1_${UID}`;

  // Para resolver dinámicamente vistas de Drive cuando vengan IDs
  const DRIVE_VIEW_TMPL = @json(route('media.drive', ['id' => 'FILE_ID'])); // reemplaza FILE_ID

  // ===== Helpers =====
  const fmt = (sec) => {
    sec = Math.max(0, Math.floor(sec || 0));
    const m = Math.floor(sec / 60), s = sec % 60;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
  };
  const number = (n)=> (Number(n)||0).toLocaleString('es');
  const tiles = () => $$('.lk-tile');
  const byId  = (id) => $(`.lk-tile[data-song-id="${CSS.escape(String(id))}"]`);

  const isHttp = (u) => /^https?:\/\//i.test(u||'');
  const resolveDrive = (idOrUrl, {image=false}={}) => {
    if (!idOrUrl) return image ? DEF_COVER : '';
    // Si ya es URL http(s), úsala tal cual
    if (isHttp(idOrUrl)) return idOrUrl;

    // Si parece un ID (alfanumérico largo), arma la ruta
    if (/^[A-Za-z0-9_-]{15,}$/.test(idOrUrl)) {
      // Si tienes un endpoint de imágenes (drive_img_url) en PHP, ya lo usamos en Blade.
      // En JS nos conformamos con la vista genérica del backend:
      return DRIVE_VIEW_TMPL.replace('FILE_ID', idOrUrl);
    }
    return idOrUrl;
  };

  // ===== Construye una tarjeta =====
  const buildTile = (song) => {
    const id      = song.id;
    const title   = (song.title  || 'Sin título').toString();
    const artist  = (song.artist || 'Artista').toString();
    const cover   = resolveDrive(song.cover || song.cover_path || song.portada, {image:true}) || DEF_COVER;
    const audio   = resolveDrive(song.audio || song.audio_path || song.src || '');
    const durNum  = Number.isFinite(song.duration) ? song.duration : (song.duracion ? Number(song.duracion) : 0);
    const total   = durNum ? fmt(durNum) : '--:--';
    const searchKey = (title + ' ' + artist).toLowerCase();

    const article = document.createElement('article');
    article.className = 'lk-tile';
    article.title = title;
    article.dataset.name = searchKey;
    article.dataset.duration = durNum || 0;
    article.dataset.songId = id;

    article.innerHTML = `
      <a class="lk-tile__link" aria-label="Abrir ${title}"></a>

      <div class="lk-cover">
        <img src="${cover}" alt="Portada ${title}" width="260" height="260" loading="lazy" decoding="async">
        <button type="button" class="lk-play" aria-label="Reproducir ${title}">
          <i class="fa-solid fa-play"></i>
        </button>
        <button type="button" class="lk-like is-liked" title="Quitar de Me gusta" aria-label="Quitar de Me gusta" data-unlike="${id}">
          <i class="fa-solid fa-heart"></i>
        </button>
      </div>

      <div class="lk-meta">
        <div class="lk-title" title="${title}">${title}</div>
        <div class="lk-artist">${artist}</div>
      </div>

      <div class="lk-player" aria-label="Mini reproductor">
        <audio preload="none" src="${audio}"></audio>
        <div class="lk-player__controls">
          <button class="lk-btn lk-btn--sm lk-btn--primary lk-btn-play" aria-label="Reproducir">
            <i class="fa-solid fa-play"></i>
          </button>
          <button class="lk-btn lk-btn--sm lk-btn--ghost lk-btn-mute" aria-label="Silenciar">
            <i class="fa-solid fa-volume-high"></i>
          </button>
          <div class="lk-time">
            <span class="lk-time__current">0:00</span>
            <span class="lk-time__sep">/</span>
            <span class="lk-time__total">${total}</span>
          </div>
        </div>
        <div class="lk-player__bar">
          <input class="lk-seek" type="range" min="0" max="${durNum || 0}" value="0" step="1" aria-label="Barra de progreso">
        </div>
      </div>

      <form class="lk-like-form" method="POST" action="/likes/${id}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="DELETE">
      </form>
    `;
    return article;
  };

  // ===== Bind de cada tarjeta =====
  const bindTile = (tile) => {
    const audio    = $('audio', tile);
    const btnPlay  = $('.lk-btn-play', tile);
    const btnMute  = $('.lk-btn-mute', tile);
    const btnHeart = $('.lk-like', tile);
    const coverBtn = $('.lk-play', tile);
    const seek     = $('.lk-seek', tile);
    const tCur     = $('.lk-time__current', tile);
    const tTot     = $('.lk-time__total', tile);

    let current = null;

    const togglePlay = () => {
      if (!audio) return;
      const playing = !audio.paused;

      // Pausa cualquier otro
      const other = document.querySelector('.lk-tile audio:not([src=""])');
      if (other && other !== audio) {
        other.pause();
        const prev = other.closest('.lk-tile');
        prev?.querySelector('.lk-btn-play i')?.classList.replace('fa-pause','fa-play');
        prev?.querySelector('.lk-play i')?.classList.replace('fa-pause','fa-play');
      }

      if (playing) {
        audio.pause();
        btnPlay?.querySelector('i')?.classList.replace('fa-pause','fa-play');
        coverBtn?.querySelector('i')?.classList.replace('fa-pause','fa-play');
      } else {
        audio.play().catch(()=>{});
        btnPlay?.querySelector('i')?.classList.replace('fa-play','fa-pause');
        coverBtn?.querySelector('i')?.classList.replace('fa-play','fa-pause');
        current = audio;
      }
    };

    btnPlay?.addEventListener('click', togglePlay);
    coverBtn?.addEventListener('click', togglePlay);

    btnMute?.addEventListener('click', () => {
      audio.muted = !audio.muted;
      const icon = btnMute.querySelector('i');
      if (audio.muted) icon.classList.replace('fa-volume-high','fa-volume-xmark');
      else             icon.classList.replace('fa-volume-xmark','fa-volume-high');
    });

    audio?.addEventListener('timeupdate', () => {
      if (seek && !seek.dataset.lock) {
        seek.max   = audio.duration || seek.max || 0;
        seek.value = audio.currentTime || 0;
      }
      if (tCur) tCur.textContent = fmt(audio.currentTime);
      if (tTot && (audio.duration || 0) > 0) tTot.textContent = fmt(audio.duration);
    });

    seek?.addEventListener('input', () => {
      seek.dataset.lock = '1';
      if (!isNaN(seek.value)) audio.currentTime = +seek.value;
      if (tCur) tCur.textContent = fmt(seek.value);
    });
    seek?.addEventListener('change', () => { delete seek.dataset.lock; });

    audio?.addEventListener('ended', () => {
      btnPlay?.querySelector('i')?.classList.replace('fa-pause','fa-play');
      coverBtn?.querySelector('i')?.classList.replace('fa-pause','fa-play');
      if (seek) seek.value = 0;
      if (tCur) tCur.textContent = '0:00';
    });

    // --- Unlike por AJAX (sin recargar) + persistencia ---
    btnHeart?.addEventListener('click', async (e) => {
      e.preventDefault();
      const id = tile.dataset.songId;
      if (!id) return;

      // Spinner temporal
      const prev = btnHeart.innerHTML;
      btnHeart.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

      try {
        const fd = new FormData();
        fd.append('_method','DELETE');
        fd.append('_token', CSRF);

        const r = await fetch(`/likes/${id}`, {
          method:'POST', body: fd, credentials:'same-origin',
          headers:{ 'X-Requested-With':'XMLHttpRequest' }
        });

        if (r.redirected) { window.location.href = r.url; return; }
        if (!r.ok) throw new Error('Error al quitar Me gusta');

        // Quitar del DOM y guardar
        tile.remove();
        updateCount(); ensureEmptyMessage(); saveNow();

        // Notificar a footer y otras pestañas
        const payload = { type:'LIKE_CHANGED', liked:false, song:{ id: Number(id) } };
        try { bc && bc.postMessage(payload); } catch {}
        document.dispatchEvent(new CustomEvent('aura:like-changed', { detail: payload }));

      } catch (err) {
        alert('No se pudo quitar de Me gusta.');
        console.error(err);
      } finally {
        // Restaurar icono (si la tarjeta sigue)
        if (btnHeart.isConnected) btnHeart.innerHTML = prev;
      }
    });
  };

  // ===== Vacío / contador =====
  const updateCount = () => { if (countEl) countEl.textContent = number(tiles().length); };
  const ensureEmptyMessage = () => {
    const empty = $('.lk-empty');
    if (tiles().length === 0) {
      if (!empty) {
        const div = document.createElement('div');
        div.className = 'lk-empty';
        div.style.gridColumn = '1/-1';
        div.innerHTML = `
          <i class="fa-solid fa-heart-crack"></i>
          <h3>Aún no tienes canciones en Me gusta</h3>
          <p>Descubre música y pulsa <i class="fa-solid fa-heart"></i> para guardarlas aquí.</p>
        `;
        grid.appendChild(div);
      }
    } else if (empty) {
      empty.remove();
    }
  };

  // ===== Búsqueda (se persiste) =====
  function applySearch() {
    const q = (search?.value || '').trim().toLowerCase();
    tiles().forEach(t => {
      const hay = t.dataset.name || '';
      t.style.display = hay.includes(q) ? '' : 'none';
    });
  }
  search?.addEventListener('input', () => {
    applySearch();
    localStorage.setItem(STORE_SEARCH, search.value || '');
  });
  clear?.addEventListener('click', () => {
    if (!search) return;
    search.value=''; applySearch(); search.focus();
    localStorage.removeItem(STORE_SEARCH);
  });

  // ===== Persistencia de lista + scroll =====
  const readLikes = () => {
    try { return JSON.parse(localStorage.getItem(STORE_LIKES) || '[]'); }
    catch { return []; }
  };
  const writeLikes = (arr) => localStorage.setItem(STORE_LIKES, JSON.stringify(arr || []));

  const collectFromDOM = () => tiles().map(t => {
    const id     = Number(t.dataset.songId) || null;
    const title  = $('.lk-title', t)?.textContent?.trim() || 'Sin título';
    const artist = $('.lk-artist', t)?.textContent?.trim() || 'Artista';
    const cover  = $('.lk-cover img', t)?.src || DEF_COVER;
    const audio  = $('audio', t)?.src || '';
    const dur    = Number(t.dataset.duration) || 0;
    return { id, title, artist, cover, audio, duration: dur };
  });

  function saveNow() {
    writeLikes(collectFromDOM());
    if (search) localStorage.setItem(STORE_SEARCH, search.value || '');
    localStorage.setItem(STORE_SCROLL, String(window.scrollY || 0));
  }

  window.addEventListener('beforeunload', saveNow);
  document.addEventListener('visibilitychange', () => { if (document.visibilityState==='hidden') saveNow(); });
  setInterval(saveNow, 2000);

  // ===== Sincronización con footer / otras pestañas =====
  function handleLikeChanged(data){
    const payload = data?.detail || data?.data || data;
    if (!payload || payload.type !== 'LIKE_CHANGED') return;
    const song  = payload.song || {};
    const liked = !!payload.liked;
    if (!song.id) return;

    const exists = byId(song.id);

    if (liked) {
      if (exists) {
        exists.querySelector('.lk-like')?.classList.add('is-liked');
        exists.style.display = '';
      } else {
        // Si el footer no mandó todos los campos, intenta completar con lo que tengamos
        const s = {
          id:       song.id,
          title:    song.title  || 'Sin título',
          artist:   song.artist || 'Artista',
          cover:    song.cover  || DEF_COVER,
          audio:    song.audio  || '',
          duration: Number.isFinite(song.duration) ? song.duration : 0
        };
        const tile = buildTile(s);
        grid.prepend(tile);
        bindTile(tile);
      }
    } else {
      exists?.remove();
    }

    updateCount(); ensureEmptyMessage(); applySearch(); saveNow();
  }

  document.addEventListener('aura:like-changed', handleLikeChanged);

  let bc = null; try { bc = new BroadcastChannel('aura-player'); bc.onmessage = (ev)=> handleLikeChanged(ev); } catch {}

  // ===== Inicio: bindear existentes, restaurar estado, etc. =====
  // 1) Bind inicial a las tarjetas renderizadas por Blade
  tiles().forEach(bindTile);

  // 2) Restaurar búsqueda
  const savedQ = localStorage.getItem(STORE_SEARCH);
  if (savedQ != null && search) { search.value = savedQ; }
  applySearch();

  // 3) Merge con lo guardado (no duplica por id)
  const stored = readLikes();
  if (stored.length) {
    const seen = new Set(tiles().map(t => 'id:'+t.dataset.songId));
    for (const s of stored) {
      const key = s?.id ? 'id:'+s.id : null;
      if (key && !seen.has(key)) {
        const tile = buildTile(s);
        grid.appendChild(tile);
        bindTile(tile);
        seen.add(key);
      }
    }
    updateCount(); ensureEmptyMessage(); applySearch();
  }

  // 4) Restaurar scroll
  const y = Number(localStorage.getItem(STORE_SCROLL) || '0');
  if (Number.isFinite(y) && y > 0) { requestAnimationFrame(()=> window.scrollTo(0, y)); }

  // 5) Asegurar mensaje vacío/contador
  ensureEmptyMessage(); updateCount();
})();
</script>

</body>
</html>
