export default function initLike() {
  console.log("Inicializando scripts de likes...");
(() => {
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const grid    = $('#lkGrid');
  const search  = $('#lkSearch');
  const clear   = $('#lkClear');
  const countEl = $('#lkCount');

  const CSRF      = (document.querySelector('meta[name="csrf-token"]')?.content) || '{{ csrf_token() }}';
const UID = document.body.dataset.userId || 'guest';

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

    // Por defecto, intentar /likes/{id} con DELETE cuando venga de localStorage
    const unlikeUrl = `/likes/${id}`;
    const unlikeMethod = 'DELETE';

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
    el.dataset.unlikeUrl = unlikeUrl;
    el.dataset.unlikeMethod = unlikeMethod;

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
      <form class="lk-like-form" method="POST" action="${unlikeUrl}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="DELETE">
      </form>
    `;
    return el;
  };

  /* ===== Helpers ===== */
  function markPlayingById(id){
    tiles().forEach(t => t.removeAttribute('data-playing'));
    const el = byId(id);
    if (el) el.setAttribute('data-playing','true');
  }

  // Bloquea reproducción si el click proviene del corazón, el kebab o el menú
  function isPlayBlocked(target){
    return !!target.closest('.lk-like, .lk-keb, .lk-menu');
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
    kebabBtn?.addEventListener('click', (e)=>{
      e.preventDefault();
      e.stopPropagation(); // <- evita burbujeo a cover/link
      toggleMenu();
    });
    document.addEventListener('click', (e)=>{
      if (!menu.classList.contains('show')) return;
      if (!menu.contains(e.target) && e.target !== kebabBtn) toggleMenu(false);
    }, { passive:true });
    document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') toggleMenu(false); });

    // Acciones del menú
    menu?.addEventListener('click', (e)=>{
      e.stopPropagation(); // <- no burbujea a la tarjeta
      const btn = e.target.closest('button[data-act]');
      if (!btn) return;
      const act = btn.dataset.act;

      if (act === 'queue'){
        const song = {
          id: Number(tile.dataset.songId),
          title: tile.dataset.title,
          artist: tile.dataset.artist,
          cover: tile.dataset.cover,
          audio: tile.dataset.audio,
          duration: Number(tile.dataset.duration) || 0
        };
        if (window.AuraQueue?.addToEnd) window.AuraQueue.addToEnd([song]);
      }
      if (act === 'playlist'){
        if (window.AuraPlayer?.play){
          window.AuraPlayer.play({
            id: Number(tile.dataset.songId),
            src: tile.dataset.audio,
            title: tile.dataset.title,
            artist: tile.dataset.artist,
            cover: tile.dataset.cover
          });
          setTimeout(()=> document.getElementById('playlistDropdown')?.click(), 120);
        }
      }
      if (act === 'unlike'){
        openModal({
          id:     tile.dataset.songId,
          title:  tile.dataset.title,
          artist: tile.dataset.artist,
          cover:  tile.dataset.cover
        });
      }
      toggleMenu(false);
    });

    // Corazón: NO reproducir y abrir modal
    btnHeart?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation(); // <- evita click en cover/link
      openModal({
        id:     tile.dataset.songId,
        title:  tile.dataset.title,
        artist: tile.dataset.artist,
        cover:  tile.dataset.cover
      });
    });

    // Reproducir al hacer click en botón play
    btnPlay?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      playInRightPlayer(tile);
    });

    // Click en la portada: SOLO si no fue en corazón/kebab/menú
    coverBox?.addEventListener('click', (e) => {
      if (isPlayBlocked(e.target)) return; // <- bloqueo
      e.preventDefault();
      playInRightPlayer(tile);
    });

    // Click en el link-overlay del artículo: SOLO si no fue en corazón/kebab/menú
    linkAll?.addEventListener('click', (e) => {
      if (isPlayBlocked(e.target)) return; // <- bloqueo
      e.preventDefault();
      playInRightPlayer(tile);
    });
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
  async function requestUnlike({ url, method, id }) {
    const isDelete = method.toUpperCase() === 'DELETE';
    const fd = new FormData();
    fd.append('_token', CSRF);
    if (isDelete) fd.append('_method','DELETE');

    const res = await fetch(url, {
      method: 'POST',
      body: fd,
      credentials:'same-origin',
      headers:{ 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
    });

    if (res.redirected) { window.location.href = res.url; return { ok:true, removed:true }; }

    let json = null;
    try { json = await res.clone().json(); } catch {}

    if (json && typeof json.liked !== 'undefined') {
      return { ok:true, removed: json.liked === false };
    }

    if (res.ok) return { ok:true, removed:true };

    return { ok:false, removed:false, status: res.status };
  }

  modalOK?.addEventListener('click', async () => {
    const id = modalSongId;
    if (!id) return;

    const tile = byId(id);
    const primaryUrl    = tile?.dataset.unlikeUrl || `/likes/${id}`;
    const primaryMethod = (tile?.dataset.unlikeMethod || 'DELETE').toUpperCase();

    const toggleUrl = `/canciones/like/${id}`;

    try {
      let result = await requestUnlike({ url: primaryUrl, method: primaryMethod, id });
      if (!result.ok || !result.removed) {
        result = await requestUnlike({ url: toggleUrl, method: 'POST', id });
        if (!result.ok || !result.removed) {
          throw new Error('No se pudo quitar de Me gusta.');
        }
      }

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
}