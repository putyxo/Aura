<?php
// index.php
// Carga configuración persistente si existe
$configFile = __DIR__ . '/config.json';
$config = [];
if (file_exists($configFile)) {
    $cfgRaw = file_get_contents($configFile);
    $cfg = json_decode($cfgRaw, true);
    if (is_array($cfg)) $config = $cfg;
}

// Valores por defecto (se usará $config si existe)
$artistName = $config['artistName'] ?? 'The Weeknd';
$monthlyListeners = $config['monthlyListeners'] ?? '112,845,675';
$profileImage = $config['profileImage'] ?? 'assets/cover.jpg';

$popularTracks = $config['popularTracks'] ?? [
    ['title' => 'Popular', 'plays' => '1,171,123,830', 'duration' => '3:51'],
    ['title' => 'Golden Gun', 'plays' => '13,570,973', 'duration' => '3:36'],
    ['title' => 'Die For You', 'plays' => '104,982,947', 'duration' => '3:16'],
    ['title' => 'Como capo', 'plays' => '13,982,401', 'duration' => '2:54']
];

$albums = $config['albums'] ?? [
    ['title' => 'Starboy', 'image' => 'assets/album1.jpg'],
    ['title' => 'The Idol OST', 'image' => 'assets/album2.jpg'],
    ['title' => 'Trilogy', 'image' => 'assets/album3.jpg'],
    ['title' => 'After Hours', 'image' => 'assets/album4.jpg']
];

