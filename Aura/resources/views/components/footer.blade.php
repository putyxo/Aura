{{-- resources/views/components/footer.blade.php --}}
<div id="rightPlayer" class="player-card" data-turbo-permanent>
  <div class="current-song">
    <div class="img-wrap"><img class="cover" src="{{ asset('img/default-cancion.png') }}" alt=""></div>
    <span class="song-name">Selecciona una canción</span>
    <span class="song-autor">Artista</span>
    <div class="time">
      <span class="current-time">0:00</span>
      <span class="total-time">--:--</span>
    </div>
    <input type="range" class="seek" min="0" max="100" value="0" step="any">
    <div class="controls">
      <button class="prev" title="Reiniciar"><i class="fas fa-backward"></i></button>
      <button class="play-btn" title="Play/Pause"><i class="fas fa-play"></i></button>
      <button class="next" title="Fin"><i class="fas fa-forward"></i></button>
    </div>
  </div>

  <div class="options">
    <button class="icon-btn vol-toggle" title="Silenciar"><i class="fa-solid fa-volume-low"></i></button>
    <input class="vol-range" type="range" min="0" max="100" value="70" title="Volumen">
    <button id="likeBtn" class="icon-btn" title="Me gusta"><i class="fa-regular fa-heart"></i></button>
    <button id="playlistDropdown" class="icon-btn" title="Agregar a playlist"><i class="fa-solid fa-plus"></i></button>
  </div>

  <div class="play-list"></div>

  <!-- Panel flotante, no bloquea la página -->
  <div id="playlistModal" class="playlist-modal" hidden>
    <div class="playlist-modal-content">
      <h3>Agregar a Playlist</h3>
      <ul id="modalPlaylists"></ul>
      <div class="new-playlist">
        <input type="text" id="newPlaylistName" placeholder="Nueva playlist...">
        <button id="createPlaylistBtn">Crear</button>
      </div>
      <button class="close-playlist-modal">Cancelar</button>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.4/dist/turbo.es2017-umd.js" defer></script>
