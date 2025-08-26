<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Subir Música</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/subir.css')
</head>
<body>
                @include('components.sidebar')
              @include('components.footer')
@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/* ===== Helpers Drive (seguros si se incluyen varias veces) ===== */
if (!function_exists('drive_extract_id')) {
    function drive_extract_id($url) {
        if (!$url) return null;
        $url = trim($url);
        if (preg_match('#/file/d/([^/]+)/#i', $url, $m)) return $m[1];   // /file/d/ID/
        if (preg_match('#[?&]id=([^&]+)#i', $url, $m)) return $m[1];     // ?id=ID
        return null;
    }
}
if (!function_exists('drive_image_view')) {
    function drive_image_view($url) {
        $id = drive_extract_id($url);
        return $id ? "https://drive.google.com/uc?export=view&id={$id}" : null; // para <img>
    }
}

/* ===== Placeholder inline para <img> ===== */
$placeholder = 'data:image/svg+xml;utf8,' . rawurlencode(
  '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="140">
     <rect width="100%" height="100%" rx="12" ry="12" fill="#1f2937"/>
     <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle"
           font-size="14" fill="#9ca3af">Sin portada</text>
   </svg>'
);

/* ===== Portadas de prueba desde BD para las tarjetas ===== */
$coverSongUrl = null;
if ($songRow = DB::table('songs')->whereNotNull('cover_path')->latest('id')->first()) {
    $raw = $songRow->cover_path;
    if ($raw) {
        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $coverSongUrl = Str::contains($raw, 'drive.google') ? (drive_image_view($raw) ?: $raw) : $raw;
        } else {
            $coverSongUrl = Storage::url($raw);
        }
    }
}

$coverAlbumUrl = null;
if (DB::getSchemaBuilder()->hasTable('albums')) {
    if ($albRow = DB::table('albums')->whereNotNull('cover_path')->latest('id')->first()) {
        $raw = $albRow->cover_path;
        if ($raw) {
            if (Str::startsWith($raw, ['http://', 'https://'])) {
                $coverAlbumUrl = Str::contains($raw, 'drive.google') ? (drive_image_view($raw) ?: $raw) : $raw;
            } else {
                $coverAlbumUrl = Storage::url($raw);
            }
        }
    }
}



/* ===== Helpers Drive (seguros si se incluyen varias veces) ===== */
if (!function_exists('drive_extract_id')) {
    function drive_extract_id($url) {
        if (!$url) return null;
        $url = trim($url);
        if (preg_match('#/file/d/([^/]+)/#i', $url, $m)) return $m[1];   // /file/d/ID/
        if (preg_match('#[?&]id=([^&]+)#i', $url, $m)) return $m[1];     // ?id=ID
        return null;
    }
}
if (!function_exists('drive_image_view')) {
    function drive_image_view($url) {
        $id = drive_extract_id($url);
        return $id ? "https://drive.google.com/uc?export=view&id={$id}" : null; // para <img>
    }
}

/* ===== Placeholder inline para <img> ===== */
$placeholder = 'data:image/svg+xml;utf8,' . rawurlencode(
  '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="140">
     <rect width="100%" height="100%" rx="12" ry="12" fill="#1f2937"/>
     <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle"
           font-size="14" fill="#9ca3af">Sin portada</text>
   </svg>'
);

/* ===== Portadas de prueba desde BD para las tarjetas ===== */
$coverSongUrl = null;
if ($songRow = DB::table('songs')->whereNotNull('cover_path')->latest('id')->first()) {
    $raw = $songRow->cover_path;
    if ($raw) {
        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $coverSongUrl = Str::contains($raw, 'drive.google') ? (drive_image_view($raw) ?: $raw) : $raw;
        } else {
            $coverSongUrl = Storage::url($raw);
        }
    }
}

$coverAlbumUrl = null;
if (DB::getSchemaBuilder()->hasTable('albums')) {
    if ($albRow = DB::table('albums')->whereNotNull('cover_path')->latest('id')->first()) {
        $raw = $albRow->cover_path;
        if ($raw) {
            if (Str::startsWith($raw, ['http://', 'https://'])) {
                $coverAlbumUrl = Str::contains($raw, 'drive.google') ? (drive_image_view($raw) ?: $raw) : $raw;
            } else {
                $coverAlbumUrl = Storage::url($raw);
            }
        }
    }
}
@endphp


