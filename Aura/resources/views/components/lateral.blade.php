<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
       <!-- Sección derecha: cola de reproducción (estética, estática) -->
      <aside class="queue-section">
        <h3>Cola de reproducción</h3>
        <div class="queue-list">
          <div class="queue-item">
            <img src="https://via.placeholder.com/56" alt="Carátula">
            <div>
              <p class="q-title">Pista en cola</p>
              <p class="q-artist">Artista</p>
            </div>
          </div>
          <div class="queue-item">
            <img src="https://via.placeholder.com/56" alt="Carátula">
            <div>
              <p class="q-title">Otra pista</p>
              <p class="q-artist">Artista</p>
            </div>
          </div>
          <div class="queue-item">
            <img src="https://via.placeholder.com/56" alt="Carátula">
            <div>
              <p class="q-title">Siguiente</p>
              <p class="q-artist">Artista</p>
            </div>
          </div>
        </div>
      </aside>


      <style>
        /* ===== SECCIÓN DERECHA (COLA) ===== */
.queue-section{
  position: sticky; top: 20px;
  height: calc(100vh - 40px);
  background: linear-gradient(180deg, var(--surface-2), #0f0e19);
  border:1px solid var(--line); border-radius: 16px;
  padding: 18px; overflow: hidden; display:flex; flex-direction:column;
  box-shadow: 0 18px 60px rgba(0,0,0,.35);
}
.queue-section h3{
  font-size:1.05rem; font-weight:700; margin-bottom:12px; color:#e7e4ff;
  padding-bottom: 10px; border-bottom:1px solid var(--line);
}
.queue-list{ overflow-y:auto; padding-right: 6px; }
.queue-item{
  display:flex; align-items:center; gap:12px;
  padding:10px; border-radius:12px; transition: background .18s ease, transform .18s ease;
  border:1px solid transparent;
}
.queue-item:hover{ background: rgba(255,255,255,.05); border-color: var(--line); }
.queue-item img{ width:56px; height:56px; border-radius:10px; object-fit:cover; border:1px solid var(--line); }
.q-title{ font-size:.95rem; font-weight:600; }
.q-artist{ font-size:.82rem; color: var(--muted); }

/* ===== SCROLL PERSONALIZADO ===== */
::-webkit-scrollbar{ width: 10px; height: 10px; }
::-webkit-scrollbar-track{ background: transparent; }
::-webkit-scrollbar-thumb{ background: #2a2a2a; border-radius: 6px; border:2px solid transparent; }
::-webkit-scrollbar-thumb:hover{ background: #3a3a3a; }

      </style>
</body>
</html>