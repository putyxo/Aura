{{-- resources/views/components/footer.blade.php (encapsulado y listo para pegar) --}}
<div id="rightPlayer" class="player-card" data-turbo-permanent>
  <div class="current-song">
    <div class="img-wrap">
      <img class="cover" src="{{ asset('img/default-cancion.png') }}" alt="cover por defecto">
    </div>

    <span class="song-name">Selecciona una canción</span>
    <span class="song-autor">Artista</span>

    <div class="time">
      <span class="current-time">0:00</span>
      <span class="total-time">--:--</span>
    </div>

    <input type="range" class="seek" min="0" max="100" value="0" step="any" aria-label="Barra de progreso">

    <div class="controls">
      <button class="prev" title="Anterior"><i class="fas fa-backward"></i></button>
      <button class="play-btn" title="Play/Pause"><i class="fas fa-play"></i></button>
      <button class="next" title="Siguiente"><i class="fas fa-forward"></i></button>
    </div>
  </div>

  <div class="options">
    <button class="icon-btn vol-toggle" title="Silenciar"><i class="fa-solid fa-volume-low"></i></button>
    <input class="vol-range" type="range" min="0" max="100" value="70" title="Volumen" aria-label="Volumen">
    <button id="likeBtn" class="icon-btn" title="Me gusta"><i class="fa-regular fa-heart"></i></button>
    <button id="playlistDropdown" class="icon-btn" title="Agregar a playlist"><i class="fa-solid fa-plus"></i></button>
  </div>

  <!-- === Fila de reproducción === -->
  <div class="play-list">
    <div class="queue-title">
      <div class="queue-title-inner">
        <i class="fa-solid fa-list"></i>
        <span>Fila de reproducción</span>
        <span id="queueCount" class="count-badge"></span>
      </div>
    </div>
    <ul id="queueList" aria-label="Fila de reproducción"></ul>
    <div id="queueEmpty" class="queue-empty" hidden>No hay canciones en cola.</div>
  </div>
  <!-- === /Fila de reproducción === -->

  <!-- Panel flotante: agregar a playlist -->
  <div id="playlistModal" class="playlist-modal" hidden>
    <div class="playlist-modal-content">
      <h3>Agregar a Playlist</h3>
      <ul id="modalPlaylists"></ul>
      <div class="new-playlist">
        <input type="text" id="newPlaylistName" placeholder="Nueva playlist...">
        <button id="createPlaylistBtn">Crear</button>
      </div>
      <button class="close-playlist-modal">Cerrar</button>
    </div>
  </div>

  <!-- Asidero lateral para redimensionar -->
  <button class="rp-resize-handle" aria-label="Ajustar ancho del reproductor" title="Arrastra para ajustar" tabindex="0"></button>
</div>

<script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.4/dist/turbo.es2017-umd.js" defer></script>