{{-- ================= SELECCIÓN DE TIPO ================= --}}
<section class="home active">
  <h2>¿Qué deseas subir?</h2>
  <div class="container">
    <div class="card" onclick="mostrarRegistro('cancion')">
      <img
        src="{{ $coverSongUrl ?? $placeholder }}"
        alt="Contenido"
        style="width:100%;height:140px;object-fit:cover;border-radius:12px;border:1px solid rgba(255,255,255,.08)"
        onerror="this.onerror=null;this.src='{{ $placeholder }}'">
      <p>Canción individual</p>
    </div>
    <div class="card" onclick="mostrarRegistro('album')">
      <img
        src="{{ $coverAlbumUrl ?? $placeholder }}"
        alt="Contenido"
        style="width:100%;height:140px;object-fit:cover;border-radius:12px;border:1px solid rgba(255,255,255,.08)"
        onerror="this.onerror=null;this.src='{{ $placeholder }}'">
      <p>Álbum</p>
    </div>
  </div>
</section>

{{-- ================= FORMULARIO (cambia action según selección) ================= --}}
<form method="post" enctype="multipart/form-data" id="uploadForm" class="upload-container" action="{{ route('songs.store') }}">
<form method="post" enctype="multipart/form-data" id="uploadForm" action="{{ route('songs.store') }}">
  @csrf

  <button type="button" onclick="volverASeleccion()" class="btn-volver">Volver a elegir</button>

  {{-- -------- SINGLE -------- --}}
  <div class="dropzone" id="dropzone-single">
    <span class="dropzone-icon">🎵</span>
    <span class="dropzone-text">Haz click o arrastra tu archivo MP3 aquí</span>
    <input type="file" id="mp3" name="mp3" accept=".mp3,audio/mpeg">
  </div>
  <div id="mp3Preview" class="preview" style="margin-top:8px"></div>

  {{-- -------- ÁLBUM -------- --}}
  <div id="album-dropzones" class="hidden" style="display:none; gap:12px; flex-direction:column">
    <div class="dropzone" id="dz-cover">
      <span class="dropzone-icon">🖼️</span>
      <span class="dropzone-text">Haz click o arrastra la portada (JPG/PNG/WEBP) – máx 10MB</span>
      <input type="file" id="cover" name="cover" accept="image/*">
    </div>
    <div id="coverPreview" class="preview" style="margin-top:8px"></div>

    <div class="dropzone" id="dz-tracks">
      <span class="dropzone-icon">🎵</span>
      <span class="dropzone-text">Haz click o arrastra varios MP3 aquí</span>
      <input type="file" id="tracks" name="tracks[]" accept=".mp3,audio/mpeg" multiple>
    </div>
    <div id="tracks-list" style="display:none;gap:10px;flex-direction:column"></div>
  </div>

  <div class="upload-form">
    {{-- -------- FORM SINGLE -------- --}}
    <div id="form-cancion">
      <label for="nombre">Nombre de la canción:</label>
      <input type="text" id="nombre" name="nombre">

      <label for="categoria-cancion">Género:</label>
      <select id="categoria-cancion" name="categoria">
        <option value="">Selecciona un género</option>
        <option value="Pop">Pop</option>
        <option value="Rock">Rock</option>
        <option value="Reggaeton">Reggaeton</option>
        <option value="Rap">Rap</option>
        <option value="Trap">Trap</option>
        <option value="Electrónica">Electrónica</option>
        <option value="Indie">Indie</option>
        <option value="Jazz">Jazz</option>
        <option value="Salsa">Salsa</option>
        <option value="Cumbia">Cumbia</option>
        <option value="Regional">Regional</option>
        <option value="Metal">Metal</option>
        <option value="Bachata">Bachata</option>
        <option value="Reggae">Reggae</option>
        <option value="Otro">Otro</option>
      </select>

      <label for="portada">Portada (opcional):</label>
      <input type="file" id="portada" name="portada" accept="image/*">
      <div id="singleCoverPreview" class="preview" style="margin-top:8px"></div>
    </div>

    {{-- -------- FORM ÁLBUM -------- --}}
    <div id="form-album" class="hidden">
      <label for="title">Título del álbum:</label>
      <input type="text" id="title" name="title">

      <label for="genre">Género (opcional):</label>
      <select id="genre" name="genre">
        <option value="">Selecciona un género</option>
        <option value="Pop">Pop</option>
        <option value="Rock">Rock</option>
        <option value="Reggaeton">Reggaeton</option>
        <option value="Rap">Rap</option>
        <option value="Trap">Trap</option>
        <option value="Electrónica">Electrónica</option>
        <option value="Indie">Indie</option>
        <option value="Jazz">Jazz</option>
        <option value="Salsa">Salsa</option>
        <option value="Cumbia">Cumbia</option>
        <option value="Regional">Regional</option>
        <option value="Metal">Metal</option>
        <option value="Bachata">Bachata</option>
        <option value="Reggae">Reggae</option>
        <option value="Otro">Otro</option>
      </select>

      <label for="release_date">Fecha de lanzamiento (opcional):</label>
      <input type="date" id="release_date" name="release_date">
    </div>

    <button type="submit" id="btn-submit">Subir</button>
  </div>
