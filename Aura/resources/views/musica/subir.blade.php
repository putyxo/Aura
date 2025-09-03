<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Subir Música</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/subir.css')
</head>
<body>

  @include('components.footer')
    @include('components.sidebar')
    @include('components.header')
<!-- ================= SELECCIÓN DE TIPO ================= -->
<section class="home active">
  <h2 class="section-title">¿Qué deseas subir?</h2>
  <div class="cards-container">
    <div class="card" onclick="mostrarRegistro('cancion')">
      <div class="card-icon">🎵</div>
      <h3 class="card-title">Canción individual</h3>
      <p class="card-desc">Sube una sola pista con su portada.</p>
    </div>
    <div class="card" onclick="mostrarRegistro('album')">
      <div class="card-icon">📀</div>
      <h3 class="card-title">Álbum</h3>
      <p class="card-desc">Sube varias canciones con una portada.</p>
    </div>
  </div>
</section>

<form method="post" enctype="multipart/form-data" id="uploadForm" class="upload-form hidden" action="{{ route('songs.store') }}">
  @csrf
  <button type="button" onclick="volverASeleccion()" class="btn-volver">← Volver</button>

  <!-- Formulario Canción -->
  <div id="form-cancion" class="hidden">
    <label for="nombre">Nombre de la canción</label>
    <input type="text" id="nombre" name="nombre">

    <div class="dropzone" id="dropzone-single">
      <div class="dz-content">
        <i class="dz-icon fas fa-music"></i>
        <p class="dz-title">Arrastra tu archivo MP3 aquí</p>
        <p class="dz-sub">o haz clic para seleccionarlo</p>
      </div>
      <input type="file" id="mp3" name="mp3" accept=".mp3,audio/mpeg">
    </div>
    <div id="mp3Preview" class="preview"></div>

    <div class="dropzone" id="dz-single-cover">
      <div class="dz-content">
        <i class="dz-icon fas fa-image"></i>
        <p class="dz-title">Arrastra la portada aquí</p>
        <p class="dz-sub">Formatos: JPG, PNG, WEBP</p>
      </div>
      <input type="file" id="portada" name="portada" accept="image/*">
    </div>
    <div id="singleCoverPreview" class="preview"></div>
  </div>

  <!-- Formulario Álbum -->
  <div id="form-album" class="hidden">
    <label for="title">Título del álbum</label>
    <input type="text" id="title" name="title">

    <div class="dropzone" id="dz-cover">
      <div class="dz-content">
        <i class="dz-icon fas fa-image"></i>
        <p class="dz-title">Arrastra la portada aquí</p>
        <p class="dz-sub">Formatos: JPG, PNG, WEBP</p>
      </div>
      <input type="file" id="cover" name="cover" accept="image/*">
    </div>
    <div id="coverPreview" class="preview"></div>

    <div class="dropzone" id="dz-tracks">
      <div class="dz-content">
        <i class="dz-icon fas fa-compact-disc"></i>
        <p class="dz-title">Arrastra varias canciones MP3</p>
        <p class="dz-sub">o haz clic para seleccionarlas</p>
      </div>
      <input type="file" id="tracks" name="tracks[]" accept=".mp3,audio/mpeg" multiple>
    </div>
    <div id="tracks-list"></div>
  </div>

  <button type="submit" id="btn-submit">Subir</button>
</form>

<script>
// Cambia entre "cancion" y "album"
const home        = document.querySelector('.home');
const form        = document.getElementById('uploadForm');
const btnSubmit   = document.getElementById('btn-submit');

// SINGLE
const formSong    = document.getElementById('form-cancion');
const dzSingle    = document.getElementById('dropzone-single');
const mp3Input    = document.getElementById('mp3');
const nombreInput = document.getElementById('nombre');

// ÁLBUM
const formAlbum   = document.getElementById('form-album');
const dzCover     = document.getElementById('dz-cover');
const inputCover  = document.getElementById('cover');
const dzTracks    = document.getElementById('dz-tracks');
const inputTracks = document.getElementById('tracks');
const list        = document.getElementById('tracks-list');
const titleAlbum  = document.getElementById('title');

// Cambiar entre los formularios de canción y álbum
function mostrarRegistro(tipo) {
  home.classList.add('hidden');
  form.classList.remove('hidden');

  if (tipo === 'album') {
    form.action = "{{ route('albums.store') }}";
    btnSubmit.textContent = 'Crear Álbum';

    titleAlbum.required = true;
    inputTracks.required = true;

    nombreInput.required = false;
    mp3Input.required = false;

    formAlbum.classList.remove('hidden');
    formSong.classList.add('hidden');
  } else {
    form.action = "{{ route('songs.store') }}";
    btnSubmit.textContent = 'Subir Canción';

    nombreInput.required = true;
    mp3Input.required = true;

    titleAlbum.required = false;
    inputTracks.required = false;

    formSong.classList.remove('hidden');
    formAlbum.classList.add('hidden');
    list.innerHTML = '';
  }
}