<!-- === Script del reproductor (no tocar) === -->
<script>
(() => {
  if (window.__AURA_PLAYER_INIT__) return;
  window.__AURA_PLAYER_INIT__ = true;

  const el = document.getElementById('rightPlayer');
  if (!el) return;

  const audio = new Audio();
  audio.preload = 'metadata';
  audio.playsInline = true;

  const playBtn  = el.querySelector('.play-btn');
  const prevBtn  = el.querySelector('.prev');
  const nextBtn  = el.querySelector('.next');
  const seek     = el.querySelector('.seek');
  const curEl    = el.querySelector('.current-time');
  const totEl    = el.querySelector('.total-time');
  const volRange = el.querySelector('.vol-range');
  const volTgl   = el.querySelector('.vol-toggle');
  const coverEl  = el.querySelector('.cover');
  const titleEl  = el.querySelector('.song-name');
  const artistEl = el.querySelector('.song-autor');

  const likeBtn  = el.querySelector('#likeBtn');
  const plBtn    = el.querySelector('#playlistDropdown');
  const plModal  = el.querySelector('#playlistModal');
  const plList   = el.querySelector('#modalPlaylists');
  const plClose  = el.querySelector('.close-playlist-modal');
  const plInput  = el.querySelector('#newPlaylistName');
  const plCreate = el.querySelector('#createPlaylistBtn');

  const CSRF   = (document.querySelector('meta[name="csrf-token"]')?.content) || '{{ csrf_token() }}';
  const userId = @json(Auth::id());
  const KEY    = 'player_state_' + userId;

  let currentSongId = null;
  let raf = null, ticker = null, lastVolume = 0.7;

  const fmt = s => !Number.isFinite(s) ? '--:--' : `${Math.floor(s/60)}:${String(Math.floor(s%60)).padStart(2,'0')}`;

  const paintSeek = p => {
    const x = Math.max(0, Math.min(100, p||0));
    seek.style.background = `linear-gradient(to right, var(--pl-accent) 0%, var(--pl-accent2) ${x}%, rgba(255,255,255,.2) ${x}%, rgba(255,255,255,.2) 100%)`;
  };
  const paintByTime = () => {
    if (!audio.duration) return paintSeek(0);
    paintSeek((audio.currentTime / audio.duration) * 100);
  };
  const paintVolume = () => {
    const p = Math.round((audio.volume || 0) * 100);
    volRange.value = p;
    volRange.style.background = `linear-gradient(to right,var(--pl-accent) 0%,var(--pl-accent2) ${p}%,#35354f ${p}%,#35354f 100%)`;
  };

  function uiSync(){
    if (audio.duration){
      seek.max = audio.duration;
      seek.value = audio.currentTime;
      curEl.textContent = fmt(audio.currentTime);
      totEl.textContent = fmt(audio.duration);
      paintByTime();
    } else {
      curEl.textContent = '0:00';
      totEl.textContent = '--:--';
    }
  }
  function rafLoop(){ uiSync(); raf = requestAnimationFrame(rafLoop); }
  function ensureTicker(){
    if (raf == null) raf = requestAnimationFrame(rafLoop);
    if (!ticker) ticker = setInterval(uiSync, 500);
  }
  ensureTicker();

  playBtn.addEventListener('click', () => {
    if (audio.paused){
      audio.play().then(()=>{ playBtn.innerHTML='<i class="fas fa-pause"></i>'; }).catch(()=>{});
    }else{
      audio.pause(); playBtn.innerHTML='<i class="fas fa-play"></i>';
    }
  });

  // Integración con la cola (atrás/adelante/siguiente)
  prevBtn.addEventListener('click', ()=> {
    if (window.AuraQueue?.back) window.AuraQueue.back();
    else audio.currentTime = 0;
  });
  nextBtn.addEventListener('click', ()=> {
    if (window.AuraQueue?.forwardOrNext) window.AuraQueue.forwardOrNext();
  });

  seek.addEventListener('input', ()=> {
    audio.currentTime = Number(seek.value) || 0;
    paintByTime();
  });

  volRange.addEventListener('input', ()=> {
    audio.volume = (Number(volRange.value)||0)/100;
    localStorage.setItem('player_volume', audio.volume.toString());
    paintVolume();
  });
  volTgl.addEventListener('click', ()=> {
    if (audio.volume>0){ lastVolume = audio.volume; audio.volume = 0; }
    else { audio.volume = lastVolume || Number(localStorage.getItem('player_volume')) || 0.7; }
    localStorage.setItem('player_volume', audio.volume.toString());
    paintVolume();
  });

  audio.addEventListener('loadedmetadata', uiSync);
  audio.addEventListener('play',  ()=> { playBtn.innerHTML='<i class="fas fa-pause"></i>'; });
  audio.addEventListener('pause', ()=> { playBtn.innerHTML='<i class="fas fa-play"></i>'; });
  audio.addEventListener('timeupdate', uiSync);
  audio.addEventListener('ended', ()=> {
    playBtn.innerHTML='<i class="fas fa-play"></i>';
    if (window.AuraQueue?.onEnded) window.AuraQueue.onEnded();
  });

  function saveState(){
    if (!audio.src) return;
    localStorage.setItem(KEY, JSON.stringify({
      id: currentSongId, src: audio.src,
      title: titleEl.textContent, artist: artistEl.textContent, cover: coverEl.src,
      time: audio.currentTime, playing: !audio.paused
    }));
  }
  function initVolume(){
    const v = localStorage.getItem('player_volume');
    audio.volume = (v!=null) ? Number(v) : 0.7;
    paintVolume();
  }
  function loadState(){
    const raw = localStorage.getItem(KEY);
    if (!raw){ initVolume(); return; }
    const s = JSON.parse(raw||'{}');
    if (!s.src){ initVolume(); return; }

    currentSongId    = s.id || null;
    titleEl.textContent  = s.title  || 'Selecciona una canción';
    artistEl.textContent = s.artist || 'Artista';
    coverEl.src          = s.cover  || "{{ asset('img/default-cancion.png') }}";
    audio.src            = s.src;

    audio.addEventListener('loadedmetadata', () => {
      audio.currentTime = s.time || 0;
      uiSync();
      if (s.playing){
        audio.play().then(()=>{ playBtn.innerHTML='<i class="fas fa-pause"></i>'; })
          .catch(()=>{
            const resume=()=>{
              audio.play().then(()=>{ playBtn.innerHTML='<i class="fas fa-pause"></i>'; });
              document.removeEventListener('click',resume,true);
            };
            document.addEventListener('click',resume,true);
          });
      }
    }, { once:true });

    initVolume();
    if (currentSongId) checkLikeStatus(currentSongId);
  }
  setInterval(saveState, 1000);
  window.addEventListener('beforeunload', saveState);
  document.addEventListener('visibilitychange', ()=>{ if(document.visibilityState==='hidden') saveState(); });
  document.addEventListener('turbo:before-cache', ()=>{ saveState(); });
  loadState();

  likeBtn.addEventListener('click', ()=>{
    if (!currentSongId) return;
    fetch(`/canciones/${currentSongId}/like`, {
      method:'POST',
      headers:{ 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' }
    })
    .then(r=>r.json())
    .then(d=>{
      likeBtn.innerHTML = d.liked
        ? '<i class="fa-solid fa-heart" style="color:#aa029c"></i>'
        : '<i class="fa-regular fa-heart"></i>';
    }).catch(()=>{});
  });
  function checkLikeStatus(id){
    fetch(`/canciones/${id}/liked`, { headers:{ 'Accept':'application/json' } })
      .then(r=>r.json())
      .then(d=>{
        likeBtn.innerHTML = d.liked
          ? '<i class="fa-solid fa-heart" style="color:#aa029c"></i>'
          : '<i class="fa-regular fa-heart"></i>';
      }).catch(()=>{});
  }

  plBtn.addEventListener('click', ()=>{
    if (!currentSongId) return alert('Selecciona una canción primero 🎵');
    plModal.hidden = false;
    fetch('/api/my-playlists', { headers:{ 'Accept':'application/json' } })
      .then(r=>r.json())
      .then(list=>{
        plList.innerHTML='';
        if(!list || !list.length){ plList.innerHTML="<li style='opacity:.85'>No tienes playlists creadas</li>"; return; }
        list.forEach(pl=>{
          const li=document.createElement('li');
          li.innerHTML = `<span>${pl.nombre}</span><i class="fa-solid fa-plus"></i>`;
          li.onclick = ()=> addToPlaylist(pl.id);
          plList.appendChild(li);
        });
      }).catch(()=>{ plList.innerHTML="<li>Error cargando playlists</li>"; });
  });
  plClose.addEventListener('click', ()=> plModal.hidden = true);
  document.addEventListener('click', (e) => {
    if (plModal.hidden) return;
    const panel = plModal.querySelector('.playlist-modal-content');
    if (!panel.contains(e.target) && !plBtn.contains(e.target)) plModal.hidden = true;
  });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') plModal.hidden = true; });

  plCreate.addEventListener('click', ()=>{
    const name=(plInput.value||'').trim();
    if(!name) return alert('Escribe un nombre para la playlist');
    fetch('/api/playlists/create', {
      method:'POST',
      headers:{ 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json', 'Content-Type':'application/json' },
      body: JSON.stringify({ nombre:name })
    })
    .then(r=>r.json())
    .then(d=>{
      const li=document.createElement('li');
      li.innerHTML = `<span>${d.nombre||name}</span><i class="fa-solid fa-plus"></i>`;
      li.onclick=()=> addToPlaylist(d.id);
      plList.appendChild(li);
      plInput.value='';
      if(currentSongId) addToPlaylist(d.id);
    }).catch(()=> alert('No se pudo crear la playlist'));
  });
  function addToPlaylist(playlistId){
    fetch(`/playlists/${playlistId}/add-song/${currentSongId}`, {
      method:'POST',
      headers:{ 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' }
    })
    .then(r=>r.json())
    .then(d=>{ alert(d.message || 'Agregado a playlist ✅'); plModal.hidden = true; })
    .catch(()=> alert('No se pudo agregar a la playlist'));
  }

  // API del reproductor
  window.AuraPlayer = {
    play({id, src, title, artist, cover}){
      currentSongId = id || null;
      document.body.dataset.nowPlayingSrc = src || '';
      titleEl.textContent  = title  || 'Sin título';
      artistEl.textContent = artist || 'Artista';
      coverEl.src          = cover  || "{{ asset('img/default-cancion.png') }}";
      audio.src            = src || '';
      audio.play().then(()=>{ playBtn.innerHTML='<i class="fas fa-pause"></i>'; }).catch(()=>{});
      if (window.AuraQueue?.noteNowPlaying) window.AuraQueue.noteNowPlaying({id,src,title,artist,cover});
    }
  };

  // Botones externos .cancion-item
  function bindSongButtons(root=document){
    root.querySelectorAll('.cancion-item').forEach(btn=>{
      if(btn.dataset.bound) return;
      btn.addEventListener('click', ()=>{
        const s = {
          id: btn.dataset.id,
          src: btn.dataset.src,
          title: btn.dataset.title,
          artist: btn.dataset.artist,
          cover: btn.dataset.cover
        };
        if (window.AuraQueue?.externalPlay) window.AuraQueue.externalPlay(s);
        else window.AuraPlayer.play(s);
      });
      btn.dataset.bound='true';
    });
  }
  bindSongButtons();
  document.addEventListener('turbo:load', ()=> bindSongButtons(document));

  /* ========= Redimensionado lateral ========= */
  const handle = el.querySelector('.rp-resize-handle');
  const ROOT = document.documentElement;
  const STORAGE_KEY_W = 'player_width_px';
  const MAX_WIDTH = 380; // límite = el actual
  const MIN_WIDTH = 320; // se puede encoger un poquito
  const STEP = 10;

  // Cargar ancho guardado
  const savedW = Number(localStorage.getItem(STORAGE_KEY_W));
  if (savedW && savedW >= MIN_WIDTH && savedW <= MAX_WIDTH) {
    ROOT.style.setProperty('--player-width', savedW + 'px');
  }

  // mouse / touch
  let dragging = false;
  function onDown(e){
    dragging = true;
    el.classList.add('resizing');
    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', onUp, { once:true });
    document.addEventListener('touchmove', onMove, { passive:false });
    document.addEventListener('touchend', onUp, { once:true });
    e.preventDefault();
  }
  function getClientX(evt){
    return (evt.touches && evt.touches[0]) ? evt.touches[0].clientX : evt.clientX;
  }
  function onMove(e){
    if (!dragging) return;
    const x = getClientX(e);
    const w = Math.max(MIN_WIDTH, Math.min(MAX_WIDTH, window.innerWidth - x));
    ROOT.style.setProperty('--player-width', w + 'px');
    localStorage.setItem(STORAGE_KEY_W, String(w));
    e.preventDefault?.();
  }
  function onUp(){
    dragging = false;
    el.classList.remove('resizing');
    document.removeEventListener('mousemove', onMove);
    document.removeEventListener('touchmove', onMove);
  }
  handle.addEventListener('mousedown', onDown);
  handle.addEventListener('touchstart', onDown, { passive:false });

  // teclado accesible
  handle.addEventListener('keydown', (e)=>{
    const cur = parseInt(getComputedStyle(ROOT).getPropertyValue('--player-width'));
    if (e.key === 'ArrowLeft'){
      const w = Math.max(MIN_WIDTH, cur - STEP);
      ROOT.style.setProperty('--player-width', w + 'px');
      localStorage.setItem(STORAGE_KEY_W, String(w));
      e.preventDefault();
    } else if (e.key === 'ArrowRight'){
      const w = Math.min(MAX_WIDTH, cur + STEP);
      ROOT.style.setProperty('--player-width', w + 'px');
      localStorage.setItem(STORAGE_KEY_W, String(w));
      e.preventDefault();
    }
  });
})();
</script>
<!-- === /Script del reproductor === -->

<!-- === Script de Cola (persistente + sync + auto-shuffle en perfiles) === -->
<script>
(function(){
  const root   = document.getElementById('rightPlayer');
  if (!root) return;

  const listEl  = root.querySelector('#queueList');
  const emptyEl = root.querySelector('#queueEmpty');
  const countEl = root.querySelector('#queueCount');
  const DEF_COVER = "{{ asset('img/default-cancion.png') }}";

  // Identidad de usuario para aislar el storage por cuenta
  const USER_ID = @json(Auth::id());
  const SUFFIX  = (USER_ID ?? 'guest');

  // Claves de almacenamiento
  const STORAGE = {
    QUEUE   : `aura_queue_v1_${SUFFIX}`,
    SOURCE  : `aura_queue_source_v1_${SUFFIX}`, // {type:'profile'|'playlist'|..., id, seed}
    SETTINGS: `aura_settings_v1_${SUFFIX}`      // { shuffle:boolean }
  };

  // Canal de sincronización entre pestañas
  const TAB_ID = crypto.randomUUID();
  let bc = null;
  try { bc = new BroadcastChannel('aura-player'); } catch (_){}

  // Estado en memoria
  let queue = [];
  let nowPlaying = null;
  window._auraBackStack    = window._auraBackStack || [];
  window._auraForwardStack = window._auraForwardStack || [];
  let sourceInfo = null; // quién armó la cola
  let settings = readJSON(STORAGE.SETTINGS, { shuffle:false });

  // Utilidades
  function readJSON(key, fallback){
    try { const raw = localStorage.getItem(key); return raw ? JSON.parse(raw) : fallback; }
    catch { return fallback; }
  }
  function writeJSON(key, value){
    localStorage.setItem(key, JSON.stringify(value));
  }
  function fmtTime(s){
    if (!Number.isFinite(s)) return '--:--';
    return `${Math.floor(s/60)}:${String(Math.floor(s%60)).padStart(2,'0')}`;
  }
  function seeded(seed){
    let x = (seed>>>0) || (Math.random()*0xffffffff)>>>0;
    return ()=>{ x ^= x<<13; x ^= x>>>17; x ^= x<<5; return (x>>>0)/0xffffffff; };
  }
  function shuffleArray(arr, seed=null){
    const rnd = seed==null ? Math.random : seeded(seed);
    const a = arr.slice();
    for (let i=a.length-1;i>0;i--){
      const j = Math.floor(rnd()*(i+1));
      [a[i],a[j]] = [a[j],a[i]];
    }
    return a;
  }
  function normalizeSong(s){
    return {
      id:       s.id ?? s.ID ?? s.song_id ?? crypto.randomUUID(),
      title:    s.title ?? s.titulo ?? s.name ?? 'Sin título',
      artist:   s.artist ?? s.artista ?? 'Artista',
      cover:    s.cover ?? s.portada ?? DEF_COVER,
      src:      s.src ?? s.audio ?? s.audio_url ?? s.url ?? '',
      duration: s.duration ?? s.duracion ?? '--:--'
    };
  }
  function dedupeById(list){
    const out=[], seen=new Set();
    for (const s of list){
      if (!s?.id) continue;
      if (!seen.has(s.id)){ seen.add(s.id); out.push(s); }
    }
    return out;
  }

  // Persistencia + Sync
  function persistAll({silent=false}={}){
    writeJSON(STORAGE.QUEUE, queue);
    writeJSON(STORAGE.SOURCE, sourceInfo);
    writeJSON(STORAGE.SETTINGS, settings);
    updateCount();
    if (!silent) broadcast({ type:'QUEUE_UPDATED', from:TAB_ID });
  }
  function restoreAll(){
    queue      = (readJSON(STORAGE.QUEUE, [])||[]).map(normalizeSong);
    sourceInfo = readJSON(STORAGE.SOURCE, null);
    settings   = { shuffle:false, ...readJSON(STORAGE.SETTINGS, {}) };
    renderQueue();
  }
  function onStorage(e){
    if (!e) return;
    if ([STORAGE.QUEUE, STORAGE.SOURCE, STORAGE.SETTINGS].includes(e.key)){
      restoreAll();
    }
  }
  window.addEventListener('storage', onStorage);

  function broadcast(payload){ if (bc) try{ bc.postMessage(payload); } catch {} }
  if (bc){
    bc.onmessage = (ev)=>{
      const msg = ev.data;
      if (!msg || msg.from === TAB_ID) return;
      if (msg.type === 'REQUEST_QUEUE'){
        // Otra pestaña recién abrió y no tiene cola
        broadcast({ type:'PUSH_QUEUE', from:TAB_ID, queue, sourceInfo, settings });
      }
      if (msg.type === 'PUSH_QUEUE'){
        // Adoptamos solo si aquí no hay cola
        if (!queue.length){
          queue      = (msg.queue||[]).map(normalizeSong);
          sourceInfo = msg.sourceInfo || null;
          settings   = { shuffle:false, ...(msg.settings||{}) };
          persistAll({silent:true});
          renderQueue();
        }
      }
      if (msg.type === 'QUEUE_UPDATED'){
        restoreAll();
      }
    };
    // Si esta pestaña arranca “vacía”, pide a otra
    if (!readJSON(STORAGE.QUEUE, []).length){
      broadcast({ type:'REQUEST_QUEUE', from:TAB_ID });
    }
  }

  // Auto-shuffle en páginas de perfil
  function maybeAutoShuffleFromProfile(){
    const body = document.body;
    if (!body || body.dataset.page !== 'profile') return;
    const raw = body.dataset.profileSongs;
    if (!raw) return;

    let songs = [];
    try { songs = JSON.parse(raw); } catch { songs=[]; }
    if (!Array.isArray(songs) || !songs.length) return;

    const profileId = body.dataset.profileId || 'profile';

    const isDifferentSource =
      !sourceInfo ||
      sourceInfo.type !== 'profile' ||
      String(sourceInfo.id) !== String(profileId);

    if (isDifferentSource || !queue.length){
      setQueue(songs, { source:{type:'profile', id:profileId}, shuffle:true, persist:true });
    }
  }

  // API de Cola
  function setQueue(songs, {source=null, shuffle=false, persist=true}={}){
    const base  = dedupeById((songs||[]).map(normalizeSong));
    let final   = base;
    let seed    = null;
    if (shuffle){
      seed  = Date.now();
      final = shuffleArray(base, seed);
    }
    queue      = final;
    sourceInfo = source ? { ...source, seed } : null;
    renderQueue();
    if (persist) persistAll();
  }

  function addToEnd(songs, {dedupe=true}={}){
    const list = (songs||[]).map(normalizeSong);
    const seen = new Set(queue.map(s=>s.id));
    for (const s of list){
      if (!dedupe || !seen.has(s.id)){
        seen.add(s.id);
        queue.push(s);
      }
    }
    renderQueue(); persistAll();
  }

  function playFromQueue(index){
    if (index<0 || index>=queue.length) return;
    const s = queue[index];
    if (nowPlaying) window._auraBackStack.push(nowPlaying);
    window._auraForwardStack = [];
    window.AuraPlayer?.play(s);
    nowPlaying = s;

    // elimina el elemento reproducido de la cola
    queue.splice(index,1);
    renderQueue(); persistAll();
  }

  function back(){
    const prev = window._auraBackStack.pop();
    if (!prev) return;
    if (nowPlaying) window._auraForwardStack.push(nowPlaying);
    window.AuraPlayer?.play(prev);
    nowPlaying = prev;
  }

  function forwardOrNext(){
    if (window._auraForwardStack.length){
      const nx = window._auraForwardStack.pop();
      if (nowPlaying) window._auraBackStack.push(nowPlaying);
      window.AuraPlayer?.play(nx);
      nowPlaying = nx;
    } else {
      next();
    }
  }

  function next(){
    if (!queue.length) return;
    playFromQueue(0);
  }

  function onEnded(){
    window._auraForwardStack = [];
    next();
  }

  function noteNowPlaying(s){ nowPlaying = s || nowPlaying; }

  function externalPlay(s){
    const song = normalizeSong(s||{});
    // Si existe en cola, elimínala (evita duplicado)
    const idx = queue.findIndex(x => (song.id && x.id===song.id) || (!!song.src && x.src===song.src));
    if (nowPlaying) window._auraBackStack.push(nowPlaying);
    window._auraForwardStack = [];
    window.AuraPlayer?.play(song);
    nowPlaying = song;
    if (idx>=0){ queue.splice(idx,1); renderQueue(); persistAll(); }
  }

  // ====== Render + Drag & Drop ======
  let listListenersBound = false;

  function updateCount(){
    if (!countEl) return;
    const n = queue.length;
    countEl.textContent = n ? n : '';
    emptyEl.hidden = n>0;
  }

  function ensureDurations(){
    queue.forEach((s,i)=>{
      if (s.duration && s.duration!=='--:--') return;
      if (!s.src) return;
      const a = new Audio();
      a.preload = 'metadata';
      a.src = s.src;
      a.addEventListener('loadedmetadata', ()=>{
        s.duration = fmtTime(a.duration);
        const li = listEl.children[i];
        if (li){ const d = li.querySelector('.dur'); if (d) d.textContent = s.duration; }
        persistAll({silent:true});
      }, { once:true });
    });
  }

  const dropIndicator = document.createElement('li');
  dropIndicator.className = 'drop-indicator';

  function renderQueue(){
    listEl.innerHTML = '';
    queue.forEach((s, i)=>{
      const li = document.createElement('li');
      li.className = 'queue-item';
      li.setAttribute('draggable','true');
      li.dataset.index = i;

      li.innerHTML = `
        <span class="num">${i+1}</span>
        <button class="drag-handle" title="Arrastrar" aria-label="Arrastrar"><i class="fa-solid fa-grip-vertical"></i></button>
        <img src="${s.cover || DEF_COVER}" alt="cover">
        <div class="info">
          <b title="${s.title}">${s.title}</b>
          <small title="${s.artist}">${s.artist}</small>
        </div>
        <span class="dur">${s.duration || '--:--'}</span>
        <button class="qi-play" title="Reproducir"><i class="fa-solid fa-play"></i></button>
      `;

      const playThis = ()=> playFromQueue(i);
      li.addEventListener('click', playThis);
      li.querySelector('.qi-play').addEventListener('click', (e)=>{ e.stopPropagation(); playThis(); });

      li.addEventListener('dragstart', (e)=>{
        e.dataTransfer.setData('text/plain', String(i));
        li.classList.add('dragging');
      });
      li.addEventListener('dragend', ()=>{
        li.classList.remove('dragging');
        hideDropIndicator();
      });

      listEl.appendChild(li);
    });

    if (!listListenersBound){
      listEl.addEventListener('dragover', onDragOver);
      listEl.addEventListener('drop', onDrop);
      listListenersBound = true;
    }
    updateCount();
    ensureDurations();
  }

  function indexFromY(y){
    const items = [...listEl.querySelectorAll('.queue-item:not(.dragging)')];
    let closest = { offset: Number.NEGATIVE_INFINITY, index: items.length };
    items.forEach((child, idx)=>{
      const box = child.getBoundingClientRect();
      const offset = y - box.top - box.height/2;
      if (offset < 0 && offset > closest.offset){
        closest = { offset, index: idx };
      }
    });
    return closest.index;
  }
  function showDropIndicator(atIndex){
    const children = [...listEl.querySelectorAll('.queue-item:not(.dragging)')];
    if (atIndex >= children.length) {
      listEl.appendChild(dropIndicator);
    } else {
      listEl.insertBefore(dropIndicator, children[atIndex]);
    }
    requestAnimationFrame(()=> dropIndicator.classList.add('show'));
  }
  function hideDropIndicator(){
    dropIndicator.classList.remove('show');
    if (dropIndicator.parentNode){
      setTimeout(()=> dropIndicator.parentNode && dropIndicator.parentNode.removeChild(dropIndicator), 160);
    }
  }
  function onDragOver(e){ e.preventDefault(); const idx = indexFromY(e.clientY); showDropIndicator(idx); }
  function onDrop(e){
    e.preventDefault();
    const from = Number(e.dataTransfer.getData('text/plain'));
    const to   = [...listEl.querySelectorAll('.queue-item:not(.dragging), .drop-indicator')].indexOf(dropIndicator);
    hideDropIndicator();
    if (Number.isNaN(from) || to<0) return;
    const item = queue.splice(from,1)[0];
    queue.splice(to,0,item);
    renderQueue(); persistAll();
  }

  // ====== Inicialización ======
  function collectSongsFromDOM(){
    const btns = Array.from(document.querySelectorAll('.cancion-item'));
    return btns.map(btn => ({
      id     : btn.dataset.id || null,
      src    : btn.dataset.src || '',
      title  : btn.dataset.title || 'Sin título',
      artist : btn.dataset.artist || 'Artista',
      cover  : btn.dataset.cover || DEF_COVER,
      duration: btn.dataset.duration || '--:--'
    }));
  }

  function bootstrap(){
    // 1) Restaura lo que ya exista
    restoreAll();

    // 2) Si no había cola, intenta inicial con lo que hay en la página
    if (!queue.length){
      const fromDom = collectSongsFromDOM();
      if (fromDom.length){
        setQueue(fromDom, { source:{type:'page', id:location.pathname}, shuffle:false, persist:true });
      }
    }

    // 3) Si es un perfil, auto-shuffle si cambia el perfil o no hay cola
    maybeAutoShuffleFromProfile();

    // 4) Expone API global (usada por tu reproductor)
    window.AuraQueue = {
      render: renderQueue,
      syncFromDocument: ()=>{}, // ya no necesario (persistimos)
      externalPlay,
      back,
      forwardOrNext,
      next,
      onEnded,
      noteNowPlaying,
      addToEnd,
      setQueue, // por si quieres encolar manualmente
      toggleShuffle(force=null){
        const want = force==null ? !settings.shuffle : !!force;
        settings.shuffle = want;
        persistAll();
      },
      settings(){ return { ...settings }; },
      source(){ return sourceInfo; },
      queue(){ return queue.slice(); }
    };
  }

  // Listo
  bootstrap();

  // También re-intenta al cargar con Turbo
  document.addEventListener('turbo:load', bootstrap);
})();
</script>
<!-- === /Script de Cola === -->

<style>
:root{
  --player-width:380px; --player-gap:20px;
  --z-player:9999999; --z-popup:1500;
  --pl-bg-1:#0a0a0f; --pl-bg-2:#151521; --pl-line:rgba(255,255,255,.08);
  --pl-fg:#f8f9fb; --pl-fg-dim:#a8a9b8; --pl-accent:#7c3aed; --pl-accent2:#4c1d95; --pl-glow:rgba(76,29,149,.55);
}
.main-content{ margin-right: calc(var(--player-width) + var(--player-gap)); }
.header{ padding-right: calc(var(--player-width) + var(--player-gap)); }

#rightPlayer.player-card{
  position:fixed; top:0; right:0; bottom:0; width:var(--player-width);
  display:flex; flex-direction:column;
  background:linear-gradient(180deg,var(--pl-bg-2),var(--pl-bg-1));
  border-left:1px solid var(--pl-line);
  box-shadow:-8px 0 30px rgba(0,0,0,.8);
  z-index:var(--z-player); overflow:hidden;
}

/* Player (sin cambios de estilo general) */
#rightPlayer .current-song{
  padding:20px; display:flex; flex-direction:column; align-items:center;
  border-bottom:1px solid var(--pl-line);
  background:radial-gradient(circle at top, rgba(76,29,149,.15), transparent 70%);
}
#rightPlayer .img-wrap{ max-width:220px; aspect-ratio:1/1; border-radius:18px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,.75); transition:transform .6s ease }
#rightPlayer .img-wrap:hover{ transform:scale(1.05) rotate(-1deg) }
#rightPlayer .img-wrap img{ width:100%; height:100%; object-fit:cover }
#rightPlayer .song-name{ font-size:20px; font-weight:800; margin-top:14px; color:var(--pl-fg); text-align:center; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; max-width:85% }
#rightPlayer .song-autor{ font-size:13px; color:#b39ddb; margin-top:4px; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; max-width:80% }
#rightPlayer .time{ display:flex; justify-content:space-between; margin:12px auto 6px; width:85%; font-size:14px; color:var(--pl-fg); font-weight:600 }
#rightPlayer .seek{ -webkit-appearance:none; width:85%; height:10px; margin:0 auto; border-radius:999px; background:linear-gradient(to right, rgba(255,255,255,.2) 0%, rgba(255,255,255,.2) 100%); border:1px solid rgba(255,255,255,.1); cursor:pointer; box-shadow: inset 0 1px 2px rgba(0,0,0,.3); transition: all 0.2s ease }
#rightPlayer .seek:hover{ transform: scaleY(1.2); border-color: rgba(255,255,255,.3); box-shadow: inset 0 1px 2px rgba(0,0,0,.3), 0 0 8px rgba(124,58,237,.3) }
#rightPlayer .seek::-webkit-slider-thumb{ -webkit-appearance:none; width:16px; height:16px; border-radius:50%; background:linear-gradient(135deg,var(--pl-accent),var(--pl-accent2)); box-shadow:0 0 12px var(--pl-glow); transition:transform .2s; position:relative; z-index:2 }
#rightPlayer .seek::-webkit-slider-thumb:hover{ transform: scale(1.2); box-shadow:0 0 16px var(--pl-glow) }
#rightPlayer .controls{ display:flex; justify-content:center; align-items:center; gap:24px; margin-top:16px }
#rightPlayer .controls button{ border:none; background:none; font-size:20px; cursor:pointer; color:var(--pl-fg); transition:transform .25s, color .25s }
#rightPlayer .controls button:hover{ transform:scale(1.2); color:var(--pl-accent) }
#rightPlayer .controls .play-btn{ width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg,var(--pl-accent),var(--pl-accent2)); color:#fff; font-size:26px; box-shadow:0 0 25px var(--pl-glow); transition:transform .25s, box-shadow .25s }
#rightPlayer .controls .play-btn:hover{ transform:scale(1.12); box-shadow:0 0 35px var(--pl-glow) }
#rightPlayer .options{ display:flex; justify-content:center; align-items:center; gap:18px; padding:14px; border-top:1px solid var(--pl-line); background:var(--pl-bg-2) }
#rightPlayer .icon-btn{ background:none; border:none; color:var(--pl-fg); font-size:20px; cursor:pointer; transition:.25s }
#rightPlayer .icon-btn:hover{ color:var(--pl-accent); transform:scale(1.12) }
#rightPlayer .vol-range{ -webkit-appearance:none; width:120px; height:6px; border-radius:999px; background:linear-gradient(to right,var(--pl-accent) 0%, var(--pl-accent2) 70%, #35354f 70%, #35354f 100%); cursor:pointer }
#rightPlayer .vol-range::-webkit-slider-thumb{ -webkit-appearance:none; width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid var(--pl-accent2); box-shadow:0 0 8px var(--pl-glow) }

/* === Lista (pegada a los bordes) === */
#rightPlayer .play-list{
  flex:1; background:var(--pl-bg-1); padding:0; overflow-y:auto; position:relative;
}
#rightPlayer .play-list::-webkit-scrollbar{ width:6px }
#rightPlayer .play-list::-webkit-scrollbar-thumb{ background:linear-gradient(180deg,var(--pl-accent),var(--pl-accent2)); border-radius:999px }