</form>

{{-- ===================== SCRIPT: PREVIEW LIB ===================== --}}
<script>
(function(){
  const exts = {
    image: ['jpg','jpeg','png','gif','webp','bmp','svg'],
    audio: ['mp3','wav','ogg','m4a','aac','flac'],
    video: ['mp4','webm','ogv','mov']
  };

  function kindFrom(val, forced){
    if (forced) return forced;
    if (typeof val === 'string') {
      const ext = (val.split('.').pop() || '').toLowerCase();
      if (exts.image.includes(ext)) return 'image';
      if (exts.audio.includes(ext)) return 'audio';
      if (exts.video.includes(ext)) return 'video';
      return 'audio';
    } else if (val && val.type) {
      if (val.type.startsWith('image')) return 'image';
      if (val.type.startsWith('audio')) return 'audio';
      if (val.type.startsWith('video')) return 'video';
    }
    return 'audio';
  }

  function renderPreview(el, src, opts={}){
    const type = kindFrom(src, opts.type);
    el.innerHTML = '';
    const url = (typeof src === 'string') ? src : URL.createObjectURL(src);

    let node;
    if (type === 'image'){
      node = new Image();
      node.src = url; node.alt = 'preview';
      Object.assign(node.style, { maxWidth:'520px', width:'100%', borderRadius:'10px' });
    } else if (type === 'video'){
      node = document.createElement('video');
      node.src = url; node.controls = true;
      if (opts.autoplay) node.autoplay = true;
      if (opts.muted) node.muted = true;
      Object.assign(node.style, { width:'100%', maxWidth:'520px', borderRadius:'10px' });
    } else {
      node = document.createElement('audio');
      node.src = url; node.controls = true; node.preload = 'none';
      if (opts.autoplay) node.autoplay = true;
      Object.assign(node.style, { width:'100%', maxWidth:'520px' });
    }
    el.appendChild(node);
  }

  // API pública
  window.renderPreview = renderPreview;
  window.initFilePreview = function(inputSel, previewSel, type='auto'){
    const i = document.querySelector(inputSel);
    const p = document.querySelector(previewSel);
    if (!i || !p) return;
    const empty = 'Ningún archivo seleccionado';
    p.textContent = empty;

    i.addEventListener('change', () => {
      const f = i.files && i.files[0];
      if (!f) { p.textContent = empty; return; }
      renderPreview(p, f, { type });
    });
  };

  // Auto (si usas data-preview-url en otras vistas)
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-preview-url]').forEach(el => {
      renderPreview(el, el.getAttribute('data-preview-url'), {
        type: el.getAttribute('data-preview-type'),
        autoplay: el.getAttribute('data-preview-autoplay') === 'true',
        muted: el.getAttribute('data-preview-muted') === 'true'
      });
    });
  });
})();
</script>