// Helper para placeholder si falta imagen
function image_or_placeholder($path, $w = 400, $h = 400) {
    if (file_exists($path)) return $path;
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='$w' height='$h'><rect width='100%' height='100%' fill='%23333' rx='20'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-size='24' fill='%23eee'>No image</text></svg>";
    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title class="traducible">Configuración de perfil — <?=htmlspecialchars($artistName)?> · Música</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
 @vite('resources/css/ed_perfil.css')

</head>
<body>
        @yield('content')
@include('components.traductor')
  <header class="topbar">
    <div class="top-left">
<a href="menu" class="icon-btn" aria-label="Atrás">◀</a>
<a href="/ruta-siguiente" class="icon-btn" aria-label="Adelante">▶</a>

    </div>
    <div class="top-center">
      <input class="search" placeholder="Buscar artista, lista, canción" aria-label="Buscar">
    </div>
    <div class="top-right">
      <button class="nav-pill">Home</button>
      <button class="nav-pill traducible">Mi biblioteca</button>
      <button class="avatar-btn" aria-label="Perfil">PT</button>
    </div>
  </header>

  <div class="stage">
    <div class="profile-card">
      <div class="header">
        <div class="cover">
          <img id="coverImg" src="<?=image_or_placeholder($profileImage)?>" alt="cover">
        </div>
        <div class="meta">
          <div class="row-top">
            <h1 id="artistName"><?=htmlspecialchars($artistName)?></h1>
            <div class="badges">
              <span class="badge">Verified</span>
              <span class="badge alt" id="genreBadge"><?=htmlspecialchars($config['genre'] ?? 'R&B')?></span>
            </div>
          </div>

          <div class="sub">
            <div class="pill traducible" id="listeners"><?=htmlspecialchars($monthlyListeners)?> oyentes mensuales</div>
            <div style="flex:1"></div>
          </div>

          <div class="controls">
            <button class="edit-btn traducible" id="editBtn " aria-label="Editar perfil">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" fill="#fff"/></svg>
              Editar perfil
            </button>

            <button class="follow traducible" id="followBtn"><?= (isset($config['following']) && $config['following']) ? 'Siguiendo' : 'Seguir' ?></button>

            <div style="margin-left:auto;color:var(--muted);font-weight:700" class="traducible"  >Configuración · Perfil</div>
          </div>

          <div class="bio traducible" id="bio"><?=htmlspecialchars($config['bio'] ?? 'Artista · Unificador · Realizador · Audiencia. — Actualiza tu biografía en "Editar perfil" para darle más personalidad a tu página.')?></div>

        </div>
      </div>

      <div class="popular">
        <h3>Últimos likes</h3>
        <?php foreach($popularTracks as $t): ?>
        <div class="track" data-title="<?=htmlspecialchars($t['title'])?>">
          <div class="thumb">S</div>
          <div class="info">
            <div class="title"><?=htmlspecialchars($t['title'])?></div>
            <div class="meta traducible"><?=htmlspecialchars($t['plays'])?> reproducciones</div>
          </div>
          <div class="duration"><?=htmlspecialchars($t['duration'])?></div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <aside class="albums-panel">
      <h4>Playlist</h4>
      <div class="albums-grid">
        <?php foreach($albums as $a): ?>
          <div class="album" tabindex="0">
            <img src="<?=image_or_placeholder($a['image'],600,600)?>" alt="<?=htmlspecialchars($a['title'])?>">
            <div class="a-title"><?=htmlspecialchars($a['title'])?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="young-tips" >
        <strong class="traducible">Consejo:</strong> <p>Personaliza tu portada y añade enlaces a redes para conectar con fans.</p>
      </div>
    </aside>

  </div>

  <!-- Modal / Panel moderno para editar perfil -->
  <div class="modal" id="editModal" aria-hidden="true">
    <div class="modal-content">
      <h3>Editar perfil</h3>

      <!-- Form: se envía con JS a save_profile.php -->
      <form id="profileForm" method="post" enctype="multipart/form-data" action="../views/save_profile.php">
        <!-- Sección de Información Básica -->
        <div class="form-section" class="traducible">
          <h4>Información Básica</h4>
          <div class="form-group grid-2">
            <label for="inputName" >
              Nombre del artista
              <input id="inputName" name="artistName" type="text" placeholder="Nombre del artista" value="<?=htmlspecialchars($artistName)?>">
              <span class="error-message" id="errorName"></span>
            </label>
            <label for="inputListeners" >
              Oyentes mensuales
              <input id="inputListeners" name="monthlyListeners" type="text" placeholder="Ej: 1,234,567" value="<?=htmlspecialchars($monthlyListeners)?>">
              <span class="error-message" id="errorListeners"></span>
            </label>
          </div>

          <label for="inputBio" class="form-group">
            Biografía
            <textarea id="inputBio" name="bio" rows="4" placeholder="Escribe una biografía corta..."><?=htmlspecialchars($config['bio'] ?? '')?></textarea>
            <span class="error-message" id="errorBio"></span>
          </label>
        </div>

        <!-- Sección de Imagen de Portada -->
        <div class="form-section">
          <h4>Imagen de Portada</h4>
          <div class="form-group image-upload-group">
            <div class="image-preview-container">
              <img id="coverPreview" src="<?=image_or_placeholder($profileImage,240,240)?>" alt="preview" class="cover-preview-img">
            </div>
            <div class="file-input-container">
              <input id="coverFile" name="cover" type="file" accept="image/*">
              <p class="file-info traducible">Selecciona una imagen (jpg/png/webp). Max 3MB.</p>
              <span class="error-message" id="errorCover"></span>
            </div>
          </div>
        </div>

        <!-- Sección de Detalles Adicionales -->
        <div class="form-section traducible">
          <h4>Detalles Adicionales</h4>
          <div class="form-group grid-2">
            <label for="inputGenre">
              Género
              <select id="inputGenre" name="genre">
                <?php
                  $genres = ['R&B','Pop','Hip-Hop','Indie','Electronica','Reggaetón'];
                  $currentGenre = $config['genre'] ?? 'R&B';
                  foreach($genres as $g) {
                    $sel = ($g === $currentGenre) ? 'selected' : '';
                    echo "<option value=\"".htmlspecialchars($g)."\" $sel>".htmlspecialchars($g)."</option>";
                  }
                ?>
              </select>
              <span class="error-message" id="errorGenre"></span>
            </label>

            <label for="inputSocial" class="traducible">
              Red social (ej. Instagram)
              <input id="inputSocial" name="social" type="text" placeholder="https://instagram.com/usuario" value="<?=htmlspecialchars($config['social'] ?? '')?>">
              <span class="error-message" id="errorSocial"></span>
            </label>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" id="saveEdit">Guardar</button>
          <button type="button" id="cancelEdit" class="muted">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/ed-perfil.js"></script>
</body>
</html>