/* Título sticky (no se va atrás) */
#rightPlayer .queue-title{
  position:sticky; top:0; z-index:5;
  background:linear-gradient(180deg, rgba(21,21,33,.98) 0%, rgba(21,21,33,.92) 100%);
  backdrop-filter:saturate(140%) blur(3px);
  margin:0; padding:12px 14px; border-bottom:1px solid var(--pl-line);
}
#rightPlayer .queue-title-inner{ display:flex; align-items:center; gap:10px; font-weight:900; color:#e7d7ff; letter-spacing:.2px; }
#rightPlayer .queue-title .count-badge{ background:#2e2a4b; color:#d9ccff; font-weight:800; font-size:11px; padding:2px 8px; border-radius:999px; border:1px solid #3c356a; }

#rightPlayer #queueList{ list-style:none; margin:0; padding:0; }
#rightPlayer #queueList .queue-item{
  display:grid; grid-template-columns: 28px 28px 48px 1fr auto auto; gap:12px;
  align-items:center; width:100%;
  background:#191a2a; border-radius:0;
  padding:12px 14px;
  border-top:1px solid var(--pl-line);
  transition:transform .15s ease, background .15s ease, border-color .15s ease, box-shadow .15s ease;
  cursor:pointer;
}
#rightPlayer #queueList .queue-item:first-child{ border-top:0 }
#rightPlayer #queueList .queue-item:hover{ background:#23243a; border-color:#2e3051; transform:translateY(-1px); box-shadow:0 6px 20px rgba(0,0,0,.25) }
#rightPlayer #queueList .queue-item.dragging{
  cursor:grabbing; opacity:.9; transform:scale(1.02); z-index:3; background:#272842; box-shadow:0 10px 30px rgba(0,0,0,.35)
}
#rightPlayer #queueList .queue-item .num{ text-align:right; font-size:12px; opacity:.75 }
#rightPlayer #queueList .queue-item .drag-handle{ background:transparent; border:none; color:#8c8fb3; cursor:grab }
#rightPlayer #queueList .queue-item img{ width:48px; height:48px; border-radius:10px; object-fit:cover }
#rightPlayer #queueList .queue-item .info{ display:flex; flex-direction:column; min-width:0 }
#rightPlayer #queueList .queue-item .info b{ font-size:14px; line-height:1.15; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
#rightPlayer #queueList .queue-item .info small{ font-size:12px; opacity:.8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
#rightPlayer #queueList .queue-item .dur{ font-size:12px; opacity:.8; padding:0 6px }
#rightPlayer #queueList .queue-item .qi-play{ background:#2b2d46; color:#fff; border:none; border-radius:9px; padding:8px 12px; cursor:pointer }
#rightPlayer #queueList .queue-item .qi-play:hover{ background:#3a3d62 }

