<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Configuración de perfil — {{ Auth::user()->nombre_artistico }} · Música</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
  @vite('resources/css/ed_perfil.css')
</head>
<body>

  <!-- ===== TOP BAR ===== -->
  <header class="topbar">
    <div class="top-left">
      <a href="{{ url('menu') }}" class="icon-btn" aria-label="Atrás">◀</a>
      <a href="#" class="icon-btn" aria-label="Adelante">▶</a>
    </div>
    <div class="top-center">
      <input class="search" placeholder="Buscar artista, lista, canción" aria-label="Buscar">
    </div>
    <div class="top-right">
      <button class="nav-pill">Home</button>
      <button class="nav-pill">Mi biblioteca</button>
      <button class="avatar-btn" aria-label="Perfil">
        {{ substr(Auth::user()->nombre,0,2) }}
      </button>
    </div>
  </header>

  <!-- ===== PERFIL ===== -->
  <div class="stage">
    <div class="profile-card">
      <div class="header">
        <div class="cover">
          <img id="coverImg" src="{{ asset(Auth::user()->imagen_portada ?? 'img/default.png') }}" alt="cover">
        </div>
        <div class="meta">
          <div class="row-top">
            <h1 id="artistName">{{ Auth::user()->nombre_artistico }}</h1>
            <div class="badges">
              @if(Auth::user()->verificado)
                <span class="badge">Verified</span>
              @endif
              <span class="badge alt" id="genreBadge">{{ Auth::user()->generos }}</span>
            </div>
          </div>
          <div class="sub">
            <div class="pill" id="listeners">
              {{ Auth::user()->oyentes_mensuales ?? 0 }} oyentes mensuales
            </div>
          </div>
          <div class="controls">
            <button class="edit-btn" id="editBtn">Editar perfil</button>
            <div style="margin-left:auto;color:var(--muted);font-weight:700">Configuración · Perfil</div>
          </div>
          <div class="bio" id="bio">{{ Auth::user()->biografia }}</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== MODAL EDITAR PERFIL ===== -->
  <div class="modal" id="editModal" aria-hidden="true">
    <div class="modal-content">
      <h3>Editar perfil</h3>
      <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
        @csrf

        <!-- Información básica -->
        <div class="form-section">
          <h4>Información Básica</h4>
          <div class="form-group grid-2">
            <label>Nombre del artista
              <input name="artistName" type="text" value="{{ Auth::user()->nombre_artistico }}">
            </label>
            <label>Oyentes mensuales
              <input name="monthlyListeners" type="text" value="{{ Auth::user()->oyentes_mensuales }}">
            </label>
          </div>
          <label>Biografía
            <textarea name="bio" rows="4">{{ Auth::user()->biografia }}</textarea>
          </label>
        </div>

        <!-- Imagen de portada -->
        <div class="form-section">
          <h4>Imagen de Portada</h4>
          <input name="cover" type="file" accept="image/*">
        </div>

        <!-- Detalles adicionales -->
        <div class="form-section">
          <h4>Detalles Adicionales</h4>
          <div class="form-group grid-2">
            <label>Género
              <select name="genre">
                @foreach(['R&B','Pop','HipHop','Indie','Electronica','Reggaetón'] as $g)
                  <option value="{{ $g }}" {{ Auth::user()->generos===$g ? 'selected' : '' }}>
                    {{ $g }}
                  </option>
                @endforeach
              </select>
            </label>
            <label>Red social
              <input name="social" type="text" value="{{ Auth::user()->social ?? '' }}">
            </label>
          </div>
        </div>

        <!-- Botones -->
        <div class="modal-actions">
          <button type="submit" id="saveEdit">Guardar</button>
          <button type="button" id="cancelEdit" class="muted">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  @vite('resources/js/ed-perfil.js')
</body>
</html>
