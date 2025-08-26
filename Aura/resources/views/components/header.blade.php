
<header class="header">
  <!-- Buscador -->
  <div class="header-search-group">
    <span class="search-icon"></span>
    <input class="search" type="text" placeholder="Buscar..." />
   

    <!-- 🔎 Caja de resultados -->
    <div id="searchResults" class="search-results"></div>
  </div>

  <!-- Acciones rápidas -->
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

  <!-- Usuario -->
  <div class="user-menu">
    <button class="user-chip" id="userMenuBtn" type="button" aria-expanded="false">
     {{-- Avatar pequeño en la barra superior --}}
<img class="chip-avatar"
     src="@if(auth()->user()->avatar)
             {{ drive_img_url(auth()->user()->avatar, 100) }}&v={{ time() }}
           @else
             {{ asset('img/default-user.png') }}
           @endif"
     alt="{{ auth()->user()->nombre_artistico ?? auth()->user()->nombre }}">

      <span class="chip-name">
        {{ auth()->user()->es_artista ? auth()->user()->nombre_artistico : auth()->user()->nombre }}
      </span>
      <i class="fa-solid fa-chevron-down"></i>
    </button>

    <div class="dropdown-menu" id="userDropdown" aria-hidden="true">
      <!-- Avatar + editar -->
       <a href="{{ route('perfil.show', auth()->id()) }}" class="profile-card-link">
  <div class="profile-grid">
    <div class="avatar-wrap">
      <img class="profile-avatar" 
           src="{{ auth()->user()->avatar ?? asset('img/default-avatar.png') }}" 
           alt="Avatar">
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
</a>

      

      <!-- Perfil / Salir -->
      <ul class="dropdown-list list-bottom">
        <li>
          <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" class="logout-wide"><i class="fa-solid fa-right-from-bracket"></i><span>Salir</span></button>
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

/* Contenedor clickeable */
.profile-card-link {
  display:block;
  text-decoration:none;
  color:inherit;
  border-radius:12px;
  transition: transform .1s ease, box-shadow .2s ease;
}
.profile-card-link:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,.35);
}