/* Indicador de drop con animación (apertura) */
#rightPlayer #queueList .drop-indicator{
  width:100%;
  height:0; margin:0; border-radius:0;
  border:2px dashed #6d64c4; background:rgba(109,100,196,.10);
  opacity:0; transition: height .18s ease, margin .18s ease, opacity .15s ease;
}
#rightPlayer #queueList .drop-indicator.show{
  height:58px; margin:8px 0; opacity:1;
}

/* Mensaje vacío */
#rightPlayer .queue-empty{
  opacity:.7; font-size:13px; padding:14px; border-top:1px dashed #2b2d46;
}

/* Animación al quitar (por reproducirse) */
@keyframes queueRemove {
  0% { opacity:1; transform:translateX(0) scale(1); }
  60%{ opacity:.35; transform:translateX(18px) scale(.98); }
  100%{ opacity:0; transform:translateX(60px) scale(.96); height:0; margin:0; padding-top:0; padding-bottom:0; }
}
#rightPlayer #queueList .queue-item.removing{ animation:queueRemove .32s ease forwards }

/* ===== Asidero de redimensionado ===== */
#rightPlayer .rp-resize-handle{
  position:absolute; top:0; left:-6px; width:12px; height:100%;
  cursor:ew-resize; background:transparent; border:0; padding:0; margin:0;
  outline:none; z-index:6;
}
/* Capa base: sin morado por defecto */
#rightPlayer .rp-resize-handle::before{
  content:""; position:absolute; inset:0;
  background:transparent;                         /* neutral por defecto */
  border-left:1px solid var(--pl-line);
  border-right:1px solid transparent;
  transition:background .2s, box-shadow .2s, border-color .2s;
}
/* Grip con puntitos (siempre, tenue) */
#rightPlayer .rp-resize-handle::after{
  content:""; position:absolute; left:2px; top:50%; transform:translateY(-50%);
  width:8px; height:36px;
  background:
    radial-gradient(circle, #8c8fb3 35%, transparent 36%) 0 4px/4px 8px repeat-y,
    radial-gradient(circle, #8c8fb3 35%, transparent 36%) 4px 4px/4px 8px repeat-y;
  opacity:.45; pointer-events:none;
}

/* Morado SOLO en hover/drag/teclado */
#rightPlayer .rp-resize-handle:hover::before,
#rightPlayer.resizing .rp-resize-handle::before,
#rightPlayer .rp-resize-handle:focus-visible::before{
  background:linear-gradient(180deg, rgba(124,58,237,.16), rgba(76,29,149,.16));
  box-shadow:0 0 18px var(--pl-glow) inset, 0 0 18px var(--pl-glow);
  border-left-color:rgba(124,58,237,.8);
  border-right-color:rgba(76,29,149,.8);
}

