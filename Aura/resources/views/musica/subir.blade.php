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

/* ===== Helpers Drive ===== */
if (!function_exists('drive_extract_id')) {
    function drive_extract_id($url) {
        if (!$url) return null;
        if (preg_match('#/file/d/([^/]+)/#i', $url, $m)) return $m[1];
        if (preg_match('#[?&]id=([^&]+)#i', $url, $m)) return $m[1];
        return null;
    }
}
if (!function_exists('drive_image_view')) {
    function drive_image_view($url) {
        $id = drive_extract_id($url);
        return $id ? "https://drive.google.com/uc?export=view&id={$id}" : null;
    }
}

/* ===== Placeholder inline ===== */
$placeholder = 'data:image/svg+xml;utf8,' . rawurlencode(
  '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="140">
     <rect width="100%" height="100%" rx="12" ry="12" fill="#1f2937"/>
     <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle"
           font-size="14" fill="#9ca3af">Sin portada</text>
   </svg>'
);

/* ===== Últimas portadas de prueba ===== */
$coverSongUrl = null;
if ($song = DB::table('songs')->whereNotNull('cover_path')->latest('id')->first()) {
    $raw = $song->cover_path;
    $coverSongUrl = Str::startsWith($raw, ['http://','https://'])
        ? (Str::contains($raw, 'drive.google') ? (drive_image_view($raw) ?: $raw) : $raw)
        : Storage::url($raw);
}

$coverAlbumUrl = null;
if (DB::getSchemaBuilder()->hasTable('albums')) {
    if ($alb = DB::table('albums')->whereNotNull('cover_path')->latest('id')->first()) {
        $raw = $alb->cover_path;
        $coverAlbumUrl = Str::startsWith($raw, ['http://','https://'])
            ? (Str::contains($raw, 'drive.google') ? (drive_image_view($raw) ?: $raw) : $raw)
            : Storage::url($raw);
    }
}
@endphp

{{-- ================= SELECCIÓN DE TIPO ================= --}}
<section class="home active">
  <h2>¿Qué deseas subir?</h2>
  <div class="container">
    <div class="card" onclick="mostrarRegistro('cancion')">
      <img src="{{ $coverSongUrl ?? $placeholder }}" alt="Canción"
           class="card-img" onerror="this.src='{{ $placeholder }}'">
      <p>Canción individual</p>
    </div>
    <div class="card" onclick="mostrarRegistro('album')">
      <img src="{{ $coverAlbumUrl ?? $placeholder }}" alt="Álbum"
           class="card-img" onerror="this.src='{{ $placeholder }}'">
      <p>Álbum</p>
    </div>
  </div>
</section>

