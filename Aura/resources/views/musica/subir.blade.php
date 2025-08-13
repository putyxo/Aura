@vite('resources/css/subir.css')

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Subir Música</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

  @if(session('ok'))
  <div class="alert alert-success">{{ session('ok') }}</div>

  {{-- Portada del single o álbum --}}
  @if(session('cover_url'))
    <img src="{{ session('cover_url') }}" alt="Portada" style="max-width:220px;border-radius:10px;margin-top:8px">
  @endif

  {{-- Audio del single --}}
  @if(session('audio_url'))
    <audio controls preload="none" style="width:100%;max-width:480px;margin-top:8px">
      <source src="{{ session('audio_url') }}" type="audio/mpeg">
      Tu navegador no soporta audio HTML5.
    </audio>
  @endif

  {{-- Lista de canciones del álbum recién creado --}}
  @if(session('album_id'))
    @php
      $album = \App\Models\Album::with('songs')->find(session('album_id'));
    @endphp

    @if($album && $album->songs->count())
      <h3 style="margin-top:12px">Pistas del álbum: {{ $album->title }}</h3>
      <ul style="display:flex;flex-direction:column;gap:10px;padding:0;list-style:none">
        @foreach($album->songs as $song)
          <li>
            <div style="font-weight:600">{{ $song->title }}</div>
            <audio controls preload="none" style="width:100%;max-width:520px">
              <source src="{{ $song->audio_path }}" type="audio/mpeg">
            </audio>
          </li>
        @endforeach
      </ul>
    @endif
  @endif
@endif
  @endif

  <section class="home active">
    <h2>¿Qué deseas subir?</h2>
    <div class="container">
      <div class="card" onclick="mostrarRegistro('cancion')">
        <img src="https://image.shutterstock.com/image-photo/music-note-on-white-background-260nw-1839737693.jpg" alt="Contenido" />
        <p>Canción individual</p>
      </div>
      <div class="card" onclick="mostrarRegistro('album')">
        <img src="https://image.shutterstock.com/image-photo/music-note-on-white-background-260nw-1839737693.jpg" alt="Contenido" />
        <p>Álbum</p>
      </div>
    </div>
  </section>

  {{-- Un solo formulario que cambia la action según selección --}}
  <form method="post" enctype="multipart/form-data" id="uploadForm" class="upload-container" action="{{ route('songs.store') }}">
    @csrf

    <button type="button" onclick="volverASeleccion()" class="btn-volver">Volver a elegir</button>

    {{-- =================== DROPZONE SINGLE =================== --}}
    <div class="dropzone" id="dropzone-single">
      <span class="dropzone-icon">🎵</span>
      <span class="dropzone-text">Haz click o arrastra tu archivo MP3 aquí</span>
      <input type="file" id="mp3" name="mp3" accept=".mp3">
    </div>

    {{-- =================== DROPZONES ÁLBUM =================== --}}
    <div id="album-dropzones" class="hidden" style="display:none; gap:12px; flex-direction:column">
      {{-- Portada del álbum --}}
      <div class="dropzone" id="dz-cover">
        <span class="dropzone-icon">🖼️</span>
        <span class="dropzone-text">Haz click o arrastra la portada (JPG/PNG/WEBP) – máx 10MB</span>
        <input type="file" id="cover" name="cover" accept="image/*">
      </div>
      {{-- Múltiples canciones --}}
      <div class="dropzone" id="dz-tracks">
        <span class="dropzone-icon">🎵</span>
        <span class="dropzone-text">Haz click o arrastra varios MP3 aquí</span>
        <input type="file" id="tracks" name="tracks[]" accept=".mp3,audio/mpeg" multiple>
      </div>
      {{-- Lista editable de títulos por pista --}}
      <div id="tracks-list" style="display:none;gap:10px;flex-direction:column"></div>
    </div>

    <div class="upload-form">
      {{-- =================== FORM SINGLE =================== --}}
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

        {{-- Portada opcional para el single --}}
        <label for="portada">Portada (opcional):</label>
        <input type="file" id="portada" name="portada" accept="image/*">
      </div>

      {{-- =================== FORM ÁLBUM =================== --}}
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
        // Action y textos
        form.action = "{{ route('albums.store') }}";
        btnSubmit.textContent = 'Crear Álbum y Subir Canciones';

        // Requeridos para álbum
        titleAlbum.setAttribute('required', 'required');
        inputTracks.setAttribute('required', 'required');

        // Quitar requeridos de single
        nombreInput.removeAttribute('required');
        catCancion.removeAttribute('required');
        mp3Input.removeAttribute('required');

        // Mostrar álbum / ocultar single
        formAlbum.classList.remove('hidden');
        albumWrap.style.display = 'flex';
        formSong.classList.add('hidden');
        dzSingle.style.display = 'none';
      } else {
        // Action y textos
        form.action = "{{ route('songs.store') }}";
        btnSubmit.textContent = 'Subir canción';

        // Requeridos para single
        nombreInput.setAttribute('required', 'required');
        catCancion.setAttribute('required', 'required');
        mp3Input.setAttribute('required', 'required');

        // Quitar requeridos de álbum
        titleAlbum.removeAttribute('required');
        inputTracks.removeAttribute('required');

        // Mostrar single / ocultar álbum
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
      if (e.dataTransfer.files.length) mp3Input.files = e.dataTransfer.files;
    });

    // ===== Drag & drop ÁLBUM (portada)
    dzCover?.addEventListener('click', () => inputCover.click());
    dzCover?.addEventListener('dragover', (e) => { e.preventDefault(); dzCover.classList.add('dragover'); });
    dzCover?.addEventListener('dragleave', () => dzCover.classList.remove('dragover'));
    dzCover?.addEventListener('drop', (e) => {
      e.preventDefault(); dzCover.classList.remove('dragover');
      if (e.dataTransfer.files.length) inputCover.files = e.dataTransfer.files;
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
  </script>
</body>
</html>