/* Grid interno */
.profile-grid {
  display:grid;
  grid-template-columns:auto 1fr;
  gap:12px;
  align-items:center;
  padding:12px;
  border:1px solid rgba(255,255,255,.08);
  border-radius:12px;
  background: linear-gradient(180deg,#15192a,#0f1120);
}

/* Avatar */
.avatar-wrap {
  width:52px; height:52px;
  flex-shrink:0;
}
.profile-avatar {
  width:100%; height:100%;
  border-radius:12px;
  object-fit:cover;
  border:1px solid rgba(255,255,255,.1);
  background:#222;
}

/* Texto */
.id-block {
  min-width:0;
  display:flex;
  flex-direction:column;
  justify-content:center;
}
.profile-name {
  font-weight:700;
  font-size:15px;
  color:#f5f5f5;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.profile-email {
  font-size:12px;
  color:#b6b6c8;
  margin-top:3px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.profile-card-link {
  display:block;
  text-decoration:none;
  color:inherit;
  border-radius:14px;  
  border:2px solid transparent;          /* sin borde normal */
  overflow:hidden;                       /* respeta esquinas */
  transition: border-color .2s, box-shadow .2s, transform .1s;
}

.profile-card-link:hover {
  border-color:#a855f7;                  /* borde morado */
  box-shadow:0 0 12px rgba(168,85,247,.6);
  transform:translateY(-1px);
}

/* Fondo solo en el grid, sin borde */
.profile-grid {
  display:grid;
  grid-template-columns:auto 1fr;
  gap:12px;
  align-items:center;
  padding:12px;
  border-radius:inherit;                  /* hereda esquinas */
  background: linear-gradient(180deg,#15192a,#0f1120);
}



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




/* Asegurar que los resultados se posicionen respecto al buscador */
.header-search-group {
  position: relative; /* 👈 clave */
}

/* Caja de resultados */
.search-results {
  position: absolute;
  top: calc(100% + 6px); /* justo debajo del input */
  left: 0;
  right: 0;
  background: #11131d;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 12px;
  min-height: 50px;           /* 👈 altura mínima visible */
  max-height: 300px;
  overflow-y: auto;
  padding: 6px 0;
  box-shadow: 0 10px 30px rgba(0,0,0,.45);
  z-index: 500;
  display: none;              /* 👈 oculto por defecto */
}

/* Scrollbar bonito */
.search-results::-webkit-scrollbar { width: 8px; }
.search-results::-webkit-scrollbar-thumb {
  background: #2c2c3e;
  border-radius: 10px;
}
.search-results::-webkit-scrollbar-thumb:hover {
  background: #3d3d55;
}

/* Ítems de resultado */
.search-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  color: #eaeaea;
  text-decoration: none;
  border-radius: 6px;
  transition: background .2s;
}
.search-item:hover { background: rgba(255,255,255,.08); }

.search-item img {
  width: 38px;
  height: 38px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
}
.search-item div { display: flex; flex-direction: column; line-height: 1.2; }
.search-item strong { font-size: 14px; font-weight: 600; }
.search-item small { font-size: 12px; color: var(--color-dim); }
</style>

<script>
// ===================== USER MENU DROPDOWN =====================
(function initUserMenu(){
  const root      = document.querySelector('.user-menu');
  const btn       = document.getElementById('userMenuBtn');
  const dropdown  = document.getElementById('userDropdown');

  if (!root || !btn || !dropdown) return;

  // Evita doble-bind al recargar con PJAX
  if (btn.dataset.bound === 'true') return;
  btn.dataset.bound = 'true';

  function openMenu(){
    root.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    dropdown.setAttribute('aria-hidden', 'false');
  }
  function closeMenu(){
    root.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    dropdown.setAttribute('aria-hidden', 'true');
  }
  function toggleMenu(){
    if (root.classList.contains('open')) closeMenu(); else openMenu();
  }

  // Click en el chip: abrir/cerrar
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggleMenu();
  });

  // Cerrar al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!root.contains(e.target)) closeMenu();
  });

  // Cerrar con Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });

  // Si el dropdown tiene links, no dejes que el click cierre antes de tiempo
  dropdown.addEventListener('click', (e) => {
    // Permite clicks normales en enlaces y botones, pero no cierres por burbujeo
    e.stopPropagation();
  });

  // Soporte para PJAX: re-inicializar tras navegación parcial
  if (window.$ && $.pjax) {
    $(document).on('pjax:success', function(){ 
      // Re-intenta inicializar por si el header se re-renderizó
      setTimeout(initUserMenu, 0);
    });
  }

  // (Opcional) Autocargar avatar si usas un campo en el backend
  try {
    const avatarEls = root.querySelectorAll('.chip-avatar, .profile-avatar');
    const fallback  = "{{ asset('img/default-avatar.png') }}";
    // Si el backend ya imprime src, no hace falta esto.
    avatarEls.forEach(img => {
      if (!img.getAttribute('src')) img.setAttribute('src', fallback);
    });
  } catch (_) {}
})();
</script>


<script>
/* === Buscador === */
const searchInput = document.querySelector('.search');
const resultsBox = document.getElementById('searchResults');

if (searchInput && resultsBox) {
  searchInput.addEventListener('input', async () => {
    const q = searchInput.value.trim();

    if (q.length < 2) {
      resultsBox.innerHTML = "<div class='search-item'>Escribe al menos 2 letras...</div>";
      resultsBox.style.display = "block";   // 👈 siempre se muestra
      return;
    }

    try {
      const res = await fetch(`/buscar?q=${encodeURIComponent(q)}`);
      const data = await res.json();

      if (!Array.isArray(data) || data.length === 0) {
        resultsBox.innerHTML = "<div class='search-item'>No se encontraron resultados</div>";
      } else {
        resultsBox.innerHTML = data.map(item => `
          <a href="${item.url}" class="search-item">
            <img src="${item.avatar}" alt="${item.tipo}">
            <div>
              <strong>${item.nombre}</strong><br>
              <small>${item.tipo}</small>
            </div>
          </a>
        `).join('');
      }

      resultsBox.style.display = "block"; // 👈 asegúrate que se vea
    } catch (err) {
      console.error("Error en búsqueda:", err);
      resultsBox.innerHTML = "<div class='search-item'>Error al buscar</div>";
      resultsBox.style.display = "block";
    }
  });

  // Cerrar si haces click fuera
  document.addEventListener('click', (e) => {
    if (!resultsBox.contains(e.target) && e.target !== searchInput) {
      resultsBox.style.display = "none";
    }
  });
}


</script>

