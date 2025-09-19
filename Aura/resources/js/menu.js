export default function initMenu() {
console.log("Inicializando scripts de menu...");
/**
 * MENU.JS — Home PRO MAX
 * - Carruseles .x-carousel (drag + auto)
 * - Songs: paginación (5 visibles), player/cola/like/playlist
 * - Toast + modal playlists
 */

  setupCarousels();
  setupSongsPager(5); // 5 visibles
  setupSongRows();
  setupPlaylistModal();

/* ============== Carruseles genéricos (.x-carousel) ============== */
function setupCarousels(){
  document.querySelectorAll(".x-carousel").forEach(wrap=>{
    const vp = wrap.querySelector(".x-viewport");
    const track = wrap.querySelector(".x-track");
    const prev = wrap.querySelector(".x-btn.prev");
    const next = wrap.querySelector(".x-btn.next");
    if(!vp || !track) return;

    const gap = parseInt(getComputedStyle(track).gap) || 18;
    let idx = 0;
    let cardW = track.querySelector(".x-card")?.getBoundingClientRect().width || 320;

    function goTo(i, instant=false){
      const max = Math.max(0, track.children.length - 1);
      idx = Math.max(0, Math.min(i, max));
      const tx = idx * (cardW + gap);
      track.style.transitionDuration = instant ? "0ms" : "600ms";
      track.style.transform = `translateX(-${tx}px)`;
    }
    function onResize(){
      cardW = track.querySelector(".x-card")?.getBoundingClientRect().width || 320;
      goTo(idx, true);
    }
    window.addEventListener("resize", onResize);

    let t=null;
    function startAuto(){ stopAuto(); t=setInterval(()=>goTo(idx+1), 5000); }
    function stopAuto(){ if(t){ clearInterval(t); t=null; } }

    next?.addEventListener("click", ()=>{ stopAuto(); goTo(idx+1); startAuto(); });
    prev?.addEventListener("click", ()=>{ stopAuto(); goTo(idx-1); startAuto(); });

    vp.addEventListener("mouseenter", stopAuto);
    vp.addEventListener("mouseleave", startAuto);

    // Drag
    let down=false, sx=0, startTx=0, currTx=0;
    const curTx = () => {
      const m = /translateX\(-?([\d.]+)px\)/.exec(track.style.transform||"");
      return m? parseFloat(m[1]) : idx*(cardW+gap);
    };
    const clamp = (n,min,max)=>Math.max(min,Math.min(max,n));
    const onDown = x => { down=true; sx=x; startTx=curTx(); track.style.transitionDuration="0ms"; stopAuto(); };
    const onMove = x => {
      if(!down) return;
      const dx = x - sx;
      currTx = clamp(startTx - dx, 0, (track.children.length-1)*(cardW+gap));
      track.style.transform = `translateX(-${currTx}px)`;
    };
    const onUp = ()=>{ if(!down) return; down=false; const newIndex = Math.round(currTx/(cardW+gap)); goTo(newIndex); startAuto(); };

    vp.addEventListener("mousedown", e=>onDown(e.clientX));
    window.addEventListener("mousemove", e=>onMove(e.clientX));
    window.addEventListener("mouseup", onUp);
    vp.addEventListener("touchstart", e=>onDown(e.touches[0].clientX), {passive:true});
    window.addEventListener("touchmove", e=>onMove(e.touches[0].clientX), {passive:true});
    window.addEventListener("touchend", onUp);

    goTo(0, true); startAuto();
  });
}

/* ============== Songs: paginación (5 visibles) ============== */
function setupSongsPager(pageSize){
  const list = document.getElementById("latestSongs");
  if (!list) return;
  const rows = Array.from(list.querySelectorAll(".song-row"));
  let page = 0;
  const total = Math.max(1, Math.ceil(rows.length / pageSize));
  const ind = document.querySelector(".page-indicator b");
  const tot = document.querySelector(".page-total");
  const prev = document.querySelector(".pager .prev");
  const next = document.querySelector(".pager .next");
  if (tot) tot.textContent = total;

  function render(){
    rows.forEach((r,i)=> {
      const show = Math.floor(i / pageSize) === page;
      r.style.display = show ? "grid" : "none";
    });
    if (ind) ind.textContent = (page+1);
    if (prev) prev.disabled = (page === 0);
    if (next) next.disabled = (page >= total-1);
  }
  prev?.addEventListener("click", ()=>{ page = Math.max(0, page-1); render(); });
  next?.addEventListener("click", ()=>{ page = Math.min(total-1, page+1); render(); });
  render();
}

/* ============== Canciones: play / cola / like / playlist ============== */
function setupSongRows(){
  const list = document.getElementById("latestSongs");
  if (!list) return;
 list.querySelectorAll(".song-row").forEach(row=>{
    const liked = row.dataset.liked === "1";
    const btn = row.querySelector(".like-btn");
    if (btn && liked) {
      btn.classList.add("is-liked");
      const ico = btn.querySelector("i");
      if (ico) {
        ico.classList.remove("fa-regular");
        ico.classList.add("fa-solid");
      }
    }
  });
  // Menú kebab
  list.addEventListener("click", (e)=>{
    const more = e.target.closest(".more-btn");
    if (!more) return;
    const menu = more.parentElement.querySelector(".kebab-menu");
    document.querySelectorAll(".kebab-menu.show").forEach(m => { if(m!==menu) m.classList.remove("show"); });
    menu.classList.toggle("show");
    e.stopPropagation();
  });
  document.addEventListener("click", ()=>document.querySelectorAll(".kebab-menu.show").forEach(m=>m.classList.remove("show")));

  // Fila
  list.querySelectorAll(".song-row").forEach(row=>{
    row.querySelectorAll("[data-play], .song-cover-link").forEach(t => t.addEventListener("click", ()=>playRow(row)));
    row.addEventListener("keydown", (ev)=>{ if(ev.key==='Enter') playRow(row); });

    row.querySelectorAll(".kebab-menu .menu-item, .icon-chip").forEach(btn=>{
      btn.addEventListener("click", (ev)=>{
        const menu = btn.closest(".kebab-menu");
        if(menu) menu.classList.remove("show");

        const act =
          btn.dataset.action ||
          (btn.classList.contains("like-btn") ? "like" :
           btn.classList.contains("add-queue-btn") ? "queue" :
           btn.classList.contains("add-playlist-btn") ? "playlist" : null);

        if (!act) return;
        if (act === "play") { playRow(row); }
        if (act === "queue") { addToQueue(row); }
        if (act === "like")  { toggleLike(btn, row); }
        if (act === "playlist") { openPlaylistFor(row); }
      });
    });
  });
}

function songFromRow(row){
  return {
    id: row.dataset.id,
    src: row.dataset.src,
    title: row.dataset.title,
    artist: row.dataset.artist,
    cover: row.dataset.cover,
    duration: row.dataset.duration || "--:--"
  };
}

function playRow(row){
  const s = songFromRow(row);
  if (!s.src){ toast("Esta canción no tiene archivo de audio."); return; }
  if (window.AuraQueue?.externalPlay) {
    window.AuraQueue.externalPlay(s);
  } else if (window.AuraPlayer?.play) {
    window.AuraPlayer.play(s);
  }
  toast(`Reproduciendo: ${s.title}`);
}

function addToQueue(row){
  const s = songFromRow(row);
  if (window.AuraQueue?.addToEnd) {
    window.AuraQueue.addToEnd([s]);
    toast("Añadida a la cola");
  } else {
    toast("La cola no está disponible");
  }
}

async function toggleLike(btn, row){
  const url = btn.dataset.like;
  if(!url){ toast("Acción de 'Me gusta' no disponible."); return; }
  try{
    const res = await fetch(url, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        "X-Requested-With": "XMLHttpRequest"
      },
      credentials: "same-origin"
    });
    if(res.status === 401 || res.redirected){ toast("Inicia sesión para usar Me gusta"); return; }
    if(!res.ok) throw new Error("Error " + res.status);
    btn.classList.toggle("is-liked");
    const ico = btn.querySelector("i");
    ico.classList.toggle("fa-regular");
    ico.classList.toggle("fa-solid");
    toast(btn.classList.contains("is-liked") ? "Añadida a Me gusta" : "Quitada de Me gusta");
  }catch(e){
    toast("No se pudo actualizar Me gusta");
  }
}

