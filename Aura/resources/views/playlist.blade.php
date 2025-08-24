<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AURA — Playlists</title>
  @vite('resources/css/playlist.css')
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app">
  <div class="with-sidebar">
    @include('components.sidebar')

    <main class="main-content">

      @if(session('ok'))
        <div class="toast-ok">
          <i class="fa-solid fa-circle-check"></i> <span>{{ session('ok') }}</span>
        </div>
      @endif

      {{-- ====== GRILLA ====== --}}
      <section class="playlist-grid">
        {{-- Tile 0: Nueva playlist (abre modal) --}}
        <button type="button" class="tile tile-create" id="btnOpenPlaylistModal">
          <div class="tile-cover create-cover">
            <i class="fa-solid fa-plus"></i>
          </div>
          <div class="tile-name">Nueva playlist</div>
        </button>

{{-- Tiles 1..N: Playlists desde BD (últimas primero) --}}
@forelse($playlists as $pl)
<a href="{{ route('playlists.show', $pl->id) }}" class="tile">
  <div class="tile-cover">
    @if($pl->cover_url)
      <img src="{{ $pl->cover_url }}" alt="Portada de {{ $pl->nombre }}">
    @else
      <div class="cover-placeholder">Sin portada</div>
    @endif
  </div>
  <div class="tile-name" title="{{ $pl->nombre }}">{{ $pl->nombre }}</div>
</a>
@empty
  {{-- Opcional: estado vacío --}}
  <div class="tile" style="grid-column: span 2; text-align:center; padding:16px">
    Aún no tienes playlists. ¡Crea la primera! ✨
  </div>
@endforelse


{{-- Detalle de Playlist --}}
<div id="playlistDetail" class="playlist-card" hidden>
  <div class="cover">
    <img id="detailCover" src="" alt="Portada Playlist">
  </div>
  <div class="info">
    <h2 id="detailTitle" class="title"></h2>
    <h3 id="detailSubtitle" class="subtitle"></h3>
    <span id="detailUpdate" class="update"></span>
    <p id="detailDescription" class="description"></p>
    <div class="actions">
      <button class="btn play"><i class="fa-solid fa-play"></i> Reproducir</button>
      <button class="btn shuffle"><i class="fa-solid fa-shuffle"></i> Aleatorio</button>
    </div>
  </div>
</div>


    {{-- ====== MODAL ====== --}}
<div id="playlistModalBackdrop" class="modal-backdrop" hidden></div>

<div id="playlistModal" class="modal" hidden role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <button class="modal-close" id="btnClosePlaylistModal" aria-label="Cerrar">×</button>
  <h3 id="modalTitle" class="modal-title">Añadir Playlist</h3>

  <form id="playlistForm" action="{{ route('playlists.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="modal-grid">
      <!-- Columna izquierda -->
      <div class="field" aria-live="polite">
        <label for="pl_nombre" class="label">Nombre del álbum</label>
        <input id="pl_nombre" name="nombre" type="text" class="input" placeholder="(NOMBRE DEL ÁLBUM)" required>
        <small class="field-msg" id="nameMsg"></small>
      </div>

      <!-- Columna derecha: Portada -->
      <div class="cover-field">
        <label class="label">Portada</label>

        <!-- Zona de arrastre -->
        <label for="pl_cover"
               class="cover-uploader"
               id="coverDrop"
               tabindex="0"
               aria-label="Arrastra y suelta una imagen o presiona para seleccionar">
          <input id="pl_cover" name="portada" type="file" accept="image/*" hidden>

          <!-- Vista previa -->
          <img id="coverPreview" alt="Vista previa de la portada" />

          <!-- UI vacía / instrucciones -->
          <div class="uploader-hint" id="uploaderHint">
            <div class="uploader-icon">
              <i class="fa-solid fa-arrow-up-from-bracket"></i>
            </div>
            <div class="uploader-text">
              <strong>Arrastra y suelta</strong> la imagen<br>
              <span class="muted">o haz clic para seleccionar</span>
            </div>
            <div class="uploader-meta">
              <span class="badge">PNG/JPG</span>
              <span class="badge">Máx 5MB</span>
            </div>
          </div>

          <!-- Overlay de estado (dragover) -->
          <div class="uploader-overlay" id="uploaderOverlay" aria-hidden="true">
            Suelta la imagen aquí
          </div>
        </label>

        <small class="hint">PNG/JPG hasta 5MB</small>
        <small class="field-msg" id="coverMsg"></small>
      </div>

      <div class="field field-span2" aria-live="polite">
        <label for="pl_desc" class="label">Descripción</label>
        <textarea id="pl_desc" name="descripcion" class="textarea" rows="4" placeholder="(Descripción)"></textarea>
        <small class="field-msg" id="descMsg"></small>
      </div>
    </div>

    <div class="actions">
      <button type="button" class="btn-secondary" id="btnCancelPlaylist">Cancelar</button>
      <button type="submit" class="btn-primary" id="btnSubmit">Guardar</button>
    </div>
  </form>