{{-- ===================== SCRIPT: LÓGICA DE PANTALLA ===================== --}}
<script>
  const home        = document.querySelector('.home');
  const form        = document.getElementById('uploadForm');
  const btnSubmit   = document.getElementById('btn-submit');

  // SINGLE
  const formSong    = document.getElementById('form-cancion');
  const dzSingle    = document.getElementById('dropzone-single');
  const mp3Input    = document.getElementById('mp3');
  const nombreInput = document.getElementById('nombre');
  const catCancion  = document.getElementById('categoria-cancion');

  // ÁLBUM
  const formAlbum   = document.getElementById('form-album');
  const albumWrap   = document.getElementById('album-dropzones');
  const dzCover     = document.getElementById('dz-cover');
  const inputCover  = document.getElementById('cover');
  const dzTracks    = document.getElementById('dz-tracks');
  const inputTracks = document.getElementById('tracks');
  const list        = document.getElementById('tracks-list');
  const titleAlbum  = document.getElementById('title');

  // Cambia entre "cancion" y "album"
  function mostrarRegistro(tipo) {
    home.style.display = 'none';
    form.style.display = 'flex';

    if (tipo === 'album') {
      form.action = "{{ route('albums.store') }}";
      btnSubmit.textContent = 'Crear Álbum y Subir Canciones';

      titleAlbum.setAttribute('required', 'required');
      inputTracks.setAttribute('required', 'required');

      nombreInput.removeAttribute('required');
      catCancion.removeAttribute('required');
      mp3Input.removeAttribute('required');

      formAlbum.classList.remove('hidden');
      albumWrap.style.display = 'flex';
      formSong.classList.add('hidden');
      dzSingle.style.display = 'none';
    } else {
      form.action = "{{ route('songs.store') }}";
      btnSubmit.textContent = 'Subir canción';

      nombreInput.setAttribute('required', 'required');
      catCancion.setAttribute('required', 'required');
      mp3Input.setAttribute('required', 'required');

      titleAlbum.removeAttribute('required');
      inputTracks.removeAttribute('required');

      formSong.classList.remove('hidden');
      dzSingle.style.display = 'block';
      formAlbum.classList.add('hidden');
      albumWrap.style.display = 'none';
      list.style.display = 'none';
      list.innerHTML = '';
    }
  }

  function volverASeleccion() {
    home.style.display = 'flex';
    form.style.display = 'none';
  }

  // ===== Drag & drop SINGLE
  dzSingle.addEventListener('click', () => mp3Input.click());
  dzSingle.addEventListener('dragover', (e) => { e.preventDefault(); dzSingle.classList.add('dragover'); });
  dzSingle.addEventListener('dragleave', () => dzSingle.classList.remove('dragover'));
  dzSingle.addEventListener('drop', (e) => {
    e.preventDefault(); dzSingle.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
      mp3Input.files = e.dataTransfer.files;
      initFilePreview('#mp3', '#mp3Preview', 'audio');
      mp3Input.dispatchEvent(new Event('change'));
    }
  });

  // ===== Drag & drop ÁLBUM (portada)
  dzCover?.addEventListener('click', () => inputCover.click());
  dzCover?.addEventListener('dragover', (e) => { e.preventDefault(); dzCover.classList.add('dragover'); });
  dzCover?.addEventListener('dragleave', () => dzCover.classList.remove('dragover'));
  dzCover?.addEventListener('drop', (e) => {
    e.preventDefault(); dzCover.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
      inputCover.files = e.dataTransfer.files;
      initFilePreview('#cover', '#coverPreview', 'image');
      inputCover.dispatchEvent(new Event('change'));
    }
  });

  // ===== Drag & drop ÁLBUM (tracks)
  dzTracks.addEventListener('click', () => inputTracks.click());
  dzTracks.addEventListener('dragover', (e) => { e.preventDefault(); dzTracks.classList.add('dragover'); });
  dzTracks.addEventListener('dragleave', () => dzTracks.classList.remove('dragover'));
  dzTracks.addEventListener('drop', (e) => {
    e.preventDefault(); dzTracks.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
      inputTracks.files = e.dataTransfer.files;
      renderTitles();
    }
  });
  inputTracks.addEventListener('change', renderTitles);

  // Genera inputs de títulos por cada archivo subido
  function renderTitles() {
    list.innerHTML = '';
    const files = Array.from(inputTracks.files || []);
    if (!files.length) { list.style.display = 'none'; return; }
    list.style.display = 'flex';
    files.forEach((f, i) => {
      const base = f.name.replace(/\.[^/.]+$/, '');
      const row = document.createElement('div');
      row.style.display = 'grid';
      row.style.gridTemplateColumns = '1fr 320px';
      row.style.gap = '10px';
      row.innerHTML = `
        <div style="opacity:.8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${f.name}</div>
        <input type="text" name="titles[]" placeholder="Título para la pista #${i+1}" value="${base}">
      `;
      list.appendChild(row);
    });
  }

  // ===== Inicializar previews al cargar
  document.addEventListener('DOMContentLoaded', () => {
    initFilePreview('#mp3',   '#mp3Preview',   'audio');
    initFilePreview('#cover', '#coverPreview', 'image');
    initFilePreview('#portada', '#singleCoverPreview', 'image');
  });
</script>

</body>
</html>