/* Modal playlists */
#rightPlayer .playlist-modal{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,.35); backdrop-filter: blur(3px); z-index:var(--z-popup) }
#rightPlayer .playlist-modal[hidden]{ display:none }
#rightPlayer .playlist-modal-content{ min-width:320px; max-width:420px; background:#191a2a; border:1px solid #2b2d46; border-radius:16px; padding:18px; color:#fff; box-shadow:0 20px 60px rgba(0,0,0,.55) }
#rightPlayer .playlist-modal-content h3{ margin:0 0 10px 0; font-size:16px; font-weight:800 }
#rightPlayer .playlist-modal-content ul{ list-style:none; margin:0 0 10px 0; padding:0; max-height:220px; overflow:auto }
#rightPlayer .playlist-modal-content li{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px; border-radius:10px; cursor:pointer; transition:background .2s ease }
#rightPlayer .playlist-modal-content li:hover{ background:rgba(255,255,255,.06) }
#rightPlayer .playlist-modal-content .new-playlist{ display:flex; gap:8px; margin-top:10px }
#rightPlayer .playlist-modal-content input[type="text"]{ flex:1; border-radius:10px; border:1px solid #2b2d46; background:#0f1020; color:#fff; padding:8px 10px }
#rightPlayer .playlist-modal-content button{ border:none; border-radius:10px; background:#4c1d95; color:#fff; padding:8px 12px; cursor:pointer }
#rightPlayer .playlist-modal-content .close-playlist-modal{ margin-top:12px; background:#2d2f48 }

@media (max-width:1024px){ :root{ --player-width:320px } }
@media (max-width:860px){
  :root{ --player-width:0px }
  #rightPlayer.player-card{ display:none }
  .main-content{ margin-right:var(--player-gap) }
}
</style>
