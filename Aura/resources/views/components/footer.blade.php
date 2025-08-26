<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/2.0.1/jquery.pjax.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
:root{
  --pl-h: 88px;
  --pl-fg:#f5f6fa;
  --pl-fg-dim:#b7b8c8;
  --pl-line:#26263a;
  --pl-accent:#aa029c;
  --pl-accent2:#6b3df6;
  --color-bg-1:#000;
  --color-bg-2:#090212;
}

body.has-player{ padding-bottom: var(--pl-h); }

.fusion-player{
  position:fixed; inset:auto 0 0 0; height:var(--pl-h);
  display:grid; grid-template-columns: 1fr minmax(360px,1.2fr) 1fr;
  align-items:center; gap:16px;
  padding:10px 18px;
  background:linear-gradient(90deg,var(--color-bg-2),var(--color-bg-1));
  border-top:1px solid var(--pl-line);
  box-shadow:0 -18px 40px rgba(0,0,0,.45);
  z-index:999;
  font-family:system-ui, sans-serif;
  color:var(--pl-fg);
}
.track-info{display:flex;align-items:center;gap:14px;min-width:0;}
.track-cover{width:58px;height:58px;border-radius:12px;object-fit:cover;}
.track-title{margin:0;font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.track-artist{margin:2px 0 0;font-size:12px;color:var(--pl-fg-dim);}
.controls{display:flex;flex-direction:column;align-items:center;gap:6px;}
.main-buttons{display:flex;align-items:center;gap:18px;}
.play-btn{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--pl-fg),#e9e9ee);color:#121212;}
.progress{display:flex;align-items:center;gap:10px;width:min(560px,92%);}
.time{font-size:12px;color:var(--pl-fg-dim);}
.seek{width:100%;height:6px;border-radius:999px;background:#2a2a3d;cursor:pointer;}
.seek::-webkit-slider-thumb{appearance:none;width:14px;height:14px;border-radius:50%;background:var(--pl-fg);}
.options{display:flex;align-items:center;gap:14px;justify-content:flex-end;}
.icon-btn { background:none;border:none;color:var(--pl-fg);font-size:18px;cursor:pointer; }

/* 📂 Modal playlists */
.playlist-modal {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.playlist-modal[hidden] { display: none; }
.playlist-modal-content {
  background: var(--color-bg-2);
  padding: 20px;
  border-radius: 12px;
  width: 320px;
  box-shadow: 0 6px 18px rgba(0,0,0,.5);
}
.playlist-modal-content h3 { margin:0 0 12px; font-size:18px; color: var(--pl-fg);}
.playlist-modal-content ul { list-style:none; margin:0; padding:0; max-height:200px; overflow-y:auto;}
.playlist-modal-content li { padding:8px 12px; cursor:pointer; border-radius:6px;}
.playlist-modal-content li:hover { background: var(--pl-line);}
.new-playlist { margin: 12px 0; display:flex; gap:6px;}
.new-playlist input { flex:1; padding:6px; border-radius:6px; border:1px solid var(--pl-line); background: var(--color-bg-1); color: var(--pl-fg);}
.new-playlist button { padding:6px 10px; background: var(--pl-accent); color:white; border:none; border-radius:6px; cursor:pointer;}
.new-playlist button:hover { background: var(--pl-accent2);}
.close-playlist-modal {
  margin-top: 10px; width: 100%;
  padding: 8px; border: none;
  background: var(--pl-accent); color: white;
  border-radius: 8px; cursor: pointer;
}
.close-playlist-modal:hover { background: var(--pl-accent2);}
</style>

<footer class="fusion-player">
  <div class="track-info">
    <img src="{{ asset('img/default-cancion.png') }}" class="track-cover" alt="Portada de la canción">
    <div>
      <h4 class="track-title">Selecciona una canción</h4>
      <p class="track-artist">Artista</p>
    </div>
  </div>

  <div class="controls">
    <div class="main-buttons">
      <button class="ctl prev"><i class="fa-solid fa-backward-step"></i></button>
      <button class="ctl play-btn"><i class="fa-solid fa-play"></i></button>
      <button class="ctl next"><i class="fa-solid fa-forward-step"></i></button>
    </div>
    <div class="progress">
      <span class="time current-time">0:00</span>
      <input type="range" class="seek" min="0" max="100" value="0" step="any">
      <span class="time total-time">--:--</span>
    </div>
  </div>

  <div class="options">
    <div class="volume">
      <button class="icon-btn vol-toggle"><i class="fa-solid fa-volume-low"></i></button>
      <input class="vol-range" type="range" min="0" max="100" value="70">
    </div>

    <button id="likeBtn" class="icon-btn"><i class="fa-regular fa-heart"></i></button>
    <button id="playlistDropdown" class="icon-btn"><i class="fa-solid fa-plus"></i></button>
  </div>
</footer>

<!-- 📂 Modal de agregar a playlist -->
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

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add('has-player');

  const audio=new Audio(); audio.preload='metadata';
  const playBtn=document.querySelector('.play-btn');
  const seek=document.querySelector('.seek');
  const currentTimeEl=document.querySelector('.current-time');
  const totalTimeEl=document.querySelector('.total-time');
  const volRange=document.querySelector('.vol-range');
  const volToggle=document.querySelector('.vol-toggle');
  const likeBtn=document.getElementById("likeBtn");
  const playlistDropdown=document.getElementById("playlistDropdown");

  // elementos de track
  const titleEl=document.querySelector('.track-title');
  const artistEl=document.querySelector('.track-artist');
  const coverEl=document.querySelector('.track-cover');

  // modal playlist
  const playlistModal=document.getElementById("playlistModal");
  const modalPlaylists=document.getElementById("modalPlaylists");
  const closePlaylistModal=document.querySelector(".close-playlist-modal");
  const newPlaylistInput=document.getElementById("newPlaylistName");
  const createPlaylistBtn=document.getElementById("createPlaylistBtn");

  let lastVolume=0.7;
  let currentSongId=null;

  const fmt=s=>!Number.isFinite(s)?'--:--':`${Math.floor(s/60)}:${String(Math.floor(s%60)).padStart(2,'0')}`;

  function updateProgress(){
    seek.value=audio.currentTime;
    currentTimeEl.textContent=fmt(audio.currentTime);
    requestAnimationFrame(updateProgress);
  }

  playBtn.addEventListener('click',()=>{
    if(audio.paused){audio.play();playBtn.innerHTML='<i class="fa-solid fa-pause"></i>';}
    else{audio.pause();playBtn.innerHTML='<i class="fa-solid fa-play"></i>';}
  });
  audio.addEventListener('play',()=>requestAnimationFrame(updateProgress));
  audio.addEventListener('loadedmetadata',()=>{seek.max=audio.duration;totalTimeEl.textContent=fmt(audio.duration);});
  seek.addEventListener('input',()=>{audio.currentTime=Number(seek.value)||0;});
  volRange.addEventListener('input',()=>{audio.volume=Number(volRange.value)/100;});
  volToggle.addEventListener('click',()=>{
    if(audio.volume>0){lastVolume=audio.volume;audio.volume=0;volRange.value=0;}
    else{audio.volume=lastVolume||0.7;volRange.value=Math.round(audio.volume*100);}
    volRange.dispatchEvent(new Event('input'));
  });

  // === Persistencia entre pestañas ===
  const currentUserId = @json(Auth::id());

  function savePlayerState() {
    if (!audio.src) return;
    localStorage.setItem("player_state_" + currentUserId, JSON.stringify({
      id: currentSongId,
      src: audio.src,
      title: titleEl.textContent,
      artist: artistEl.textContent,
      cover: coverEl.src,
      time: audio.currentTime,
      playing: !audio.paused
    }));
  }

  function loadPlayerState() {
    const saved = JSON.parse(localStorage.getItem("player_state_" + currentUserId) || "{}");
    if (saved.src) {
      currentSongId = saved.id || null;
      audio.src = saved.src;
      titleEl.textContent = saved.title || "Selecciona una canción";
      artistEl.textContent = saved.artist || "Artista";
      coverEl.src = saved.cover || "{{ asset('img/default-cancion.png') }}";

      audio.addEventListener("loadedmetadata", () => {
        audio.currentTime = saved.time || 0;
        if (saved.playing) audio.play();
      }, { once: true });

      if (currentSongId) checkLikeStatus(currentSongId);
    }
  }

  setInterval(savePlayerState, 1000);
  loadPlayerState();

  // ❤️ Toggle Like
  likeBtn?.addEventListener("click", () => {
    if (!currentSongId) return;
    fetch(`/canciones/${currentSongId}/like`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        "Accept": "application/json"
      }
    })
    .then(r => r.json())
    .then(data => {
      likeBtn.innerHTML = data.liked
        ? '<i class="fa-solid fa-heart" style="color:#aa029c"></i>' 
        : '<i class="fa-regular fa-heart"></i>';
    });
  });

  // 📂 Abrir modal playlists
  playlistDropdown?.addEventListener("click", () => {
    if (!currentSongId) return alert("Selecciona una canción primero 🎵");
    playlistModal.hidden = false;
    fetch("/api/my-playlists", { headers: { "Accept": "application/json" } })
      .then(r => r.json())
      .then(data => {
        modalPlaylists.innerHTML = "";
        if (data.length === 0) {
          modalPlaylists.innerHTML = "<li>No tienes playlists creadas</li>";
        } else {
          data.forEach(pl => {
            const li = document.createElement("li");
            li.textContent = pl.nombre;
            li.onclick = () => addSongToPlaylist(pl.id);
            modalPlaylists.appendChild(li);
          });
        }
      });
  });

  closePlaylistModal.addEventListener("click", () => playlistModal.hidden = true);
  playlistModal.addEventListener("click", (e) => {
    if (e.target === playlistModal) playlistModal.hidden = true;
  });

  // ➕ Crear nueva playlist y agregar canción actual
  createPlaylistBtn.addEventListener("click", () => {
    const name = newPlaylistInput.value.trim();
    if (!name) return alert("Escribe un nombre para la playlist");

    fetch("/api/playlists/create", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ nombre: name })
    })
    .then(r => r.json())
    .then(data => {
      alert(data.message);

      if (currentSongId) {
        addSongToPlaylist(data.id);
      }

      const li = document.createElement("li");
      li.textContent = data.nombre;
      li.onclick = () => addSongToPlaylist(data.id);
      modalPlaylists.appendChild(li);

      newPlaylistInput.value = "";
    });
  });

  function addSongToPlaylist(playlistId) {
    fetch(`/playlists/${playlistId}/add-song/${currentSongId}`, {
      method: "POST",
      headers: { 
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        "Accept": "application/json"
      }
    })
    .then(r => r.json())
    .then(data => {
      alert(data.message || "Agregado a playlist ✅");
      playlistModal.hidden = true;
    });
  }

  function checkLikeStatus(songId) {
    fetch(`/canciones/${songId}/liked`, { headers: { "Accept": "application/json" } })
    .then(r => r.json())
    .then(data => {
      likeBtn.innerHTML = data.liked
        ? '<i class="fa-solid fa-heart" style="color:#aa029c"></i>'
        : '<i class="fa-regular fa-heart"></i>';
    });
  }

  // 🎵 Enganchar canciones dinámicas
  function bindSongEvents(){
    document.querySelectorAll('.cancion-item').forEach(btn=>{
      if(!btn.dataset.bound){
        btn.addEventListener('click',()=>{
          audio.src=btn.dataset.src;
          titleEl.textContent=btn.dataset.title;
          artistEl.textContent=btn.dataset.artist;
          coverEl.src = btn.dataset.cover || "{{ asset('img/default-cancion.png') }}";

          currentSongId = btn.dataset.id;
          checkLikeStatus(currentSongId);

          audio.play();
          playBtn.innerHTML='<i class="fa-solid fa-pause"></i>';

          // ✅ guardar estado al instante
          savePlayerState();
        });
        btn.dataset.bound="true";
      }
    });
  }
  bindSongEvents();
  const obs=new MutationObserver(()=>bindSongEvents());
  obs.observe(document.body,{childList:true,subtree:true});
});
</script>