/* ============== Playlists (modal + API) ============== */
let PL_OPTS = null;
function setupPlaylistModal(){
  const el = document.getElementById("playlist-endpoints");
  if(!el) return;
  PL_OPTS = {
    mine: el.dataset.mine || "",
    quick: el.dataset.quick || "",
    pattern: el.dataset.addPattern || ""
  };

  const modal = document.getElementById("plModal");
  const close = modal.querySelector(".pl-close");
  close.addEventListener("click", ()=>hidePL());
  modal.addEventListener("click", (e)=>{ if(e.target === modal) hidePL(); });
}

function openPlaylistFor(row){
  if (!PL_OPTS?.mine){ toast("Playlists no disponibles"); return; }
  const songId = row.dataset.id;
  showPL();
  loadMyPlaylists(PL_OPTS.mine, songId, PL_OPTS.pattern);
}

function showPL(){ document.getElementById("plModal").classList.add("show"); }
function hidePL(){ document.getElementById("plModal").classList.remove("show"); }

async function loadMyPlaylists(mineUrl, songId, pattern){
  const list = document.getElementById("plList");
  list.innerHTML = `<div class="pl-item" style="opacity:.7">Cargando tus playlists...</div>`;
  try{
    const r = await fetch(mineUrl, { headers:{ "X-Requested-With":"XMLHttpRequest" }, credentials:"same-origin" });
    if (r.status === 401 || r.redirected){ hidePL(); toast("Inicia sesión para usar playlists"); return; }
    const data = await r.json();
    const items = Array.isArray(data) ? data : (data.data || data.playlists || []);
    if(!items || !items.length){
      list.innerHTML = `<div class="pl-item" style="opacity:.8">No tienes playlists todavía.</div>`;
    }else{
      list.innerHTML = "";
      items.forEach(pl=>{
        const row = document.createElement("div");
        row.className = "pl-item";
        row.innerHTML = `
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-list-music"></i>
            <strong>${escapeHtml(pl.name || pl.titulo || "Playlist")}</strong>
          </div>
          <button type="button"><i class="fa-solid fa-plus"></i> Añadir</button>
        `;
        row.querySelector("button").addEventListener("click", ()=> addSongToPlaylist(pattern, pl.id || pl.playlist_id, songId));
        list.appendChild(row);
      });
    }
  }catch(e){
    list.innerHTML = `<div class="pl-item">No se pudieron cargar tus playlists.</div>`;
  }

  const form = document.getElementById("plQuick");
  form.onsubmit = async (ev)=>{
    ev.preventDefault();
    const name = form.title.value.trim();
    if(!name) return;
    try{
      const res = await fetch(PL_OPTS.quick, {
        method:"POST",
        headers:{
          "Content-Type":"application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          "X-Requested-With":"XMLHttpRequest"
        },
        credentials: "same-origin",
        body: JSON.stringify({ name })
      });
      if(res.status === 401 || res.redirected){ hidePL(); toast("Inicia sesión para crear playlists"); return; }
      if(!res.ok){ toast("No se pudo crear la playlist"); return; }
      const pl = await res.json();
      toast("Playlist creada");
      await addSongToPlaylist(PL_OPTS.pattern, (pl.id || pl.playlist_id), songId);
      hidePL();
    }catch(_){
      toast("No se pudo crear la playlist");
    }
  };
}

async function addSongToPlaylist(pattern, playlistId, songId){
  if(!playlistId){ toast("Playlist inválida"); return; }
  const url = pattern.replace("__PID__", playlistId).replace("__SID__", songId);
  try{
    const r = await fetch(url, {
      method:"POST",
      headers:{
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        "X-Requested-With":"XMLHttpRequest"
      },
      credentials: "same-origin"
    });
    if(r.status === 401 || r.redirected){ hidePL(); toast("Inicia sesión para usar playlists"); return; }
    if(!r.ok) throw new Error("status "+r.status);
    toast("Canción agregada a la playlist");
  }catch(e){
    toast("No se pudo agregar a la playlist");
  }
}

/* ============== Utilidades ============== */
let toastTimer=null;
function toast(msg){
  const el = document.getElementById("toast");
  if(!el) return;
  el.textContent = msg;
  el.classList.add("show");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(()=> el.classList.remove("show"), 2200);
}
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m=>({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m])); }
}