{{-- ================= FORMULARIO (inicialmente oculto) ================= --}}
<form method="post" enctype="multipart/form-data" id="uploadForm"
      action="{{ route('songs.store') }}"
      class="upload-container hidden">
  @csrf

  <!-- 🔙 Volver -->
  <button type="button" onclick="volverASeleccion()" class="btn-volver">
    ← Volver a elegir
  </button>

  <!-- 🎵 Canción -->
  <section id="form-cancion">
    <h3>Canción individual</h3>

    <!-- Dropzone -->
    <div class="dropzone" id="dropzone-single">
      <span class="dropzone-icon">🎵</span>
      <span class="dropzone-text">Haz click o arrastra tu archivo MP3 aquí</span>
      <input type="file" id="mp3" name="mp3" accept=".mp3,audio/mpeg">
    </div>
    <div id="mp3Preview" class="preview"></div>

    <!-- Campos -->
    <div class="upload-form">
      <div class="form-group">
        <label for="nombre">Nombre de la canción</label>
        <input type="text" id="nombre" name="nombre" required>
      </div>

      <div class="form-group">
        <label for="categoria-cancion">Género</label>
        <select id="categoria-cancion" name="categoria" required>
          <option value="">Selecciona un género</option>
          <option>Pop</option><option>Rock</option><option>Reggaeton</option>
          <option>Rap</option><option>Trap</option><option>Electrónica</option>
          <option>Indie</option><option>Jazz</option><option>Salsa</option>
          <option>Cumbia</option><option>Regional</option><option>Metal</option>
          <option>Bachata</option><option>Reggae</option><option>Otro</option>
        </select>
      </div>

      <div class="form-group">
        <label for="portada">Portada (opcional)</label>
        <input type="file" id="portada" name="portada" accept="image/*">
        <div id="singleCoverPreview" class="preview"></div>
      </div>
    </div>
  </section>

  <!-- 📀 Álbum -->
  <section id="form-album" class="hidden">
    <h3>Álbum completo</h3>

    <!-- Dropzones -->
    <div class="dropzone" id="dz-cover">
      <span class="dropzone-icon">🖼️</span>
      <span class="dropzone-text">Portada (JPG/PNG/WEBP)</span>
      <input type="file" id="cover" name="cover" accept="image/*">
    </div>
    <div id="coverPreview" class="preview"></div>

    <div class="dropzone" id="dz-tracks">
      <span class="dropzone-icon">🎵</span>
      <span class="dropzone-text">Arrastra varios MP3 aquí</span>
      <input type="file" id="tracks" name="tracks[]" accept=".mp3,audio/mpeg" multiple>
    </div>
    <div id="tracks-list" class="tracks-list"></div>

    <!-- Campos -->
    <div class="upload-form">
      <div class="form-group">
        <label for="title">Título del álbum</label>
        <input type="text" id="title" name="title" required>
      </div>

      <div class="form-group">
        <label for="genre">Género (opcional)</label>
        <select id="genre" name="genre">
          <option value="">Selecciona un género</option>
          <option>Pop</option><option>Rock</option><option>Reggaeton</option>
          <option>Rap</option><option>Trap</option><option>Electrónica</option>
          <option>Indie</option><option>Jazz</option><option>Salsa</option>
          <option>Cumbia</option><option>Regional</option><option>Metal</option>
          <option>Bachata</option><option>Reggae</option><option>Otro</option>
        </select>
      </div>

      <div class="form-group">
        <label for="release_date">Fecha de lanzamiento</label>
        <input type="date" id="release_date" name="release_date">
      </div>
    </div>
  </section>

  <!-- ✅ Botón final -->
  <button type="submit" id="btn-submit">Subir</button>
</form>


{{-- ===================== SCRIPTS ===================== --}}
@vite('resources/js/subir.js')
<Script>document.addEventListener('DOMContentLoaded', () => {
  const home = document.querySelector('.home');
  const form = document.getElementById('uploadForm');

  window.mostrarRegistro = function(tipo) {
    home.style.display = 'none';
    form.style.display = 'flex'; // o block según tu CSS
    form.classList.remove('hidden');

    // Cambiar acción y textos según tipo
    const btnSubmit = document.getElementById('btn-submit');
    if (tipo === 'album') {
      form.action = "/albums"; // o route('albums.store')
      btnSubmit.textContent = "Crear Álbum y Subir Canciones";
      document.getElementById('form-album').classList.remove('hidden');
      document.getElementById('album-dropzones').style.display = 'flex';
      document.getElementById('form-cancion').classList.add('hidden');
      document.getElementById('dropzone-single').style.display = 'none';
    } else {
      form.action = "/songs"; // o route('songs.store')
      btnSubmit.textContent = "Subir canción";
      document.getElementById('form-cancion').classList.remove('hidden');
      document.getElementById('dropzone-single').style.display = 'block';
      document.getElementById('form-album').classList.add('hidden');
      document.getElementById('album-dropzones').style.display = 'none';
    }
  };

  window.volverASeleccion = function() {
    home.style.display = 'flex';
    form.style.display = 'none';
  };
});
</Script>
</body>
</html>
