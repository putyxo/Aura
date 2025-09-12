<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Me gusta</title>

  <!-- Fuente + Iconos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Vite CSS -->
  @vite(['resources/css/like.css'])
</head>
<body>
<div class="lk-app">
  <div class="lk-with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    @php
      if (!isset($likedSongs) || is_null($likedSongs)) {
          $likedSongs = collect();
      } elseif (is_array($likedSongs)) {
          $likedSongs = collect($likedSongs);
      }
    @endphp

    <main class="main-content lk-page" data-page="likes">
      <div class="lk-shell">

        <!-- HERO -->
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

        @if(session('ok'))
          <div class="lk-toast lk-toast--ok" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif

        <!-- GRID -->
        <section class="lk-grid" id="lkGrid" data-count="{{ $likedSongs->count() }}">
          @forelse($likedSongs as $song)
            @php
              $title   = $song->title  ?? $song->titulo  ?? $song->name ?? 'Sin título';
              $artist  = optional($song->user)->nombre_artistico ?? optional($song->user)->nombre ?? 'Artista';

              // COVER robusto
              $coverRaw = $song->cover_path ?? $song->portada ?? $song->imagen ?? null;
              $cover    = asset('img/default-cover.jpg');
              if ($coverRaw) {
                  $coverId = null;
                  if (!\Illuminate\Support\Str::startsWith($coverRaw, ['http://','https://'])) {
                      $coverId = $coverRaw;
                  } elseif (\Illuminate\Support\Str::contains($coverRaw, 'drive.google')) {
                      if (preg_match('~/d/([^/]+)~', $coverRaw, $m))         $coverId = $m[1];
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

              // AUDIO robusto
              $audioRaw = $song->audio_path ?? $song->ruta_audio ?? $song->file_url ?? null;
              $audio    = null;
              if ($audioRaw) {
                  $audioId = null;
                  if (!\Illuminate\Support\Str::startsWith($audioRaw, ['http://','https://'])) {
                      $audioId = $audioRaw;
                  } elseif (\Illuminate\Support\Str::contains($audioRaw, 'drive.google')) {
                      if (preg_match('~/d/([^/]+)~', $audioRaw, $m))         $audioId = $m[1];
                      elseif (preg_match('~[?&]id=([^&]+)~', $audioRaw, $m)) $audioId = $m[1];
                  }
                  $audio = $audioId ? route('media.drive', ['id' => $audioId]) : $audioRaw;
              }

              $durSec    = is_numeric($song->duration ?? $song->duracion ?? null) ? (int)($song->duration ?? $song->duracion) : 0;
              $durText   = $durSec ? gmdate('i:s', max(0,$durSec)) : '--:--';
              $searchKey = mb_strtolower(($title ?? '') . ' ' . $artist, 'UTF-8');
            @endphp

            <article
              class="lk-tile"
              title="{{ $title }}"
              data-name="{{ $searchKey }}"
              data-duration="{{ $durSec }}"
              data-song-id="{{ $song->id }}"
              data-title="{{ $title }}"
              data-artist="{{ $artist }}"
              data-cover="{{ $cover }}"
              data-audio="{{ $audio }}"
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

                <!-- 3 puntitos -->
                <button type="button" class="lk-keb" aria-haspopup="menu" aria-expanded="false" title="Más opciones">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="lk-menu" role="menu">
                  <button type="button" data-act="queue"><i class="fa-solid fa-list"></i> Añadir a cola</button>
                  <button type="button" data-act="playlist"><i class="fa-solid fa-plus"></i> Agregar a playlist</button>
                  <div class="sep" aria-hidden="true"></div>
                  <button type="button" data-act="unlike" class="danger"><i class="fa-solid fa-heart-crack"></i> Quitar de favoritos</button>
                </div>
              </div>

              <div class="lk-meta">
                <div class="lk-title-row">
                  <div class="lk-title" title="{{ $title }}">{{ $title }}</div>
                  <span class="lk-duration">{{ $durText }}</span>
                </div>
                <div class="lk-artist">{{ $artist }}</div>
              </div>

              <!-- Fallback sin JS -->
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

<!-- ======= MODAL CONFIRMACIÓN ======= -->
<div id="lkConfirm" class="lk-modal" role="dialog" aria-modal="true" aria-labelledby="lkConfirmTitle" hidden>
  <div class="lk-modal__backdrop" data-close="1"></div>
  <div class="lk-modal__card">
    <div class="lk-modal__header">
      <h3 id="lkConfirmTitle"><i class="fa-solid fa-heart-crack"></i> Quitar de favoritos</h3>
      <button class="lk-modal__x" data-close="1" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="lk-modal__body">
      <img id="lkConfirmCover" src="{{ asset('img/default-cover.jpg') }}" alt="Portada" class="lk-modal__cover">
      <div class="lk-modal__info">
        <p class="lk-modal__text">¿Quieres quitar de favoritos esta canción?</p>
        <div class="lk-modal__song">
          <div class="lk-modal__title">—</div>
          <div class="lk-modal__artist">—</div>
        </div>
      </div>
    </div>
    <div class="lk-modal__actions">
      <button class="lk-btn lk-btn--ghost" data-close="1">Cancelar</button>
      <button class="lk-btn lk-btn--danger" id="lkConfirmOk">Aceptar</button>
    </div>
  </div>
</div>

<!-- ======= JS: buscador + persistencia + modal + PLAY/menú ======= -->
<script>
(() => {
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const grid    = $('#lkGrid');
  const search  = $('#lkSearch');
  const clear   = $('#lkClear');
  const countEl = $('#lkCount');

  const CSRF      = (document.querySelector('meta[name="csrf-token"]')?.content) || '{{ csrf_token() }}';
  const USER_ID   = @json(Auth::id());
  const UID       = (USER_ID ?? 'guest');

  let bc = null; try { bc = new BroadcastChannel('aura-player'); } catch {}

  const STORE_LIKES   = `aura_likes_v1_${UID}`;
  const STORE_SEARCH  = `aura_likes_search_v1_${UID}`;
  const STORE_SCROLL  = `aura_likes_scroll_v1_${UID}`;

  const number = (n)=> (Number(n)||0).toLocaleString('es');
  const tiles  = () => $$('.lk-tile');
  const byId   = (id) => $(`.lk-tile[data-song-id="${CSS.escape(String(id))}"]`);

  /* ===== Modal ===== */
  const modal       = $('#lkConfirm');
  const modalOK     = $('#lkConfirmOk');
  const modalTitle  = $('.lk-modal__title', modal);
  const modalArtist = $('.lk-modal__artist', modal);
  const modalCover  = $('#lkConfirmCover');
  let modalSongId   = null;

  function openModal({id, title, artist, cover}) {
    modalSongId = id;
    modalTitle.textContent  = title || '—';
    modalArtist.textContent = artist || '—';
    modalCover.src = cover || modalCover.src;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    modal.classList.add('is-open');
  }
  function closeModal() {
    modal.classList.remove('is-open');
    modal.hidden = true;
    document.body.style.overflow = '';
    modalSongId = null;
  }
  modal?.addEventListener('click', (e) => { if (e.target.dataset.close) closeModal(); });
  document.addEventListener('keydown', (e) => { if (!modal.hidden && e.key === 'Escape') closeModal(); });

  /* ===== Crear tarjeta (para persistencia) ===== */
  const buildTile = (song) => {
    const id      = song.id;
    const title   = (song.title  || 'Sin título').toString();
    const artist  = (song.artist || 'Artista').toString();
    const cover   = song.cover || "{{ asset('img/default-cover.jpg') }}";
    const audio   = song.audio || '';
    const durNum  = Number.isFinite(song.duration) ? song.duration : 0;
    const total   = durNum ? new Date(durNum * 1000).toISOString().substring(14,19) : '--:--';
    const searchKey = (title + ' ' + artist).toLowerCase();

    const el = document.createElement('article');
    el.className = 'lk-tile';
    el.title = title;
    el.dataset.name = searchKey;
    el.dataset.duration = durNum || 0;
    el.dataset.songId = id;
    el.dataset.title = title;
    el.dataset.artist = artist;
    el.dataset.cover = cover;
    el.dataset.audio = audio;

    el.innerHTML = `
      <a class="lk-tile__link" aria-label="Abrir ${title}"></a>
      <div class="lk-cover">
        <img src="${cover}" alt="Portada ${title}" width="260" height="260" loading="lazy" decoding="async">
        <button type="button" class="lk-play" aria-label="Reproducir ${title}">
          <i class="fa-solid fa-play"></i>
        </button>
        <button type="button" class="lk-like is-liked" title="Quitar de Me gusta" aria-label="Quitar de Me gusta" data-unlike="${id}">
          <i class="fa-solid fa-heart"></i>
        </button>
        <button type="button" class="lk-keb" aria-haspopup="menu" aria-expanded="false" title="Más opciones">
          <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <div class="lk-menu" role="menu">
          <button type="button" data-act="queue"><i class="fa-solid fa-list"></i> Añadir a cola</button>
          <button type="button" data-act="playlist"><i class="fa-solid fa-plus"></i> Agregar a playlist</button>
          <div class="sep" aria-hidden="true"></div>
          <button type="button" data-act="unlike" class="danger"><i class="fa-solid fa-heart-crack"></i> Quitar de favoritos</button>
        </div>
      </div>
      <div class="lk-meta">
        <div class="lk-title-row">
          <div class="lk-title" title="${title}">${title}</div>
          <span class="lk-duration">${total}</span>
        </div>
        <div class="lk-artist">${artist}</div>
      </div>
      <form class="lk-like-form" method="POST" action="/likes/${id}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="DELETE">
      </form>
    `;
    return el;
  };

  /* ===== Helpers: resaltar "reproduciendo" ===== */
  function markPlayingById(id){
    tiles().forEach(t => t.removeAttribute('data-playing'));
    const el = byId(id);
    if (el) el.setAttribute('data-playing','true');
  }

  /* ===== Reproducir en el reproductor lateral ===== */
  function playInRightPlayer(tile){
    const s = {
      id: Number(tile.dataset.songId),
      src: tile.dataset.audio,
      title: tile.dataset.title,
      artist: tile.dataset.artist,
      cover: tile.dataset.cover,
      duration: Number(tile.dataset.duration) || 0
    };
    markPlayingById(s.id);
    if (window.AuraQueue?.externalPlay) {
      window.AuraQueue.externalPlay(s);
    } else if (window.AuraPlayer?.play) {
      window.AuraPlayer.play(s);
    } else {
      try { bc && bc.postMessage({ type:'PLAY_REQUEST', song:s }); } catch {}
    }
  }

  /* ===== Bind de cada tarjeta ===== */
  const bindTile = (tile) => {
    const btnHeart = $('.lk-like', tile);
    const btnPlay  = $('.lk-play', tile);
    const coverBox = $('.lk-cover', tile);
    const linkAll  = $('.lk-tile__link', tile);
    const kebabBtn = $('.lk-keb', tile);
    const menu     = $('.lk-menu', tile);

    // Abrir/cerrar menú (tres puntitos)
    function toggleMenu(show = null){
      const want = show == null ? !menu.classList.contains('show') : show;
      menu.classList.toggle('show', want);
      kebabBtn.setAttribute('aria-expanded', want ? 'true' : 'false');
    }
    kebabBtn?.addEventListener('click', (e)=>{ e.stopPropagation(); toggleMenu(); });
    document.addEventListener('click', (e)=>{
      if (!menu.classList.contains('show')) return;
      if (!menu.contains(e.target) && e.target !== kebabBtn) toggleMenu(false);
    });
    document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') toggleMenu(false); });

    // Acciones del menú
    menu?.addEventListener('click', (e)=>{
      const btn = e.target.closest('button[data-act]');
      if (!btn) return;
      const act = btn.dataset.act;
      const song = {
        id: Number(tile.dataset.songId),
        title: tile.dataset.title,
        artist: tile.dataset.artist,
        cover: tile.dataset.cover,
        audio: tile.dataset.audio,
        duration: Number(tile.dataset.duration) || 0
      };

      if (act === 'queue'){
        if (window.AuraQueue?.addToEnd) window.AuraQueue.addToEnd([song]);
      }
      if (act === 'playlist'){
        // Reproducimos (para establecer currentSongId) y abrimos el modal del footer
        if (window.AuraPlayer?.play){
          window.AuraPlayer.play({ id:song.id, src:song.audio, title:song.title, artist:song.artist, cover:song.cover });
          setTimeout(()=> document.getElementById('playlistDropdown')?.click(), 120);
        }
      }
      if (act === 'unlike'){
        openModal({ id:song.id, title:song.title, artist:song.artist, cover:song.cover });
      }
      toggleMenu(false);
    });

    // Hover corazón: animación crack
    btnHeart?.addEventListener('mouseenter', () => {
      const i = btnHeart.querySelector('i');
      i?.classList.replace('fa-heart', 'fa-heart-crack');
      btnHeart.classList.add('is-danger');
    });
    btnHeart?.addEventListener('mouseleave', () => {
      const i = btnHeart.querySelector('i');
      i?.classList.replace('fa-heart-crack', 'fa-heart');
      btnHeart.classList.remove('is-danger');
    });

    // Click corazón => abrir modal
    btnHeart?.addEventListener('click', (e) => {
      e.preventDefault();
      openModal({
        id:     tile.dataset.songId,
        title:  tile.dataset.title,
        artist: tile.dataset.artist,
        cover:  tile.dataset.cover
      });
    });

    // Reproducir al hacer click en botón, imagen o toda la tarjeta
    btnPlay?.addEventListener('click', (e) => { e.preventDefault(); playInRightPlayer(tile); });
    coverBox?.addEventListener('click', (e) => { e.preventDefault(); playInRightPlayer(tile); });
    linkAll?.addEventListener('click', (e) => { e.preventDefault(); playInRightPlayer(tile); });
  };

  /* ===== Vacío / contador ===== */
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

  /* ===== Búsqueda ===== */
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

  /* ===== Persistencia (lista + scroll) ===== */
  const readLikes  = () => { try { return JSON.parse(localStorage.getItem(STORE_LIKES) || '[]'); } catch { return []; } };
  const writeLikes = (arr) => localStorage.setItem(STORE_LIKES, JSON.stringify(arr || []));

  const collectFromDOM = () => tiles().map(t => ({
    id:       Number(t.dataset.songId) || null,
    title:    t.dataset.title  || 'Sin título',
    artist:   t.dataset.artist || 'Artista',
    cover:    t.dataset.cover  || '',
    audio:    t.dataset.audio  || '',
    duration: Number(t.dataset.duration) || 0
  }));

  function saveNow() {
    writeLikes(collectFromDOM());
    if (search) localStorage.setItem(STORE_SEARCH, search.value || '');
    localStorage.setItem(STORE_SCROLL, String(window.scrollY || 0));
  }
  window.addEventListener('beforeunload', saveNow);
  document.addEventListener('visibilitychange', () => { if (document.visibilityState==='hidden') saveNow(); });
  setInterval(saveNow, 2000);

  /* ===== Sincronización con otras pestañas / footer ===== */
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
        const s = {
          id:       song.id,
          title:    song.title  || 'Sin título',
          artist:   song.artist || 'Artista',
          cover:    song.cover  || '',
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
  if (bc) bc.onmessage = (ev)=> handleLikeChanged(ev);

  /* ===== Bind inicial ===== */
  tiles().forEach(bindTile);

  // Restaurar búsqueda
  const savedQ = localStorage.getItem(STORE_SEARCH);
  if (savedQ != null && search) { search.value = savedQ; }
  applySearch();

  // Merge con lo guardado
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

  // Restaurar scroll
  const y = Number(localStorage.getItem(STORE_SCROLL) || '0');
  if (Number.isFinite(y) && y > 0) { requestAnimationFrame(()=> window.scrollTo(0, y)); }

  ensureEmptyMessage(); updateCount();

  /* ===== Confirmar eliminación (Aceptar) ===== */
  modalOK?.addEventListener('click', async () => {
    const id = modalSongId;
    if (!id) return;

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

      byId(id)?.remove();
      updateCount(); ensureEmptyMessage(); saveNow();

      const payload = { type:'LIKE_CHANGED', liked:false, song:{ id: Number(id) } };
      try { bc && bc.postMessage(payload); } catch {}
      document.dispatchEvent(new CustomEvent('aura:like-changed', { detail: payload }));

    } catch (err) {
      alert('No se pudo quitar de Me gusta.');
      console.error(err);
    } finally {
      closeModal();
    }
  });
})();
</script>
</body>
</html>