</div>

<script>
/* ====== Apertura/Cierre de modal ====== */
const openBtn   = document.getElementById('btnOpenPlaylistModal');
const closeBtn  = document.getElementById('btnClosePlaylistModal');
const cancelBtn = document.getElementById('btnCancelPlaylist');
const backdrop  = document.getElementById('playlistModalBackdrop');
const modal     = document.getElementById('playlistModal');

function openModal(){
  backdrop.hidden=false; modal.hidden=false;
  document.documentElement.style.overflow='hidden';
  document.getElementById('pl_nombre').focus();
}
function closeModal(){
  backdrop.hidden=true; modal.hidden=true;
  document.documentElement.style.overflow='';
}

openBtn?.addEventListener('click', e => { e.preventDefault(); openModal(); });
[closeBtn, cancelBtn, backdrop].forEach(el => el?.addEventListener('click', closeModal));
document.addEventListener('keydown', e => { if(e.key==='Escape' && !modal.hidden) closeModal(); });

/* ====== Uploader con Drag & Drop + Validación ====== */
const fileInput = document.getElementById('pl_cover');
const dropArea  = document.getElementById('coverDrop');
const overlay   = document.getElementById('uploaderOverlay');
const hint      = document.getElementById('uploaderHint');
const preview   = document.getElementById('coverPreview');
const coverMsg  = document.getElementById('coverMsg');
const btnSubmit = document.getElementById('btnSubmit');

const MAX_MB = 5;
function showError(el, msg){
  el.textContent = msg;
  el.classList.add('msg-error');
}
function clearMsg(el){
  el.textContent = '';
  el.classList.remove('msg-error', 'msg-ok');
}
function validType(file){
  return /^image\/(png|jpe?g|webp)$/i.test(file.type);
}
function validSize(file){
  return file.size <= MAX_MB*1024*1024;
}
function setPreview(file){
  const url = URL.createObjectURL(file);
  preview.src = url;
  preview.style.display='block';
  hint.style.display='none';
  dropArea.classList.add('has-preview');
}

['dragenter','dragover'].forEach(evt => {
  dropArea.addEventListener(evt, e => {
    e.preventDefault(); e.stopPropagation();
    dropArea.classList.add('is-dragover');
    overlay.style.opacity = '1';
  });
});
['dragleave','drop'].forEach(evt => {
  dropArea.addEventListener(evt, e => {
    e.preventDefault(); e.stopPropagation();
    dropArea.classList.remove('is-dragover');
    overlay.style.opacity = '0';
  });
});

dropArea.addEventListener('drop', e => {
  const file = e.dataTransfer.files?.[0]; if(!file) return;
  clearMsg(coverMsg);
  if(!validType(file)){ showError(coverMsg, 'Formato no permitido. Usa PNG/JPG.'); return; }
  if(!validSize(file)){ showError(coverMsg, `El archivo supera ${MAX_MB}MB.`); return; }
  const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
  setPreview(file);
});

fileInput?.addEventListener('change', e => {
  const file = e.target.files?.[0]; if(!file) return;
  clearMsg(coverMsg);
  if(!validType(file)){ showError(coverMsg, 'Formato no permitido. Usa PNG/JPG.'); fileInput.value=''; return; }
  if(!validSize(file)){ showError(coverMsg, `El archivo supera ${MAX_MB}MB.`); fileInput.value=''; return; }
  setPreview(file);
});

/* ====== Validación mínima del formulario ====== */
document.getElementById('playlistForm')?.addEventListener('submit', (e)=>{
  let ok = true;
  const name = document.getElementById('pl_nombre');
  const nameMsg = document.getElementById('nameMsg');
  clearMsg(nameMsg); clearMsg(coverMsg);

  if(!name.value.trim()){
    showError(nameMsg, 'Ingresa un nombre.'); ok = false;
  }
  if(fileInput.files.length===0){
    showError(coverMsg, 'Agrega una portada.'); ok = false;
  }
  if(!ok){ e.preventDefault(); }
});


/* ====== Mostrar detalle de playlist ====== */
document.querySelectorAll('.tile').forEach(tile => {
  tile.addEventListener('click', () => {
    if(tile.classList.contains('tile-create')) return; // no abrir en "nueva playlist"

    const nombre = tile.dataset.nombre;
    const cover  = tile.dataset.cover;
    const desc   = tile.dataset.desc;
    const updated= tile.dataset.updated;
    const sub    = tile.dataset.subtitle;

    document.getElementById('detailTitle').textContent = nombre;
    document.getElementById('detailSubtitle').textContent = sub;
    document.getElementById('detailUpdate').textContent = updated;
    document.getElementById('detailDescription').textContent = desc || '(Sin descripción)';
    document.getElementById('detailCover').src = cover || 'https://via.placeholder.com/250x250.png?text=Cover';

    document.getElementById('playlistDetail').hidden = false;
  });
});

</script>
              @include('components.footer')
