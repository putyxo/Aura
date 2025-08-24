

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



<header class="header">
  <!-- Buscador -->
  <div class="header-search-group">
    <span class="search-icon"></span>
    <input class="search" type="text" placeholder="Buscar..." />
    <button class="filter-btn" type="button" title="Filtros">
      <i class="fa-solid fa-sliders"></i>
      <span class="btn-label">Filtros</span>
    </button>
  </div>

  <!-- Acciones rápidas (SOLO íconos) -->
  <div class="quick-actions" role="toolbar" aria-label="Acciones rápidas">
    @auth
      @if(auth()->user()->es_artista)
        <a class="qa-btn" href="{{ route('musica.subir') }}" title="Subir música">
          <i class="fas fa-upload"></i>
        </a>
      @endif
    @endauth
    <button class="qa-btn" type="button" title="Traductor"><i class="fa-solid fa-language"></i></button>
    <button class="qa-btn" type="button" title="Ajustes"><i class="fa-solid fa-gear"></i></button>
    <button class="qa-btn" type="button" title="Notificaciones"><i class="fa-regular fa-bell"></i></button>
  </div>
  <!-- Usuario (MOVER fuera de .topbar/.quick-actions) -->
<div class="user-menu">
  <button class="user-chip" id="userMenuBtn" type="button" title="Cuenta" aria-expanded="false">
    <img class="chip-avatar"
         src=""
    
         alt="Avatar">
    <span class="chip-name">
      {{ auth()->user()->es_artista ? auth()->user()->nombre_artistico : auth()->user()->nombre }}
    </span>
    <i class="fa-solid fa-chevron-down"></i>
  </button>

  <div class="dropdown-menu" id="userDropdown" aria-hidden="true">
    <!-- Fila: avatar + datos + botón Editar a la par de la foto -->
    <div class="profile-grid">
      <div class="avatar-wrap">
        <img class="profile-avatar"
             src=""
             onerror="'"
             alt="Avatar">

        <!-- Botón EDITAR a la par de la foto -->
        <a href="{{ route('ed_perfil') }}" class="edit-chip" title="Editar foto y nombre">
          <i class="fa-solid fa-pen"></i><span>Editar</span>
        </a>
      </div>

      <div class="id-block">
        <div class="profile-name">
          {{ auth()->user()->es_artista ? auth()->user()->nombre_artistico : auth()->user()->nombre }}
        </div>
        @if(auth()->user()->email)
          <div class="profile-email">{{ auth()->user()->email }}</div>
        @endif
      </div>
    </div>

    <!-- Dos botones abajo: Subir música + Traductor -->
    <div class="actions-two">
      @auth
        @if(auth()->user()->es_artista)
          <a class="pill-btn" href="{{ route('musica.subir') }}">
            <i class="fa-solid fa-upload"></i><span class="label traducible">Subir música</span>
          </a>
        @endif
      @endauth
      <button class="pill-btn" type="button" id="translatorBtn">
        <i class="fa-solid fa-language"></i><span class="label traducible">Traductor</span>
      </button>
    </div>

    <!-- Configuración centrado con texto -->
    <div class="center-config">
      <a href="{{ route('ed_perfil') }}" class="center-btn" title="Configuración">
        <i class="fa-solid fa-gear"></i><span class="label traducible">Configuración</span>
      </a>
    </div>

    <!-- Lista inferior: Perfil y Salir -->
    <ul class="dropdown-list list-bottom">
      <li>
        <a href="{{ route('ed_perfil') }}" class="dropdown-item">
          <i class="fa-solid fa-user"></i><span class="label traducible">Perfil</span>
        </a>
      </li>
      <li class="dropdown-sep"></li>
      <li>
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button type="submit" class="logout-wide">
            <i class="fa-solid fa-right-from-bracket"></i><span class="label traducible">Salir</span>
          </button>
        </form>
      </li>
    </ul>
  </div>
</div>


</header>

<style>


