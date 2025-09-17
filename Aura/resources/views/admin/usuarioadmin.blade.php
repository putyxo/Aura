<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>AURA — Interfaz</title>
  @vite('resources/css/usuarioadmin.css')
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .avatar-img{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #333}
    .inspector-grid{display:flex;gap:2rem;padding:1rem}
    .inspector-left{flex:1;display:flex;gap:1rem;align-items:flex-start}
    .inspector-avatar img{width:90px;height:90px;border-radius:50%;object-fit:cover;border:2px solid #444}
    .inspector-info h4{margin:0;font-size:1.2rem;color:#fff}
    .inspector-info p{margin:.3rem 0;font-size:.9rem;color:#aaa}
    .info-list{list-style:none;padding:0;margin:.5rem 0 0}
    .info-list li{font-size:.9rem;color:#ccc;margin-bottom:.3rem}
    .inspector-right{flex:1;display:flex;flex-direction:column}
    .inspector-actions{margin-top:1rem;display:flex;gap:.5rem}
    .studio__toolbar {
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.studio__title {
  margin: 0;
}
.btn-admin {
    background: #6f36ff;
    color: #fff;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.2s;
}
.btn-admin:hover {
    background: #5329c7;
}
  </style>
</head>
<body>
@yield('content')
@include('components.traductor')


<div class="with-sidebar">
  @include('components.sidebar')
  @include('components.header')
  @include('components.traductor')
  @include('components.fondo')

  <div class="app">
    <div class="with-sidebar">
      <div class="main-content">
        <div class="page-studio studio">

        <br>
        
          <div class="studio__toolbar">
            <h1 class="studio__title"><i class="fa-solid fa-users"></i> Usuarios</h1>
          </div>
<div class="admin-nav" style="display:flex; gap:10px; justify-content:center; margin:20px 0;">
    <a href="{{ route('admin') }}" class="btn-admin">
        <i class="fa-solid fa-music"></i> Canciones
    </a>
    <a href="{{ route('albumadmin') }}" class="btn-admin">
        <i class="fa-solid fa-film"></i> Álbumes
    </a>
    
</div>
        <br>
          <!-- Tabla de usuarios -->
          <div class="studio__table" id="studioTable">
            <div class="studio__thead">
              <div class="c1"></div>
              <div class="c2">Nombre</div>
              <div class="c3">Tipo</div>
              <div class="c4">Baneos</div>
              <div class="c5">Unión</div>
              <div class="c6">Subidos</div>
              <div class="c7 ta-r">Acciones</div>
            </div>

           <!-- Dentro del foreach de usuarios -->
@foreach($usuarios as $usuario)
  <div class="studio__row reveal"
       data-id="{{ $usuario->id }}"
       data-nombre-artistico="{{ $usuario->nombre_artistico ?? '' }}"
       data-fecha-nacimiento="{{ $usuario->fecha_nacimiento }}"
       data-delete-url="{{ route('usuarios.destroy', $usuario) }}">

    <div class="c1">
      <img src="{{ $usuario->avatar_url }}"
           alt="Avatar de {{ $usuario->nombre }}"
           class="avatar-img" loading="lazy">
    </div>

    <div class="c2">
      <div class="vtitle">{{ $usuario->nombre }}</div>
      <a class="vlink" href="mailto:{{ $usuario->email }}">{{ $usuario->email }}</a>
    </div>

    <div class="c3">
      <span class="pill {{ $usuario->es_artista ? 'pill--publico' : 'pill--none' }}">
        {{ $usuario->es_artista ? 'Artista' : 'Usuario' }}
      </span>
    </div>

    <div class="c4">
      <span class="pill pill--none">0</span>
    </div>

    <div class="c5">{{ $usuario->created_at->format('d M Y') }}</div>

    <div class="c6">
      <span class="songcount" aria-label="Canciones subidas">
        <i class="fa-solid fa-music"></i>
        <b class="songs__num">{{ $usuario->likedSongs()->count() }}</b>
        <small>canciones</small>
      </span>
    </div>

    <div class="c7">
      <div class="actions ta-r">
        <button type="button" class="btn-act btn-accept" data-open="#modal-approve" data-id="{{ $usuario->id }}" title="Aceptar"><i class="fa-solid fa-check"></i></button>
        <button type="button" class="btn-act btn-delete" data-open="#modal-delete" data-id="{{ $usuario->id }}" title="Borrar"><i class="fa-solid fa-trash"></i></button>
      </div>
    </div>
  </div>
@endforeach

          </div>
        </div>
      </div>
    </div>

    <!-- Modal inspector -->
    <div class="modal" id="modal-inspector" aria-hidden="true">
      <div class="modal__dialog modal__dialog--wide" role="dialog" aria-modal="true">
        <div class="modal__head">
          <h3>Detalles del usuario</h3>
        </div>

        <div class="inspector-grid">
          <div class="inspector-left">
            <div class="inspector-avatar">
              <img id="inspectorAvatar" src="{{ asset('img/default-avatar.png') }}" alt="Avatar">
            </div>
            <div class="inspector-info">
              <h4 id="inspectorTitle">—</h4>
              <p><a id="inspectorUrl" href="#">—</a></p>
              <ul class="info-list">
                <li><strong>Tipo:</strong> <span id="inspectorVis">—</span></li>
                <li><strong>Restricción:</strong> <span id="inspectorRes">—</span></li>
                <li><strong>Fecha:</strong> <span id="inspectorDate">—</span></li>
                <li><strong>Canciones:</strong> <span id="inspectorSongs">—</span></li>
              </ul>
            </div>
          </div>

          <div class="inspector-right">
            <label class="modal__label">Notas</label>
            <textarea id="inspectorNotes" class="modal__input" rows="6" placeholder="Opcional: feedback..."></textarea>
            <div class="inspector-actions">
              <button class="studio__btn" id="inspectorAccept"><i class="fa-solid fa-check"></i> Aceptar</button>
              <button class="studio__btn" id="inspectorDeny"><i class="fa-solid fa-ban"></i> Denegar</button>
              <button class="studio__btn studio__btn--ghost" id="inspectorDelete"><i class="fa-solid fa-trash"></i> Borrar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal borrar -->
    <div class="modal" id="modal-delete" aria-hidden="true">
      <div class="modal__dialog" role="dialog" aria-modal="true">
        <div class="modal__head"><h3>Eliminar usuario</h3></div>
        <div class="modal__body">Acción irreversible. ¿Borrar definitivamente este usuario?</div>
        <div class="modal__foot">
          <button class="studio__btn studio__btn--ghost" data-close>Cancelar</button>
          <button class="studio__btn" id="deleteConfirm">Borrar</button>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
  // Animaciones Reveal
  (function(){
    const io=new IntersectionObserver(es=>es.forEach(e=>{
      if(e.isIntersecting){e.target.classList.add('reveal--show');io.unobserve(e.target)}
    }),{threshold:.1});
    document.querySelectorAll('.studio__row.reveal').forEach(el=>io.observe(el));
  })();

  // Pulse anim
  document.addEventListener('click',e=>{
    const b=e.target.closest('[data-animate="pulse"]'); if(!b) return;
    b.classList.remove('pulse'); void b.offsetWidth; b.classList.add('pulse');
  });

  let activeId = null;
  let activeDeleteUrl = null;

  const openModal = (sel)=>{
    const el=document.querySelector(sel);
    if(!el) return;
    el.classList.add('show');
    el.setAttribute('aria-hidden','false');
  };
  const closeAll = ()=>{
    document.querySelectorAll('.modal.show').forEach(m=>{
      m.classList.remove('show');
      m.setAttribute('aria-hidden','true');
    });
  };

  // Cerrar modales
  document.addEventListener('click',e=>{
    if(e.target.matches('.modal,[data-close]')) closeAll();
  });

  // Botones acciones
  document.addEventListener('click',e=>{
    const act = e.target.closest('.btn-act');
    if(!act) return;
    e.stopPropagation();
    const row = act.closest('.studio__row');
    activeId = act.getAttribute('data-id') || row?.dataset.id || null;

    if (act.classList.contains('btn-delete')) {
      activeDeleteUrl = row?.dataset.deleteUrl || `/usuarios/${activeId}`;
    }

    const modalSel = act.getAttribute('data-open');
    if(modalSel) openModal(modalSel);
  });

  // Click en fila -> Inspector
  document.addEventListener('click',e=>{
    const row = e.target.closest('.studio__row');
    if(!row || e.target.closest('.actions')) return;
    activeId = row.dataset.id;

    const title = row.querySelector('.c2 .vtitle')?.textContent.trim() || 'Sin título';
    const nombreArtistico = row.dataset.nombreArtistico || '';
    document.getElementById('inspectorTitle').textContent = nombreArtistico ? `${title} (${nombreArtistico})` : title;

    const urlEl = row.querySelector('.c2 .vlink');
    document.getElementById('inspectorUrl').textContent = urlEl ? urlEl.textContent.trim() : '—';
    document.getElementById('inspectorUrl').href = urlEl ? urlEl.href : '#';

    document.getElementById('inspectorVis').textContent = row.querySelector('.c3 .pill')?.textContent.trim() || '—';
    document.getElementById('inspectorRes').textContent = row.querySelector('.c4 .pill')?.textContent.trim() || '—';
    document.getElementById('inspectorDate').textContent = row.querySelector('.c5')?.textContent.trim() || '—';
    document.getElementById('inspectorSongs').textContent = row.querySelector('.c6 .songs__num')?.textContent.trim() || '0';

    const avatar = row.querySelector('.avatar-img')?.src || "{{ asset('img/default-avatar.png') }}";
    document.getElementById('inspectorAvatar').src = avatar;

    openModal('#modal-inspector');
  });

  // Acciones inspector
  document.getElementById('inspectorAccept').onclick=()=>{ closeAll(); openModal('#modal-approve'); }
  document.getElementById('inspectorDeny').onclick=()=>{ 
    const notes=document.getElementById('inspectorNotes').value;
    const denyArea=document.getElementById('denyReason');
    if(denyArea) denyArea.value=notes;
    closeAll(); openModal('#modal-deny');
  }
  document.getElementById('inspectorDelete').onclick=()=>{ closeAll(); openModal('#modal-delete'); }

  // Confirmación de borrado
  const deleteBtn=document.getElementById('deleteConfirm');
  if(deleteBtn){
    deleteBtn.onclick=()=>{
      if(!activeId) return;
      const url=activeDeleteUrl||`/usuarios/${activeId}`;
      const token=document.querySelector('meta[name="csrf-token"]').content;

      fetch(url,{
        method:'DELETE',
        headers:{
          'X-CSRF-TOKEN':token,
          'X-Requested-With':'XMLHttpRequest',
          'Accept':'application/json'
        }
      })
      .then(async res=>{
        if(!res.ok){
          const txt=await res.text();
          throw new Error(`HTTP ${res.status}: ${txt}`);
        }
        return res.json();
      })
      .then(data=>{
        if(data.success){
          document.querySelector(`.studio__row[data-id="${activeId}"]`)?.remove();
          alert(`Usuario ${activeId} eliminado correctamente.`);
        }else{
          alert('Error al eliminar el usuario.');
        }
        closeAll();
      })
      .catch(err=>{
        console.error(err);
        alert('Error al eliminar el usuario.');
        closeAll();
      });
    };
  }
</script>
</body>
</html>
