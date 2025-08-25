<!-- ===== BARRA LATERAL ===== -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<aside class="sidebar" id="sidebar">
  <a href="{{ url('/menu') }}" class="brand" aria-label="AURA Inicio">
    <div class="logo-clip">
      <img src="../img/Aura_LOGO.png" alt="Logo de AURA" class="logo-img">
    </div>
  </a>
  <nav class="menu">
    <a href="{{ url('/menu') }}" class="item active">
      <i class="fas fa-house"></i><span class="label">Inicio</span>
    </a>

    <a href="{{ url('/like') }}" class="item">
      <i class="fas fa-heart"></i><span class="label">Favoritos</span>
    </a>

    <a href="{{ url('/playlists') }}" class="item">
      <i class="fa-solid fa-notes-medical"></i><span class="label">Playlist</span>
    </a>

    <a href="{{ url('/follow_artist') }}" class="item">
      <i class="fa-solid fa-user-check"></i><span class="label">Tus Seguidos</span>
    </a>

    <a href="{{ url('/menu_album') }}" class="item">
      <i class="fa-solid fa-book-bookmark"></i><span class="label">Tus Albumes</span>
    </a>

    <a href="{{ url('/recientes') }}" class="item">
      <i class="fa-solid fa-list"></i><span class="label">Recientes</span>
    </a>

    <div class="menu-sep"></div>
  </nav>
</aside>


<style>

:root {
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

/* ================= BASE ================= */
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{
  font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
  color:var(--color-text);
  background:
    radial-gradient(1200px 600px at 80% -10%, rgba(168,85,247,.15), transparent 60%),
    radial-gradient(1000px 700px at 10% 10%, rgba(236,72,153,.13), transparent 60%),
    radial-gradient(900px 400px at 60% 110%, rgba(0,255,168,.10), transparent 55%),
    var(--color-bg);
  overflow:hidden;
}
.app{display:flex;flex-direction:column;height:100vh}

/* ================= MAIN empujado por sidebar ================= */
.main-content{
  margin-left: calc(var(--sidebar-collapsed) + var(--sidebar-gap));
  height:100vh;
  overflow-y:auto;
  padding:18px 24px 96px 24px;
  transition: margin-left var(--anim-dur) var(--anim-ease);
}
.sidebar:hover ~ .main-content{
  margin-left: calc(var(--sidebar-expanded) + var(--sidebar-gap));
}
/* scrollbar suave */
.main-content::-webkit-scrollbar{ width:10px }
.main-content::-webkit-scrollbar-thumb{ background:#23233a; border-radius:10px }
.main-content::-webkit-scrollbar-thumb:hover{ background:#2d2d4a }

/* ===== Sidebar ===== */
.sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  width: var(--sidebar-width-mini);
  display: flex;
  flex-direction: column;
  background:
    radial-gradient(1200px 120px at 20% 120%, color-mix(in oklab, var(--color-accent) 22%, transparent) 0%, transparent 60%),
    linear-gradient(90deg, var(--color-bg-2), var(--color-bg-1));
  border: 1px solid var(--sidebar-line);
  border-left: none;
  border-radius: 0 18px 18px 0;
  box-shadow: 0 24px 60px rgba(0,0,0,.40);
  backdrop-filter: blur(14px) saturate(1.05);
  -webkit-backdrop-filter: blur(14px) saturate(1.05);
  overflow: hidden;
  z-index: 60;
  transition: width var(--anim-dur) var(--anim-ease), box-shadow var(--anim-dur) var(--anim-ease);
}
.sidebar::after {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  border-radius: 0 18px 18px 0;
  background: radial-gradient(
    1000px 300px at 50% 110%,
    color-mix(in oklab, var(--sidebar-accent) 10%, transparent) 0%,
    transparent 60%
  );
  opacity: .65;
}
.sidebar:hover{ width:var(--sidebar-width); box-shadow:var(--shadow-lg) }
.sidebar:hover ~ .main-content { margin-left: calc(var(--sidebar-expanded) + var(--sidebar-gap)); }

/* Logo */
.brand{ display:flex; align-items:center; padding:12px }
.logo-clip{
  height:38px; width:46px; border-radius:10px; overflow:hidden;
  transition: width var(--anim-dur) var(--anim-ease);
}
.sidebar:hover .logo-clip{ width:140px }
.logo-img{ height:100%; width:auto; object-fit:contain; object-position:left center; display:block }

/* Menú */
.menu{ display:flex; flex-direction:column; gap:6px; padding:4px 8px 2000px }
.menu-sep{ height:10px }
.item{
  display:flex; align-items:center; gap:12px;
  padding:10px 12px; border-radius:12px; width:100%;
  color:var(--color-dim); text-decoration:none; border:1px solid transparent;
  transition: background var(--anim-dur), color var(--anim-dur), border-color var(--anim-dur), transform .08s ease-out;
}
.item i{ min-width:22px; text-align:center; font-size:18px; opacity:.95 }
.item .label{
  opacity:0; transform:translateX(-6px); white-space:nowrap;
  transition: opacity var(--anim-dur) var(--anim-ease), transform var(--anim-dur) var(--anim-ease);
}
.sidebar:hover .item .label{ opacity:1; transform:none }
.item:hover{ background:rgba(255,255,255,.08); color:#fff; border-color:var(--color-line) }
.item.active{
  color:#fff;
  background:linear-gradient(135deg,#1a1a2b,#151527);
  border-color:var(--color-line);
  box-shadow:0 6px 14px rgba(0,0,0,.35);
}
</style>