/* ===== Header en GRID: no afecta el contenido del medio ===== */
.header{
  position: sticky; top:0; z-index:40;
  display: grid;
  grid-template-columns: 1fr auto auto; /* buscador | iconos | usuario */
  grid-template-areas: "search actions user";
  gap: 16px;
  padding:10px 350px;

  background: linear-gradient(90deg,var(--color-bg-1, #12121a),var(--color-bg-2, #0e0e18));
  box-shadow: 0 10px 30px rgba(0,0,0,.25);
}

/* Anclar cada bloque a su área */
.header-search-group{ grid-area: search; }
.quick-actions{ grid-area: actions; }
.user-menu{ grid-area: user; }


/* Grupo de búsqueda */
/* ====== BUSCADOR (responsive) ====== */
.header-search-group{
  display:flex; align-items:center; gap:12px;
  flex: 1 1 520px;       /* ancho ideal en desktop */
  min-width: 260px;
  height: 48px;
  padding: 0 10px 0 14px;
  border-radius: 20px;
  border:1px solid var(--color-line);
  background: rgba(255,255,255,.06);
  box-shadow:
    inset 0 0 0 1px rgba(255,255,255,.03),
    0 0 0 0 rgba(168,85,247,0);
  transition: border-color var(--anim-dur) var(--anim-ease),
              background var(--anim-dur) var(--anim-ease),
              box-shadow var(--anim-dur) var(--anim-ease);
}
.header-search-group:focus-within{
  border-color: var(--color-accent);   /* morado */
  background: rgba(255,255,255,.10);
  box-shadow: 0 0 0 3px color-mix(in oklab, var(--color-accent) 30%, transparent);
}
.search{
  flex:1; border:0; outline:0;
  background:transparent; color:var(--color-text);
  font-size:15px;
}
.filter-btn{
  display:flex; align-items:center; gap:8px;
  height:36px; padding:0 12px;
  border-radius:12px; border:1px solid var(--color-line);
  background:#181826; color:#eaeaea; cursor:pointer;
  transition: transform .08s ease, border-color var(--anim-dur) var(--anim-ease);
}
.filter-btn:hover{ border-color: var(--color-accent); }
.filter-btn:active{ transform: scale(.98); }

/* ====== RESPONSIVE ====== */
@media (max-width: 992px){
  .header-search-group{
    flex: 1 1 100%;   /* ocupa todo el ancho */
    order: 2;         /* se va abajo si el header se envuelve */
  }
}
@media (max-width: 600px){
  .header-search-group{
    height: 40px;
    gap: 8px;
  }
  .search{ font-size:14px; }
  .filter-btn{ height:32px; padding:0 10px; }
  .filter-btn span{ display:none; } /* oculta el texto "Filtros", deja solo el ícono */
}


/* ===== Acciones rápidas (igual que tenías) ===== */
.quick-actions{ display:flex; gap:10px; align-items:center; min-width:0; }
.qa-btn{
  width:42px; height:42px; display:grid; place-items:center;
  border-radius:12px; border:1px solid var(--color-line);
  background:#181826; color:#eaeaea; cursor:pointer;
  transition: transform .08s ease, border-color var(--anim-dur) var(--anim-ease);
  text-decoration:none;
}
.qa-btn:hover{ border-color: var(--color-accent) }
.qa-btn:active{ transform: scale(.97) }

/* ===== User menu (igual) ===== */
/* =========== DROPDOWN DE USUARIO =========== */
.user-menu{ position:relative; }

/* Botón/chip del usuario */
.user-chip{
  display:flex; align-items:center; gap:10px;
  height:42px; padding:0 12px; cursor:pointer;
  border-radius:14px; border:1px solid var(--color-line);
  background:#181826; color:#eaeaea;
  transition: border-color var(--anim-dur) var(--anim-ease), background var(--anim-dur) var(--anim-ease), transform .08s;
}
.user-chip:hover{ border-color: var(--color-a1); }
.user-chip:active{ transform: scale(.98); }
.chip-avatar{
  width:28px; height:28px; border-radius:10px; object-fit:cover;
  border:1px solid var(--color-line);
}
.chip-name{ max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }

/* ---------- Dropdown base (oculto por defecto) ---------- */
.dropdown-menu{
  position:absolute; right:0; top:calc(100% + 10px);
  min-width: 220px; max-width: 280px;
  padding:8px;
  border:1px solid var(--color-line);
  border-radius:14px;
  background: #11131d;
  box-shadow: 0 18px 40px rgba(0,0,0,.40);
  z-index: 1000;

  opacity:0; transform: translateY(-8px) scale(.98);
  visibility:hidden; pointer-events:none;
  transition: opacity .18s var(--anim-ease), transform .18s var(--anim-ease), visibility .18s steps(1,end);
}

.user-menu.open .dropdown-menu,
#userDropdown[data-open="true"],
#userDropdown[aria-expanded="true"],
#userDropdown[style*="display: block"]{
  opacity:1; transform: translateY(0) scale(1);
  visibility:visible; pointer-events:auto;
}

/* Flechita decorativa */
.dropdown-menu::before{
  content:""; position:absolute; top:-8px; right:18px;
  width:14px; height:14px; transform: rotate(45deg);
  background:#11131d;
  border-left:1px solid var(--color-line); border-top:1px solid var(--color-line);
}

/* Lista vertical de acciones */
.dropdown-list{
  display:flex; flex-direction:column; gap:4px; margin:0; padding:0; list-style:none;
}

/* Ítems */
.dropdown-item{
  display:flex; align-items:center; gap:10px;
  width:100%; padding:10px 12px;
  border-radius:10px;
  color:#eaeaea; text-decoration:none;
  border:1px solid transparent; background: transparent;
  cursor:pointer; user-select:none;
  transition: background .16s var(--anim-ease), border-color .16s var(--anim-ease), transform .06s;
}
.dropdown-item i{ width:18px; text-align:center; font-size:14px; opacity:.95; }
.dropdown-item .label{ flex:1; font-size:14px; font-weight:600; letter-spacing:.2px; }
.dropdown-item .kbd{
  font-size:11px; color:var(--color-dim); background:#1a1d2a; border:1px solid var(--color-line);
  padding:2px 6px; border-radius:6px;
}

.dropdown-item:hover{ background: rgba(255,255,255,.06); border-color: var(--color-line); }
.dropdown-item:active{ transform: scale(.99); }

/* Estados especiales */
.dropdown-item.primary:hover{ background: color-mix(in oklab, var(--color-a1) 22%, transparent); border-color: color-mix(in oklab, var(--color-a1) 35%, var(--color-line)); }
.dropdown-item.settings:hover{ background: rgba(124,58,237,.12); border-color: rgba(124,58,237,.35); }
.dropdown-item.logout{ color:#ffb6b6; }
.dropdown-item.logout:hover{ background: rgba(255,0,0,.08); border-color: rgba(255,0,0,.25); }

/* Separador */
.dropdown-sep{
  height:1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,.10), transparent);
  margin:6px 8px;
  border:none;
}

