<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AURA — Interfaz</title>
  @vite('resources/css/usuarioadmin.css')
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
@yield('content')
@include('components.traductor')
@include('components.footer')

<div class="with-sidebar">
  @include('components.sidebar')
  @include('components.header')
  @include('components.traductor')
  @include('components.fondo')

  <div class="app">
    <div class="with-sidebar">
      <div class="main-content">
        <div class="page-studio studio">

          <div class="studio__toolbar">
            <h1 class="studio__title"><i class="fa-solid fa-film"></i> Usuario</h1>
            <div class="studio__filters">
              <div class="studio__search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" placeholder="Buscar nuevas canciones…" aria-label="Buscar">
              </div>
              <div class="studio__select">
                <label>Visibilidad</label>
                <select>
                  <option value="">Todas</option>
                  <option>Público</option>
                  <option>No listado</option>
                  <option>Privado</option>
                </select>
              </div>
              <div class="studio__select">
                <label>Restricciones</label>
                <select>
                  <option value="">Todas</option>
                  <option>Ninguna</option>
                  <option>Derechos de autor</option>
                  <option>Parcialmente bloqueado</option>
                </select>
              </div>
              <button class="studio__btn" type="button" data-animate="pulse">
                <i class="fa-solid fa-filter"></i> Filtrar
              </button>
            </div>
          </div>

          <div class="studio__table" id="studioTable">
            <div class="studio__thead">
              <div class="c1"></div>
              <div class="c2">Usuario</div>
              <div class="c3">Disponibilidad</div>
              <div class="c4">Baneos</div>
              <div class="c5">Union</div>
              <div class="c6">Subidos</div>
              <div class="c7 ta-r">Acciones</div>
            </div>

            <!-- Fila 1 -->
            <div class="studio__row reveal" data-id="1">
              <div class="c1"><span class="thumb shimmer"></span></div>
              <div class="c2">
                <div class="vtitle">Nombre de usuario</div>
                <a class="vlink" href="https://youtu.be/XXXXXXXX" target="_blank" rel="noopener">https://youtu.be/XXXXXXXX</a>
              </div>
              <div class="c3"><span class="pill pill--publico">Público</span></div>
              <div class="c4"><span class="pill pill--none">2</span></div>
              <div class="c5">7 sept 2020</div>
              <div class="c6">
                <span class="songcount" aria-label="Canciones subidas">
                  <i class="fa-solid fa-music"></i>
                  <b class="songs__num">12</b>
                  <small>canciones</small>
                </span>
              </div>
              <div class="c7">
                <div class="actions ta-r">
                  <button type="button" class="btn-act btn-accept" data-open="#modal-approve" data-id="1" title="Aceptar"><i class="fa-solid fa-check"></i></button>
                  <button type="button" class="btn-act btn-delete"  data-open="#modal-delete"  data-id="1" title="Borrar"><i class="fa-solid fa-trash"></i></button>
                </div>
              </div>
            </div>

          </div>

          <div class="studio__pagination">
            <button class="studio__btn studio__btn--ghost" type="button">
              <i class="fa-solid fa-angle-left"></i> Anterior
            </button>
            <span class="studio__page">1 de 5</span>
            <button class="studio__btn studio__btn--ghost" type="button">
              Siguiente <i class="fa-solid fa-angle-right"></i>
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- Modales compartidos -->
    <div class="modal" id="modal-approve" aria-hidden="true">
      <div class="modal__dialog" role="dialog" aria-modal="true">
        <div class="modal__head">
          <h3>Confirmar aceptación</h3>
          <button class="modal__close" data-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal__body">¿Deseas <strong>aceptar</strong> este contenido?</div>
        <div class="modal__foot">
          <button class="studio__btn studio__btn--ghost" data-close>Cancelar</button>
          <button class="studio__btn" id="approveConfirm">Aceptar</button>
        </div>
      </div>
    </div>

    <div class="modal" id="modal-deny" aria-hidden="true">
      <div class="modal__dialog" role="dialog" aria-modal="true">
        <div class="modal__head">
          <h3>Denegar contenido</h3>
          <button class="modal__close" data-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal__body">
          <label class="modal__label">Motivo</label>
          <textarea id="denyReason" class="modal__input" rows="3" placeholder="Describe el motivo de la denegación"></textarea>
        </div>
        <div class="modal__foot">
          <button class="studio__btn studio__btn--ghost" data-close>Cancelar</button>
          <button class="studio__btn" id="denyConfirm">Denegar</button>
        </div>
      </div>
    </div>

    <div class="modal" id="modal-delete" aria-hidden="true">
      <div class="modal__dialog" role="dialog" aria-modal="true">
        <div class="modal__head">
          <h3>Eliminar contenido</h3>
          <button class="modal__close" data-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal__body">Acción irreversible. ¿Borrar definitivamente?</div>
        <div class="modal__foot">
          <button class="studio__btn studio__btn--ghost" data-close>Cancelar</button>
          <button class="studio__btn" id="deleteConfirm">Borrar</button>
        </div>
      </div>
    </div>

    <!-- Modal inspector único -->
    <div class="modal" id="modal-inspector" aria-hidden="true">
      <div class="modal__dialog modal__dialog--wide" role="dialog" aria-modal="true">
        <div class="modal__head">
          <h3>Detalles del contenido</h3>
          <button class="modal__close" data-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="inspector">
          <div class="inspector__left">
            <div class="inspector__thumb" id="inspectorThumb"></div>
            <div class="inspector__meta">
              <div class="inspector__title" id="inspectorTitle">—</div>
              <a id="inspectorUrl" href="#" target="_blank" rel="noopener" class="inspector__link">—</a>
            </div>
            <div class="inspector__grid">
              <div><span>Visibilidad</span><strong id="inspectorVis">—</strong></div>
              <div><span>Restricción</span><strong id="inspectorRes">—</strong></div>
              <div><span>Fecha</span><strong id="inspectorDate">—</strong></div>
              <div><span>Canciones</span><strong id="inspectorSongs">—</strong></div>
            </div>
          </div>
          <div class="inspector__right">
            <label class="modal__label">Notas para el autor</label>
            <textarea id="inspectorNotes" class="modal__input" rows="6" placeholder="Opcional: feedback o razones..."></textarea>
            <div class="inspector__actions">
              <button class="studio__btn" id="inspectorAccept"><i class="fa-solid fa-check"></i> Aceptar</button>
              <button class="studio__btn" id="inspectorDeny"><i class="fa-solid fa-ban"></i> Denegar</button>
              <button class="studio__btn studio__btn--ghost" id="inspectorDelete"><i class="fa-solid fa-trash"></i> Borrar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  </div>

  <script>
    // Reveal
    (function(){
      const io=new IntersectionObserver(es=>es.forEach(e=>{
        if(e.isIntersecting){e.target.classList.add('reveal--show');io.unobserve(e.target)}
      }),{threshold:.1});
      document.querySelectorAll('.studio__row.reveal').forEach(el=>io.observe(el));
    })();

    // Pulse
    document.addEventListener('click',e=>{
      const b=e.target.closest('[data-animate="pulse"]'); if(!b) return;
      b.classList.remove('pulse'); void b.offsetWidth; b.classList.add('pulse');
    });

    let activeId = null;
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

    // Botones de acciones
    document.addEventListener('click',e=>{
      const act = e.target.closest('.btn-act');
      if(!act) return;
      e.stopPropagation();
      activeId = act.getAttribute('data-id') || act.closest('.studio__row')?.dataset.id || null;
      const modalSel = act.getAttribute('data-open');
      if(modalSel) openModal(modalSel);
    });

    // Click en fila -> Inspector
    document.addEventListener('click',e=>{
      const row = e.target.closest('.studio__row');
      if(!row || e.target.closest('.actions')) return;
      activeId = row.dataset.id;

      const title = row.querySelector('.c2 .vtitle')?.textContent.trim() || 'Sin título';
      const urlEl = row.querySelector('.c2 .vlink');
      const url = urlEl?.href || '#';
      const vis = row.querySelector('.c3 .pill')?.textContent.trim() || '—';
      const res = row.querySelector('.c4 .pill')?.textContent.trim() || '—';
      const date = row.querySelector('.c5')?.textContent.trim() || '—';
      const songs = row.querySelector('.c6 .songs__num, .c6 .songcount b')?.textContent.trim() || '0';

      document.getElementById('inspectorTitle').textContent = title;
      const link = document.getElementById('inspectorUrl'); link.textContent = url; link.href = url;
      document.getElementById('inspectorVis').textContent = vis;
      document.getElementById('inspectorRes').textContent = res;
      document.getElementById('inspectorDate').textContent = date;
      const s = document.getElementById('inspectorSongs'); if (s) s.textContent = songs;

      const th = row.querySelector('.thumb');
      const thumb = document.getElementById('inspectorThumb');
      if (thumb) {
        thumb.style.background = getComputedStyle(th || document.body).background || '#2a2b31';
        thumb.style.border = '1px solid var(--border)';
      }

      openModal('#modal-inspector');
    });

    // Acciones inspector
    document.getElementById('inspectorAccept').onclick=()=>{ closeAll(); openModal('#modal-approve'); }
    document.getElementById('inspectorDeny').onclick=()=>{
      const notes = document.getElementById('inspectorNotes').value;
      const denyArea = document.getElementById('denyReason');
      if(denyArea) denyArea.value = notes;
      closeAll(); openModal('#modal-deny');
    }
    document.getElementById('inspectorDelete').onclick=()=>{ closeAll(); openModal('#modal-delete'); }

    // Confirmaciones
    document.getElementById('approveConfirm').onclick=()=>{ closeAll(); alert("Video "+activeId+" aceptado"); }
    document.getElementById('denyConfirm').onclick=()=>{
      closeAll(); alert("Video "+activeId+" denegado. Motivo: "+(document.getElementById('denyReason')?.value||'')); 
    }
    document.getElementById('deleteConfirm').onclick=()=>{ 
      document.querySelector(`.studio__row[data-id="${activeId}"]`)?.remove(); 
      closeAll(); alert("Video "+activeId+" eliminado"); 
    }
  </script>
</body>
</html>