<script>
(() => {
  if (window.__AURA_PLAYER_INIT__) return;
  window.__AURA_PLAYER_INIT__ = true;

  const el = document.getElementById('rightPlayer'); if(!el) return;

  // ===== Audio único del sitio =====
  const audio = new Audio();
  audio.preload = 'metadata';
  audio.playsInline = true;

  // ===== Refs UI =====
  const playBtn  = el.querySelector('.play-btn');
  const prevBtn  = el.querySelector('.prev');
  const nextBtn  = el.querySelector('.next');
  const seek     = el.querySelector('.seek');
  const curEl    = el.querySelector('.current-time');
  const totEl    = el.querySelector('.total-time');
  const volRange = el.querySelector('.vol-range');
  const volTgl   = el.querySelector('.vol-toggle');
  const cover    = el.querySelector('.cover');
  const title    = el.querySelector('.song-name');
  const artist   = el.querySelector('.song-autor');

  const likeBtn  = el.querySelector('#likeBtn');
  const plBtn    = el.querySelector('#playlistDropdown');
  const plModal  = el.querySelector('#playlistModal');
  const plList   = el.querySelector('#modalPlaylists');
  const plClose  = el.querySelector('.close-playlist-modal');
  const plInput  = el.querySelector('#newPlaylistName');
  const plCreate = el.querySelector('#createPlaylistBtn');

  const CSRF  = (document.querySelector('meta[name="csrf-token"]')?.content) || '{{ csrf_token() }}';
  const userId = @json(Auth::id());
  const KEY   = 'player_state_' + userId;

  let currentSongId = null;
  let raf = null, ticker = null, lastVolume = 0.7;

  const fmt = s => !Number.isFinite(s) ? '--:--' : `${Math.floor(s/60)}:${String(Math.floor(s%60)).padStart(2,'0')}`;
  const paintSeek = p => {
    const x = Math.max(0, Math.min(100, p||0));
    seek.style.background = `linear-gradient(to right,var(--pl-accent) 0%,var(--pl-accent2) ${x}%,var(--pl-line) ${x}%,var(--pl-line) 100%)`;
  };
  const paintByTime = () => { if(!audio.duration) return paintSeek(0); paintSeek((audio.currentTime/audio.duration)*100); }
  const paintVolume = () => {
    const p = Math.round((audio.volume||0)*100);
    volRange.value = p;
    volRange.style.background = `linear-gradient(to right,var(--pl-accent) 0%,var(--pl-accent2) ${p}%,#35354f ${p}%,#35354f 100%)`;
  };

  // ===== UI Sync (siempre activo aunque cambies de sección) =====
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
    if (!ticker) ticker = setInterval(uiSync, 500); // respaldo si rAF se pausa
  }
  ensureTicker(); // comienza desde ya (no depende de play)

  // ===== Controles =====
  playBtn.addEventListener('click', () => {
    if (audio.paused){
      audio.play().then(()=>{ playBtn.innerHTML='<i class="fas fa-pause"></i>'; }).catch(()=>{});
    }else{
      audio.pause(); playBtn.innerHTML='<i class="fas fa-play"></i>';
    }
  });
  prevBtn.addEventListener('click', ()=> audio.currentTime = 0);
  nextBtn.addEventListener('click', ()=> audio.currentTime = audio.duration || 0);
  seek.addEventListener('input', ()=> { audio.currentTime = Number(seek.value)||0; paintByTime(); });

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

  audio.addEventListener('loadedmetadata', ()=> { uiSync(); });
  audio.addEventListener('play',  ()=> { playBtn.innerHTML='<i class="fas fa-pause"></i>'; });
  audio.addEventListener('pause', ()=> { playBtn.innerHTML='<i class="fas fa-play"></i>'; });
  audio.addEventListener('timeupdate', uiSync);
  audio.addEventListener('ended', ()=> { playBtn.innerHTML='<i class="fas fa-play"></i>'; });

  // ===== Persistencia =====
  function saveState(){
    if (!audio.src) return;
    localStorage.setItem(KEY, JSON.stringify({
      id: currentSongId, src: audio.src,
      title: title.textContent, artist: artist.textContent, cover: cover.src,
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

    currentSongId = s.id || null;
    title.textContent  = s.title  || 'Selecciona una canción';
    artist.textContent = s.artist || 'Artista';
    cover.src          = s.cover  || "{{ asset('img/default-cancion.png') }}";
    audio.src          = s.src;

    audio.addEventListener('loadedmetadata', () => {
      audio.currentTime = s.time || 0;
      uiSync();
      if (s.playing){
        audio.play().then(()=>{ playBtn.innerHTML='<i class="fas fa-pause"></i>'; })
          .catch(()=>{ const resume=()=>{ audio.play().then(()=>{ playBtn.innerHTML='<i class="fas fa-pause"></i>'; }); document.removeEventListener('click',resume,true); }; document.addEventListener('click',resume,true); });
      }
    }, { once:true });

    initVolume();
    if (currentSongId) checkLikeStatus(currentSongId);
  }
  setInterval(saveState, 1000);
  window.addEventListener('beforeunload', saveState);
  document.addEventListener('visibilitychange', ()=>{ if(document.visibilityState==='hidden') saveState(); });
  document.addEventListener('turbo:before-cache', ()=>{ saveState(); /* NO paramos el loop */ });
  loadState();

  // ===== Likes =====
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

  // ===== Playlists =====
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
          li.textContent = pl.nombre;
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
      li.textContent=d.nombre||name;
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

  // ===== API global para reproducir desde cualquier parte =====
  window.AuraPlayer = {
    play({id, src, title:t, artist:a, cover:c}){
      currentSongId = id || null;
      title = title || el.querySelector('.song-name'); // seguridad si rebind
      artist = artist || el.querySelector('.song-autor');
      (el.querySelector('.song-name')).textContent  = t || 'Sin título';
      (el.querySelector('.song-autor')).textContent = a || 'Artista';
      cover.src = c || "{{ asset('img/default-cancion.png') }}";
      audio.src = src || '';
      audio.play().then(()=>{ playBtn.innerHTML='<i class="fas fa-pause"></i>'; }).catch(()=>{});
      if (currentSongId) checkLikeStatus(currentSongId);
      saveState();
    }
  };

  // Engancha cards con clase .cancion-item
  function bindSongButtons(root=document){
    root.querySelectorAll('.cancion-item').forEach(btn=>{
      if(btn.dataset.bound) return;
      btn.addEventListener('click', ()=>{
        window.AuraPlayer.play({
          id: btn.dataset.id,
          src: btn.dataset.src,
          title: btn.dataset.title,
          artist: btn.dataset.artist,
          cover: btn.dataset.cover
        });
      });
      btn.dataset.bound='true';
    });
  }
  bindSongButtons();
  document.addEventListener('turbo:load', ()=> bindSongButtons(document));
})();
</script>
@endpush

<style>
:root{
  --player-width:380px; --player-gap:20px;
  --z-player:1300; --z-popup:5;

  --pl-bg-1:#0a0a0f; --pl-bg-2:#151521; --pl-line:rgba(255,255,255,.08);
  --pl-fg:#f8f9fb; --pl-fg-dim:#a8a9b8; --pl-accent:#3a0ca3; --pl-accent2:#4c1d95; --pl-glow:rgba(76,29,149,.55);
}

/* Empuja tu contenido si usas .main-content / header fijo */
.main-content{ margin-right: calc(var(--player-width) + var(--player-gap)); }
.header{ padding-right: calc(var(--player-width) + var(--player-gap)); }

/* Player SIN animación de aparición */
.player-card{
  position:fixed; top:0; right:0; bottom:0; width:var(--player-width);
  display:flex; flex-direction:column;
  background:linear-gradient(180deg,var(--pl-bg-2),var(--pl-bg-1));
  border-left:1px solid var(--pl-line);
  box-shadow:-8px 0 30px rgba(0,0,0,.8);
  z-index:var(--z-player); overflow:hidden;
}

.current-song{ padding:20px; display:flex; flex-direction:column; align-items:center; border-bottom:1px solid var(--pl-line); background:radial-gradient(circle at top, rgba(76,29,149,.15), transparent 70%) }
.img-wrap{ max-width:220px; aspect-ratio:1/1; border-radius:18px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,.75); transition:transform .6s ease }
.img-wrap:hover{ transform:scale(1.05) rotate(-1deg) }
.img-wrap img{ width:100%; height:100%; object-fit:cover }

.song-name{ font-size:20px; font-weight:700; margin-top:14px; color:var(--pl-fg); text-align:center }
.song-autor{ font-size:14px; color:var(--pl-accent2); margin-top:4px }

.time{ display:flex; justify-content:space-between; margin:12px auto 6px; width:85%; font-size:12px; color:var(--pl-fg-dim); font-weight:500 }
.seek{
  -webkit-appearance:none; width:85%; height:8px; margin:0 auto; border-radius:999px;
  background:linear-gradient(to right,var(--pl-accent) 0%, var(--pl-accent2) 0%, var(--pl-line) 0%, var(--pl-line) 100%); cursor:pointer
}
.seek::-webkit-slider-thumb{ -webkit-appearance:none; width:16px; height:16px; border-radius:50%; background:linear-gradient(135deg,var(--pl-accent),var(--pl-accent2)); box-shadow:0 0 12px var(--pl-glow); transition:transform .2s; position:relative; z-index:2 }
.seek::-webkit-slider-thumb:hover{ transform:scale(1.25) }

.controls{ display:flex; justify-content:center; align-items:center; gap:24px; margin-top:16px }
.controls button{ border:none; background:none; font-size:20px; cursor:pointer; color:var(--pl-fg); transition:transform .25s, color .25s }
.controls button:hover{ transform:scale(1.2); color:var(--pl-accent2) }
.controls .play-btn{ width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg,var(--pl-accent),var(--pl-accent2)); color:#fff; font-size:26px; box-shadow:0 0 25px var(--pl-glow); transition:transform .25s, box-shadow .25s }
.controls .play-btn:hover{ transform:scale(1.12); box-shadow:0 0 35px var(--pl-glow) }

.options{ display:flex; justify-content:center; align-items:center; gap:18px; padding:14px; border-top:1px solid var(--pl-line); background:var(--pl-bg-2) }
.icon-btn{ background:none; border:none; color:var(--pl-fg); font-size:20px; cursor:pointer; transition:.25s }
.icon-btn:hover{ color:var(--pl-accent2); transform:scale(1.2) rotate(-6deg) }
.vol-range{ -webkit-appearance:none; width:120px; height:6px; border-radius:999px; background:linear-gradient(to right,var(--pl-accent) 0%, var(--pl-accent2) 70%, #35354f 70%, #35354f 100%); cursor:pointer }
.vol-range::-webkit-slider-thumb{ -webkit-appearance:none; width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid var(--pl-accent2); box-shadow:0 0 8px var(--pl-glow) }

.play-list{ flex:1; background:var(--pl-bg-1); padding:16px; overflow-y:auto }
.play-list::-webkit-scrollbar{ width:6px }
.play-list::-webkit-scrollbar-thumb{ background:linear-gradient(180deg,var(--pl-accent),var(--pl-accent2)); border-radius:999px }

/* Panel de playlists dentro del player (no cubre toda la app) */
.playlist-modal{ position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:auto; padding:0; background:transparent; pointer-events:none; z-index:var(--z-popup) }
.playlist-modal[hidden]{ display:none }
.playlist-modal-content{ min-width:320px; max-width:420px; background:#191a2a; border:1px solid #2b2d46; border-radius:16px; padding:18px; color:#fff; pointer-events:auto; box-shadow:0 20px 60px rgba(0,0,0,.55) }
.playlist-modal-content h3{ margin:0 0 10px 0; font-size:16px; font-weight:800 }
.playlist-modal-content ul{ list-style:none; margin:0 0 10px 0; padding:0; max-height:220px; overflow:auto }
.playlist-modal-content li{ padding:8px 10px; border-radius:10px; cursor:pointer; transition:background .2s ease }
.playlist-modal-content li:hover{ background:rgba(255,255,255,.06) }
.playlist-modal-content .new-playlist{ display:flex; gap:8px; margin-top:10px }
.playlist-modal-content input[type="text"]{ flex:1; border-radius:10px; border:1px solid #2b2d46; background:#0f1020; color:#fff; padding:8px 10px }
.playlist-modal-content button{ border:none; border-radius:10px; background:#4c1d95; color:#fff; padding:8px 12px; cursor:pointer }
.playlist-modal-content .close-playlist-modal{ margin-top:12px; background:#2d2f48 }

@media (max-width:1024px){ :root{ --player-width:320px } }
@media (max-width:860px){
  :root{ --player-width:0px }
  .player-card{ display:none }
  .main-content{ margin-right:var(--player-gap) }
}
</style>