/* ---------- Submenú (Cambiar idioma) ---------- */
.dropdown-item.has-submenu{
  position:relative; padding-right:34px;
}
.dropdown-item.has-submenu .chev{
  margin-left:auto; font-size:12px; opacity:.8;
}

.submenu{
  position:absolute; top:4px; right: calc(100% + 8px);
  min-width: 200px; padding:6px;
  border:1px solid var(--color-line); border-radius:12px;
  background:#0f1120; box-shadow: 0 16px 36px rgba(0,0,0,.38);
  opacity:0; transform: translateY(-6px); visibility:hidden; pointer-events:none;
  transition: opacity .16s var(--anim-ease), transform .16s var(--anim-ease), visibility .16s steps(1,end);
  z-index:1001;
}
.dropdown-item.has-submenu:hover .submenu{
  opacity:1; transform: translateY(0); visibility:visible; pointer-events:auto;
}

.submenu .dropdown-item{
  padding:9px 10px;
}
.submenu .dropdown-item.active{
  background: rgba(255,255,255,.08); border-color: var(--color-line);
}

/* Accesibilidad foco */
.dropdown-item:focus-visible{
  outline:2px solid color-mix(in oklab, var(--color-a2) 60%, transparent);
  outline-offset:2px;
}

/* Responsive: ancho pequeño */
@media (max-width: 560px){
  .dropdown-menu{ right:8px; min-width: 200px; }
  .submenu{
    right:auto; left:0; top: calc(100% + 8px);
  }
}

