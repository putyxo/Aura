<!-- includes/footer.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{
  --pl-h: 88px;

  --pl-fg:#f5f6fa;
  --pl-fg-dim:#b7b8c8;
  --pl-line:#26263a;
  --pl-accent:#aa029c;
  --pl-accent2:#6b3df6;
  --pl-radius:14px;
  --pl-dur:.22s;
  --pl-ease:cubic-bezier(.2,.8,.2,1);
  color-scheme: dark;

  --sidebar-width: 268px;
  --sidebar-width-mini: 76px;
  --sidebar-gap: 20px;
  --color-accent:#aa029c;
  --color-bg-1: #000000;
  --color-bg-2: #090212;
  --sidebar-accent:#a855f7;
  --sidebar-line: rgba(168,85,247,.14);
  --color-line:#26263a;
  --color-dim:#b6b6c8;
  --color-text:#f6f7fb;
  --anim-dur:.25s; 
  --anim-ease:cubic-bezier(.2,.8,.2,1);
  --shadow-lg: 0 24px 60px rgba(0,0,0,.40);
  --sidebar-collapsed: 76px;     /* ancho del sidebar colapsado */
  --sidebar-expanded: 268px;     /* ancho del sidebar expandido */
  --color-bg:#0b0b10; 
  --color-panel:#0f0f17; 
  --color-panel2:#141428; 
  --color-a1:#a855f7; 
  --color-a2:#ec4899; 
  --color-a3:#00ffa8;
  --shadow-md: 0 10px 30px rgba(0,0,0,.30);
}

/* Asegura espacio para el player fijo */
body:not(.has-player){ padding-bottom: var(--pl-h); }

.fusion-player{
  position:fixed; inset:auto 0 0 0; height:var(--pl-h);
  display:grid; grid-template-columns: 1fr minmax(360px, 1.2fr) 1fr;
  align-items:center; gap:16px;
  padding:10px 18px;
  background: linear-gradient(90deg, var(--color-bg-2), var(--color-bg-1));
  border-top:1px solid var(--pl-line);
  box-shadow: 0 -18px 40px rgba(0,0,0,.45);
  backdrop-filter: blur(8px) saturate(1.1);
  -webkit-backdrop-filter: blur(8px) saturate(1.1);
  z-index: 999;
  font-family: ui-sans-serif, system-ui, Segoe UI, Roboto, Inter, Arial, sans-serif;
  color:var(--pl-fg);
}