function volverASeleccion() {
  home.classList.remove('hidden');
  form.classList.add('hidden');
}

// ===== Drag & drop SINGLE
dzSingle.addEventListener('click', () => mp3Input.click());

// ===== Drag & drop Álbum portada
dzCover?.addEventListener('click', () => inputCover.click());

// ===== Drag & drop Álbum tracks
dzTracks.addEventListener('click', () => inputTracks.click());
inputTracks.addEventListener('change', renderTitles);

function renderTitles() {
  list.innerHTML = '';
  const files = Array.from(inputTracks.files || []);
  if (!files.length) { list.style.display = 'none'; return; }
  list.style.display = 'flex';
  files.forEach((f, i) => {
    const base = f.name.replace(/\.[^/.]+$/, '');
    const row = document.createElement('div');
    row.classList.add('track-row');
    row.innerHTML = `
      <audio controls src="${URL.createObjectURL(f)}"></audio>
      <input type="text" name="titles[]" placeholder="Editar título" value="${base}">
    `;
    list.appendChild(row);
  });
}

// ===== Drag & drop portada del álbum
dzCover.addEventListener('click', () => inputCover.click());
dzCover.addEventListener('dragover', e => { 
  e.preventDefault(); 
  dzCover.classList.add('dragover'); 
});
dzCover.addEventListener('dragleave', () => dzCover.classList.remove('dragover'));
dzCover.addEventListener('drop', e => {
  e.preventDefault();
  dzCover.classList.remove('dragover');
  if (e.dataTransfer.files.length) {
    inputCover.files = e.dataTransfer.files;
    inputCover.dispatchEvent(new Event('change'));
  }
});

// Vista previa portada álbum
inputCover.addEventListener('change', () => {
  coverPreview.innerHTML = "";
  const file = inputCover.files[0];
  if (file) {
    const img = document.createElement("img");
    img.src = URL.createObjectURL(file);
    img.style.maxWidth = "100%";
    img.style.borderRadius = "8px";
    coverPreview.appendChild(img);
  }
});

// ===== Drag & drop tracks
dzTracks.addEventListener('click', () => inputTracks.click());
dzTracks.addEventListener('dragover', e => { 
  e.preventDefault(); 
  dzTracks.classList.add('dragover'); 
});
dzTracks.addEventListener('dragleave', () => dzTracks.classList.remove('dragover'));
dzTracks.addEventListener('drop', e => {
  e.preventDefault();
  dzTracks.classList.remove('dragover');
  if (e.dataTransfer.files.length) {
    inputTracks.files = e.dataTransfer.files;
    renderTitles();
  }
});
inputTracks.addEventListener('change', renderTitles);

// Vista previa canciones con título editable
function renderTitles() {
  list.innerHTML = '';
  const files = Array.from(inputTracks.files || []);
  if (!files.length) { list.style.display = 'none'; return; }
  list.style.display = 'flex';
  files.forEach((f, i) => {
    const base = f.name.replace(/\.[^/.]+$/, '');
    const row = document.createElement('div');
    row.classList.add('track-row');
    row.innerHTML = `
      <audio controls src="${URL.createObjectURL(f)}"></audio>
      <input type="text" name="titles[]" placeholder="Editar título" value="${base}">
    `;
    list.appendChild(row);
  });
}

// Vista previa portada (canción individual)
const dzSingleCover = document.getElementById('dz-single-cover');
const portadaInput  = document.getElementById('portada');
const singleCoverPreview = document.getElementById('singleCoverPreview');

dzSingleCover.addEventListener('click', () => portadaInput.click());
dzSingleCover.addEventListener('dragover', e => { 
  e.preventDefault(); 
  dzSingleCover.classList.add('dragover'); 
});
dzSingleCover.addEventListener('dragleave', () => dzSingleCover.classList.remove('dragover'));
dzSingleCover.addEventListener('drop', e => {
  e.preventDefault();
  dzSingleCover.classList.remove('dragover');
  if (e.dataTransfer.files.length) {
    portadaInput.files = e.dataTransfer.files;
    portadaInput.dispatchEvent(new Event('change'));
  }
});

// Vista previa portada
portadaInput.addEventListener('change', () => {
  singleCoverPreview.innerHTML = "";
  const file = portadaInput.files[0];
  if (file) {
    const img = document.createElement("img");
    img.src = URL.createObjectURL(file);
    img.style.maxWidth = "100%";
    img.style.borderRadius = "8px";
    singleCoverPreview.appendChild(img);
  }
});
</script>
</body>
</html>