/* Opcional: micro-glow al pasar sobre el dropdown */
.dropdown-menu::after{
  content:""; position:absolute; inset:0; border-radius:inherit; pointer-events:none;
  background: radial-gradient(90% 60% at 70% 0%, rgba(168,85,247,.12), transparent 60%);
  mix-blend-mode: screen;
}


/* ===== Responsive: iconos debajo del buscador ===== */
@media (max-width: 992px){
  .header{
    grid-template-columns: 1fr auto;
    grid-template-areas:
      "search search"
      "actions user";
    row-gap:10px;
  }
  .qa-btn{ width:40px; height:40px; }
  .chip-name{ max-width:140px; }
}

@media (max-width: 768px){
  .header{
    grid-template-columns: 1fr;
    grid-template-areas:
      "search"
      "actions"
      "user";
    gap:10px;
  }
  .quick-actions{ justify-content:center; flex-wrap:wrap; gap:8px; }
  .qa-btn{ width:38px; height:38px; }
  .user-menu{ justify-self: end; }
  .chip-name{ display:none; }
  .user-chip{ height:38px; padding:0 10px; }
}

@media (max-width: 480px){
  .header{ padding:8px; border-radius:12px; }
  .qa-btn{ width:34px; height:34px; border-radius:10px; }
  .chip-avatar{ width:24px; height:24px; }
  .user-chip{ height:34px; padding:0 8px; gap:6px; }
  .dropdown-menu{ min-width:160px; }
}

/* Dropdown siempre en flujo; visibilidad por clase/aria */
.dropdown-menu{ display:block; opacity:0; transform:translateY(-8px) scale(.98); visibility:hidden; pointer-events:none; }
.user-menu.open .dropdown-menu,
#userDropdown[data-open="true"], #userDropdown[aria-expanded="true"]{ opacity:1; transform:translateY(0) scale(1); visibility:visible; pointer-events:auto; z-index:1000; }