/* ============== Columna 1: Track info ============== */
.track-info{
  display:flex; align-items:center; gap:14px; min-width:0;
}
.track-cover{
  width:58px;height:58px;border-radius:12px;object-fit:cover; flex:0 0 auto;
  box-shadow: 0 8px 20px rgba(0,0,0,.35);
}
.track-text{ min-width:0; }
.track-title{
  margin:0; font-size:14px; font-weight:600; letter-spacing:.2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.track-artist{
  margin:2px 0 0; font-size:12px;color:var(--pl-fg-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.track-actions{ display:flex; gap:10px; margin-left:6px; }
.icon-btn{
  display:inline-grid; place-items:center;
  width:32px; height:32px; border-radius:10px;
  border:1px solid transparent; background:transparent; color:var(--pl-fg-dim);
  cursor:pointer; transition: all var(--pl-dur) var(--pl-ease);
}
.icon-btn:hover{ color:var(--pl-fg); border-color:var(--pl-line); transform: translateY(-1px); }
.icon-btn.active{ color:#ff4b91; border-color:color-mix(in oklab, #ff4b91 40%, transparent); }

/* ============== Columna 2: Controles ============== */
.controls{
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px;
}
.main-buttons{ display:flex; align-items:center; gap:18px; }
.ctl{
  background:transparent; border:none; color:var(--pl-fg); cursor:pointer;
  width:36px;height:36px;border-radius:50%;
  display:grid;place-items:center; transition: transform var(--pl-dur) var(--pl-ease), background var(--pl-dur) var(--pl-ease), color var(--pl-dur) var(--pl-ease);
}
.ctl:hover{ transform:translateY(-1px); background:rgba(255,255,255,.06); }
.ctl[aria-pressed="true"]{ color:var(--pl-accent); }
.play-btn{
  width:44px;height:44px;border-radius:50%;
  background: linear-gradient(135deg, var(--pl-fg) 0%, #e9e9ee 100%);
  color:#121212; box-shadow: 0 10px 28px rgba(0,0,0,.35), inset 0 -2px 4px rgba(0,0,0,.14);
}
.play-btn:hover{ transform: scale(1.04); }

.progress{
  display:flex; align-items:center; gap:10px; width:min(560px, 92%); }
.time{ font-size:12px; color:var(--pl-fg-dim); font-variant-numeric: tabular-nums; min-width:42px; text-align:center; }

/* Barra de progreso (custom range) */
.seek-wrap{ position:relative; flex:1; }
.seek{
  -webkit-appearance:none; appearance:none; width:100%; height:6px; border-radius:999px;
  background:linear-gradient(90deg, var(--pl-accent) 0 0) no-repeat, #2a2a3d;
  outline:none; cursor:pointer;
}
.seek::-webkit-slider-thumb{
  -webkit-appearance:none; appearance:none; width:14px;height:14px;border-radius:50%;
  background:var(--pl-fg); box-shadow: 0 2px 6px rgba(0,0,0,.35); border:1px solid rgba(0,0,0,.25); margin-top:-4px;
  transition: transform var(--pl-dur) var(--pl-ease);
}
.seek::-moz-range-thumb{
  width:14px;height:14px;border-radius:50%; background:var(--pl-fg); border:none;
}
.seek:active::-webkit-slider-thumb{ transform: scale(1.1); }
.buffered{
  position:absolute; left:0; top:50%; height:6px; transform:translateY(-50%);
  border-radius:999px; background:#3a3a55; width:0; pointer-events:none;
}

/* ============== Columna 3: Opciones ============== */
.options{ display:flex; align-items:center; gap:14px; justify-content:flex-end; }
.volume{ display:flex; align-items:center; gap:8px; }
.vol-range{
  -webkit-appearance:none; appearance:none; width:100px; height:6px; border-radius:999px;
  background:linear-gradient(90deg, var(--pl-accent2) 0 0) no-repeat, #2a2a3d;
  outline:none; cursor:pointer;
}
.vol-range::-webkit-slider-thumb{
  -webkit-appearance:none; width:12px;height:12px;border-radius:50%; background:var(--pl-fg);
  border:1px solid rgba(0,0,0,.25); box-shadow: 0 2px 6px rgba(0,0,0,.3); margin-top:-3px;
}

/* Estados focus accesibles */
.icon-btn:focus-visible, .ctl:focus-visible, .play-btn:focus-visible, .seek:focus-visible, .vol-range:focus-visible{
  outline:2px solid color-mix(in oklab, var(--pl-accent) 70%, #fff 0%); outline-offset:2px; border-radius:10px;
}

/* Responsivo */
@media (max-width: 920px){
  .fusion-player{ grid-template-columns: 1fr 1fr; height:auto; padding:12px 14px; row-gap:10px; }
  .options{ grid-column:1 / -1; justify-content:space-between; }
  body:not(.has-player){ padding-bottom: calc(var(--pl-h) + 28px); }
}
@media (max-width: 540px){
  .track-artist{ display:none; }
  .progress{ width:100%; }
  .vol-range{ width:84px; }
}

/* Animaciones reducidas si el usuario prefiere */
@media (prefers-reduced-motion: reduce){
  .ctl:hover, .icon-btn:hover, .play-btn:hover{ transform:none; }
  .fusion-player, .ctl, .icon-btn{ transition:none; }
}

</style>

<footer class="fusion-player" role="contentinfo" aria-label="Reproductor de audio">
  <!-- Track Info -->
  <div class="track-info">
    <img src="img/album.jpg" alt="Portada del álbum" class="track-cover" />
    <div class="track-text">
      <h4 class="track-title">Selecciona una canción</h4>
      <p class="track-artist">Artista</p>
    </div>
    <div class="track-actions">
      <button class="icon-btn like-btn" title="Me gusta" aria-pressed="false"><i class="fa-regular fa-heart"></i></button>
      <button class="icon-btn save-btn" title="Guardar en tu biblioteca" aria-pressed="false"><i class="fa-regular fa-bookmark"></i></button>
    </div>
  </div>

  <!-- Controls -->
  <div class="controls">
    <div class="main-buttons" role="group" aria-label="Controles de reproducción">
      <button class="ctl shuffle" aria-label="Aleatorio" aria-pressed="false"><i class="fa-solid fa-shuffle"></i></button>
      <button class="ctl prev" aria-label="Anterior"><i class="fa-solid fa-backward-step"></i></button>
      <button class="ctl play-btn play" aria-label="Reproducir / Pausar"><i class="fa-solid fa-play"></i></button>
      <button class="ctl next" aria-label="Siguiente"><i class="fa-solid fa-forward-step"></i></button>
      <button class="ctl repeat" aria-label="Repetir" aria-pressed="false"><i class="fa-solid fa-repeat"></i></button>
    </div>

    <div class="progress" aria-label="Progreso de la pista">
      <span class="time current-time">0:00</span>
      <div class="seek-wrap">
        <div class="buffered" aria-hidden="true"></div>
        <input type="range" class="seek" min="0" max="100" value="0" step="1" aria-label="Línea de tiempo">
      </div>
      <span class="time total-time">--:--</span>
    </div>
  </div>

  <!-- Options -->
  <div class="options">
    <button class="icon-btn lyrics" title="Letras"><i class="fa-regular fa-comment-dots"></i></button>
    <button class="icon-btn queue" title="Cola"><i class="fa-solid fa-bars"></i></button>
    <button class="icon-btn devices" title="Dispositivos"><i class="fa-solid fa-laptop"></i></button>

    <div class="volume" aria-label="Volumen">
      <button class="icon-btn vol-toggle" title="Silenciar" aria-pressed="false"><i class="fa-solid fa-volume-low"></i></button>
      <input class="vol-range" type="range" min="0" max="100" value="70" step="1" aria-label="Control de volumen">
    </div>

    <button class="icon-btn expand" title="Expandir"><i class="fa-solid fa-up-right-and-down-left-from-center"></i></button>
  </div>
</footer>

<script>
  // Asegura padding para el player fijo
  document.body.classList.add('has-player');

  // Elementos
  const player = document.querySelector('.fusion-player');
  const audio = new Audio();
  audio.preload = 'metadata';

  const titleEl = player.querySelector('.track-title');
  const artistEl = player.querySelector('.track-artist');
  const coverEl = player.querySelector('.track-cover');
  const playBtn = player.querySelector('.play-btn');
  const nextBtn = player.querySelector('.next');
  const prevBtn = player.querySelector('.prev');
  const shuffleBtn = player.querySelector('.shuffle');
  const repeatBtn = player.querySelector('.repeat');
  const likeBtn = player.querySelector('.like-btn');
  const saveBtn = player.querySelector('.save-btn');
  const seek = player.querySelector('.seek');
  const bufferedBar = player.querySelector('.buffered');
  const currentTimeEl = player.querySelector('.current-time');
  const totalTimeEl = player.querySelector('.total-time');
  const volRange = player.querySelector('.vol-range');
  const volToggle = player.querySelector('.vol-toggle');

  // Lista de canciones (usa .cancion-item con data-*)
  let songList = Array.from(document.querySelectorAll('.cancion-item'));
  let current = 0;
  let isShuffle = false;
  let repeatMode = 0; // 0: off, 1: all, 2: one
  let lastVolume = 0.7;

  // Helpers
  const fmt = s => {
    if (!Number.isFinite(s)) return '--:--';
    const m = Math.floor(s / 60);
    const ss = Math.floor(s % 60).toString().padStart(2,'0');
    return `${m}:${ss}`;
  };

  const applyProgressFill = () => {
    const val = (seek.value - seek.min) / (seek.max - seek.min) * 100;
    seek.style.backgroundSize = `${val}% 100%`;
  };
  const applyVolumeFill = () => {
    const val = (volRange.value - volRange.min) / (volRange.max - volRange.min) * 100;
    volRange.style.backgroundSize = `${val}% 100%`;
  };

  function loadSong(index){
    if (!songList.length) return;
    current = (index + songList.length) % songList.length;

    const el = songList[current];
    const src = el?.dataset?.src || '';
    const title = el?.dataset?.title || 'Sin título';
    const artist = el?.dataset?.artist || 'Desconocido';
    const cover = el?.dataset?.cover || 'img/album.jpg';

    audio.src = src;
    titleEl.textContent = title;
    artistEl.textContent = artist;
    coverEl.src = cover;

    // Reproducción automática al cargar
    audio.play().then(() => {
      playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
    }).catch(()=>{ /* autoplay bloqueado: deja el botón en Play */ });
  }

  // Inicializa volumen
  audio.volume = Number(volRange.value) / 100;
  applyVolumeFill();

  // Eventos de la lista
  songList.forEach((item, i) => {
    item.addEventListener('click', () => {
      loadSong(i);
    });
  });

  // Controles
  playBtn.addEventListener('click', () => {
    if (audio.paused) {
      audio.play();
      playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
    } else {
      audio.pause();
      playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
    }
  });

  nextBtn.addEventListener('click', () => {
    if (!songList.length) return;
    if (isShuffle && repeatMode !== 2) {
      let n; do { n = Math.floor(Math.random() * songList.length); } while (songList.length > 1 && n === current);
      loadSong(n);
    } else if (repeatMode === 2) {
      loadSong(current); // repetir uno
    } else {
      loadSong(current + 1);
    }
  });

  prevBtn.addEventListener('click', () => {
    if (!songList.length) return;
    if (audio.currentTime > 3) {
      audio.currentTime = 0;
    } else {
      loadSong(current - 1);
    }
  });

  shuffleBtn.addEventListener('click', () => {
    isShuffle = !isShuffle;
    shuffleBtn.setAttribute('aria-pressed', String(isShuffle));
  });

  repeatBtn.addEventListener('click', () => {
    repeatMode = (repeatMode + 1) % 3;
    // 0 off, 1 all, 2 one
    repeatBtn.setAttribute('aria-pressed', repeatMode ? 'true' : 'false');
    repeatBtn.title = ['Repetir: desactivado','Repetir: lista','Repetir: pista'][repeatMode];
    repeatBtn.querySelector('i').className = repeatMode === 2
      ? 'fa-solid fa-repeat-1'
      : 'fa-solid fa-repeat';
  });

  likeBtn.addEventListener('click', () => {
    const now = likeBtn.getAttribute('aria-pressed') === 'true';
    likeBtn.setAttribute('aria-pressed', String(!now));
    likeBtn.classList.toggle('active', !now);
    likeBtn.innerHTML = now ? '<i class="fa-regular fa-heart"></i>' : '<i class="fa-solid fa-heart"></i>';
  });

  saveBtn.addEventListener('click', () => {
    const now = saveBtn.getAttribute('aria-pressed') === 'true';
    saveBtn.setAttribute('aria-pressed', String(!now));
    saveBtn.classList.toggle('active', !now);
  });

  // Progreso
  audio.addEventListener('loadedmetadata', () => {
    seek.max = Math.floor(audio.duration || 0);
    totalTimeEl.textContent = fmt(audio.duration);
    // buffer
    updateBuffered();
  });

  audio.addEventListener('timeupdate', () => {
    seek.value = Math.floor(audio.currentTime || 0);
    currentTimeEl.textContent = fmt(audio.currentTime);
    applyProgressFill();
  });

  const updateBuffered = () => {
    try{
      if (audio.buffered.length){
        const end = audio.buffered.end(audio.buffered.length - 1);
        const ratio = Math.min(1, (end || 0) / (audio.duration || 1));
        bufferedBar.style.width = `${ratio * 100}%`;
      }
    }catch(_e){}
  };
  audio.addEventListener('progress', updateBuffered);

  seek.addEventListener('input', () => {
    audio.currentTime = Number(seek.value) || 0;
    applyProgressFill();
  });

  audio.addEventListener('ended', () => {
    if (repeatMode === 2) {
      loadSong(current); // repetir pista
    } else if (isShuffle) {
      nextBtn.click();
    } else if (repeatMode === 1) {
      loadSong(current + 1);
    } else {
      // fin sin repetir
      playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
    }
  });

  // Volumen + mute
  volRange.addEventListener('input', () => {
    const v = Number(volRange.value)/100;
    audio.volume = v;
    volToggle.setAttribute('aria-pressed', String(v === 0));
    volToggle.innerHTML = v === 0 ? '<i class="fa-solid fa-volume-xmark"></i>'
      : (v < .5 ? '<i class="fa-solid fa-volume-low"></i>' : '<i class="fa-solid fa-volume-high"></i>');
    applyVolumeFill();
  });

  volToggle.addEventListener('click', () => {
    if (audio.volume > 0){
      lastVolume = audio.volume;
      audio.volume = 0; volRange.value = 0;
    } else {
      audio.volume = lastVolume || 0.7; volRange.value = Math.round(audio.volume*100);
    }
    volRange.dispatchEvent(new Event('input'));
  });

  // Atajos de teclado (si el foco está en la página)
  window.addEventListener('keydown', (e) => {
    const tag = document.activeElement?.tagName?.toLowerCase();
    if (tag === 'input' || tag === 'textarea') return;

    if (e.code === 'Space'){ e.preventDefault(); playBtn.click(); }
    if (e.code === 'ArrowRight'){ audio.currentTime = Math.min(audio.currentTime + 5, audio.duration || audio.currentTime); }
    if (e.code === 'ArrowLeft'){ audio.currentTime = Math.max(audio.currentTime - 5, 0); }
    if (e.code === 'ArrowUp'){ e.preventDefault(); volRange.value = Math.min(100, Number(volRange.value)+5); volRange.dispatchEvent(new Event('input')); }
    if (e.code === 'ArrowDown'){ e.preventDefault(); volRange.value = Math.max(0, Number(volRange.value)-5); volRange.dispatchEvent(new Event('input')); }
    if (e.key.toLowerCase() === 'm'){ volToggle.click(); }
  });

  // Auto-cargar primera pista si existe (sin autoplay forzado)
  if (songList.length){
    const first = songList[0];
    titleEl.textContent = first.dataset.title || 'Pista';
    artistEl.textContent = first.dataset.artist || 'Artista';
    coverEl.src = first.dataset.cover || 'img/album.jpg';
    audio.src = first.dataset.src || '';
  }

  // Inicializa estilos fill
  applyProgressFill();

  // Observa si cambian dinámicamente las canciones (opcional)
  const obs = new MutationObserver(() => {
    songList = Array.from(document.querySelectorAll('.cancion-item'));
  });
  obs.observe(document.body, { childList:true, subtree:true });
</script>