/* Layout del card superior */
.profile-grid{
  display:grid; grid-template-columns:auto 1fr; gap:12px;
  padding:12px; margin-bottom:10px;
  border:1px solid rgba(255,255,255,.08);
  border-radius:12px;
  background: linear-gradient(180deg,#15192a,#0f1120);
}
.avatar-wrap{ position:relative; width:60px; height:60px; }
.profile-avatar{
  width:60px; height:60px; border-radius:12px; object-fit:cover;
  border:1px solid rgba(255,255,255,.10);
  display:block;
}

/* Botón EDITAR */
.edit-chip{
  position:absolute; right:-6px; top:50%; transform:translateY(-50%);
  height:28px; padding:0 10px;
  display:flex; align-items:center; gap:6px;
  border-radius:999px; border:1px solid var(--color-line);
  background:#1a1e33; color:#eaeaea; text-decoration:none;
  box-shadow: 0 6px 18px rgba(0,0,0,.35);
  transition: transform .08s ease, border-color var(--anim-dur) var(--anim-ease), background var(--anim-dur) var(--anim-ease);
  white-space:nowrap;
  font-size:12px; font-weight:700;
}
.edit-chip:hover{ background:#212749; border-color: var(--color-a1); }
.edit-chip:active{ transform:translateY(-50%) scale(.96); }

.id-block{ display:flex; flex-direction:column; justify-content:center; min-width:0; }
.profile-name{ font-weight:800; line-height:1.1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.profile-email{ font-size:12px; color:var(--color-dim); margin-top:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* Dos botones en fila */
.actions-two{
  display:grid; grid-template-columns:1fr 1fr; gap:8px;
  padding:0 12px; margin-bottom:10px;
}
.pill-btn{
  display:flex; align-items:center; justify-content:center; gap:8px;
  height:36px; padding:0 12px; border-radius:999px;
  background:#181a2b; color:#eaeaea; border:1px solid var(--color-line);
  text-decoration:none; cursor:pointer;
  transition: transform .08s ease, border-color var(--anim-dur) var(--anim-ease), background var(--anim-dur) var(--anim-ease);
}
.pill-btn:hover{ border-color: var(--color-a1); background:#1b1e32; }
.pill-btn:active{ transform:scale(.98); }

/* Configuración centrado con texto */
.center-config{ display:flex; justify-content:center; padding:2px 12px 12px; }
.center-btn{
  display:flex; align-items:center; gap:8px; justify-content:center;
  padding:8px 14px; border-radius:999px;
  border:1px solid var(--color-line); background:#181a2b; color:#eaeaea; text-decoration:none;
  font-weight:700; cursor:pointer;
  transition: transform .08s ease, border-color var(--anim-dur) var(--anim-ease), background var(--anim-dur) var(--anim-ease);
}
.center-btn:hover{ border-color: var(--color-a1); background:#1b1e32; }
.center-btn:active{ transform:scale(.98); }

/* Botón Salir ancho */
.list-bottom{ margin-top:6px; }
.logout-wide{
  width:100%; height:40px;
  display:flex; align-items:center; justify-content:center; gap:10px;
  border-radius:10px; border:1px solid rgba(255,0,0,.25);
  background: rgba(255,0,0,.06); color:#ffb3b3; font-weight:800; cursor:pointer;
  transition: background var(--anim-dur) var(--anim-ease), border-color var(--anim-dur) var(--anim-ease), transform .06s;
}
.logout-wide:hover{ background: rgba(255,0,0,.10); border-color: rgba(255,0,0,.35); }
.logout-wide:active{ transform:scale(.99); }

/* Responsive */
@media (max-width:560px){
  .actions-two{ grid-template-columns:1fr 1fr; }
}





</style>

<script>
/* Toggle del menú de usuario y cierre al hacer click fuera */
(function(){
  const btn = document.getElementById('userMenuBtn');
  const dd  = document.getElementById('userDropdown');
  if(!btn || !dd) return;
  btn.addEventListener('click', (e)=>{
    e.stopPropagation();
    dd.style.display = dd.style.display === 'none' || !dd.style.display ? 'block' : 'none';
  });
  document.addEventListener('click', ()=>{ dd.style.display = 'none'; });
})();
// en vez de tocar style.display
document.getElementById('userMenuBtn').addEventListener('click', (e)=>{
  e.stopPropagation();
  document.querySelector('.user-menu')?.classList.toggle('open');
});
document.addEventListener('click', ()=> document.querySelector('.user-menu')?.classList.remove('open'));
<script>
document.addEventListener('DOMContentLoaded', () => {
  const wrapper = document.querySelector('.user-menu');
  const btn = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userDropdown');
  if (!wrapper || !btn || !menu) return;

  const openMenu = (open) => {
    wrapper.classList.toggle('open', open);
    menu.setAttribute('data-open', open ? 'true' : 'false');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.setAttribute('aria-hidden', open ? 'false' : 'true');
  };

<script>
document.addEventListener('DOMContentLoaded', () => {
  const wrapper = document.querySelector('.user-menu');
  const btn = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userDropdown');
  if (!wrapper || !btn || !menu) return;

  const openMenu = (open) => {
    wrapper.classList.toggle('open', open);
    menu.setAttribute('data-open', open ? 'true' : 'false');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.setAttribute('aria-hidden', open ? 'false' : 'true');
  };

  btn.addEventListener('click', (e) => { e.stopPropagation(); openMenu(menu.getAttribute('data-open') !== 'true'); });
  menu.addEventListener('click', (e) => e.stopPropagation());
  document.addEventListener('click', () => openMenu(false));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') openMenu(false); });
});
</script>

</script>

</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('userMenuBtn');
    const menu = document.getElementById('userDropdown');

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', () => {
        menu.style.display = 'none';
    });
});



</script>
