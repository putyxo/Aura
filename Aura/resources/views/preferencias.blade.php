<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Preferencias — Aura</title>

  <!-- Iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Vite -->
  @vite(['resources/css/preferencias.css', 'resources/js/preferencias.js'])
</head>
<body>

  {{-- ====== COMPONENTES GLOBALES ====== --}}
  @include('components.sidebar')
  @include('components.traductor')
  @include('components.header')
    @include('components.fondo')

  {{-- Partículas de fondo --}}
  <div class="floating-particles" id="particles"></div>

  {{-- ====== CONTENIDO PRINCIPAL ====== --}}
  <main class="main-content">
    <div class="container">

      {{-- ====== HERO NUEVO ====== --}}
      <section class="pref-hero" aria-label="Preferencias">
        <div class="pref-hero__bg"></div>

        <div class="pref-hero__content">
          <div class="pref-hero__icon">
            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
          </div>
          <div>
            <h1>Preferencias</h1>
            <p class="pref-hero__subtitle">Personaliza tu experiencia musical con artistas emergentes</p>
          </div>
        </div>

        <div class="pref-hero__chips">
          <span class="pref-chip"><i class="fa-solid fa-globe"></i> Idioma: ES</span>
          <span class="pref-chip"><i class="fa-solid fa-bolt"></i> Modo inmersivo</span>
          <span class="pref-chip"><i class="fa-solid fa-wave-square"></i> EQ: Balanceado</span>
        </div>
      </section>
      {{-- ====== /HERO NUEVO ====== --}}

      {{-- ===== CUENTA ===== --}}
      <div class="preferences-section">
        <h2 class="section-title"><i class="fas fa-user"></i> Cuenta</h2>

        <div class="preference-item">
          <div>
            <div class="preference-label">Editar métodos para iniciar sesión</div>
            <div class="preference-description">Administra email, contraseña, autenticación de dos factores y métodos de acceso</div>
          </div>
          <button class="account-edit-btn" onclick="redirectToAccountSettings(event)">
            <span class="btn-icon"><i class="fas fa-cog"></i></span>
            <span class="btn-text">Configurar cuenta</span>
            <span class="btn-arrow">→</span>
          </button>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Idioma de la aplicación</div>
            <div class="preference-description">Cambia entre inglés y español</div>
          </div>
          <div class="language-toggle" onclick="toggleLanguage(this)">
            <div class="language-option active" data-lang="es">ES</div>
            <div class="language-option" data-lang="en">EN</div>
            <div class="language-slider"></div>
          </div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Nivel de zoom</div>
            <div class="preference-description">Ajusta el tamaño de la interfaz</div>
          </div>
          <div class="zoom-container">
            <div class="zoom-preview" id="zoom-preview">
              <div class="zoom-figure" id="zoom-figure"><i class="fas fa-music"></i></div>
            </div>
            <input type="range" min="50" max="150" value="100" class="slider" id="zoom" oninput="updateZoom(this.value)">
            <span class="volume-display" id="zoom-value">100%</span>
          </div>
        </div>
      </div>

      {{-- ===== REPRODUCCIÓN ===== --}}
      <div class="preferences-section">
        <h2 class="section-title"><i class="fas fa-play-circle"></i> Reproducción</h2>

        <div class="preference-item">
          <div>
            <div class="preference-label">Reproducción automática</div>
            <div class="preference-description">Continúa reproduciendo música similar cuando termine una canción</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Nivel de exploración</div>
            <div class="preference-description">Qué tan aventurero quieres ser con nuevos géneros</div>
          </div>
          <div style="display:flex; align-items:center;">
            <input type="range" min="1" max="10" value="7" class="slider" id="exploration" oninput="updateValue('exploration-value', getExplorationLevel(this.value))">
            <span class="volume-display" id="exploration-value">Alto</span>
          </div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Modo aleatorio inteligente</div>
            <div class="preference-description">Mezcla canciones considerando tu estado de ánimo</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Crossfade</div>
            <div class="preference-description">Transición suave entre canciones</div>
          </div>
          <div style="display:flex; align-items:center;">
            <input type="range" min="0" max="12" value="3" class="slider" id="crossfade" oninput="updateValue('crossfade-value', this.value + 's')">
            <span class="volume-display" id="crossfade-value">3s</span>
          </div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Calidad de audio</div>
            <div class="preference-description">Mayor calidad consume más datos</div>
          </div>
          <div class="dropdown">
            <select class="dropdown-select">
              <option value="low">Baja (96 kbps)</option>
              <option value="normal" selected>Normal (160 kbps)</option>
              <option value="high">Alta (320 kbps)</option>
              <option value="lossless">Sin pérdida</option>
            </select>
          </div>
        </div>
      </div>

      {{-- ===== EXPERIENCIA ===== --}}
      <div class="preferences-section">
        <h2 class="section-title"><i class="fas fa-headphones"></i> Experiencia</h2>

        <div class="preference-item">
          <div>
            <div class="preference-label">Modo inmersivo</div>
            <div class="preference-description">Efectos visuales durante la reproducción</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Letras en tiempo real</div>
            <div class="preference-description">Muestra las letras sincronizadas con la música</div>
          </div>
          <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Tema de la aplicación</div>
            <div class="preference-description">Personaliza la apariencia visual</div>
          </div>
          <div class="dropdown">
            <select class="dropdown-select">
              <option value="cosmic" selected>Cósmico (actual)</option>
              <option value="neon">Neón vibrante</option>
              <option value="minimal">Minimalista</option>
              <option value="retro">Retro synthwave</option>
            </select>
          </div>
        </div>
      </div>

      {{-- ===== ECUALIZADOR ===== --}}
      <div class="preferences-section">
        <h2 class="section-title"><i class="fas fa-sliders-h"></i> Ecualizador</h2>

        <div class="preference-item full-width">
          <div>
            <div class="preference-label">Valores predefinidos de frecuencia (Hz)</div>
            <div class="preference-description">Ajusta las bandas de frecuencia para tu experiencia auditiva perfecta</div>
          </div>
        </div>

        <div class="equalizer-container">
          <div class="eq-band" data-freq="60">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="0" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-60">0dB</div>
            </div>
            <div class="eq-label">60Hz</div>
          </div>

          <div class="eq-band" data-freq="170">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="2" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-170">+2dB</div>
            </div>
            <div class="eq-label">170Hz</div>
          </div>

          <div class="eq-band" data-freq="310">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="-1" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-310">-1dB</div>
            </div>
            <div class="eq-label">310Hz</div>
          </div>

          <div class="eq-band" data-freq="600">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="1" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-600">+1dB</div>
            </div>
            <div class="eq-label">600Hz</div>
          </div>

          <div class="eq-band" data-freq="1k">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="3" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-1k">+3dB</div>
            </div>
            <div class="eq-label">1kHz</div>
          </div>

          <div class="eq-band" data-freq="3k">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="2" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-3k">+2dB</div>
            </div>
            <div class="eq-label">3kHz</div>
          </div>

          <div class="eq-band" data-freq="6k">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="1" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-6k">+1dB</div>
            </div>
            <div class="eq-label">6kHz</div>
          </div>

          <div class="eq-band" data-freq="12k">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="0" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-12k">0dB</div>
            </div>
            <div class="eq-label">12kHz</div>
          </div>

          <div class="eq-band" data-freq="14k">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="-2" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-14k">-2dB</div>
            </div>
            <div class="eq-label">14kHz</div>
          </div>

          <div class="eq-band" data-freq="16k">
            <div class="eq-slider-container">
              <input type="range" min="-12" max="12" value="1" class="eq-slider" orient="vertical" oninput="updateEQ(this)">
              <div class="eq-value" id="eq-16k">+1dB</div>
            </div>
            <div class="eq-label">16kHz</div>
          </div>
        </div>

        <div class="eq-presets">
          <button class="preset-btn active" onclick="applyPreset('balanced')">Balanceado</button>
          <button class="preset-btn" onclick="applyPreset('bass')">Graves</button>
          <button class="preset-btn" onclick="applyPreset('vocal')">Vocal</button>
          <button class="preset-btn" onclick="applyPreset('electronic')">Electrónica</button>
          <button class="preset-btn" onclick="applyPreset('rock')">Rock</button>
        </div>
      </div>

      {{-- ===== DESCUBRIMIENTO ===== --}}
      <div class="preferences-section">
        <h2 class="section-title"><i class="fas fa-rocket"></i> Descubrimiento</h2>

        <div class="preference-item">
          <div>
            <div class="preference-label">Artistas emergentes destacados</div>
            <div class="preference-description">Recibe notificaciones de nuevos talentos</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Mix semanal personalizado</div>
            <div class="preference-description">Playlist automática basada en tus gustos</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Radar de géneros nuevos</div>
            <div class="preference-description">Explora géneros emergentes y fusiones</div>
          </div>
          <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Colaboraciones recomendadas</div>
            <div class="preference-description">Descubre colaboraciones entre artistas emergentes</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>
      </div>

      {{-- ===== SOCIAL ===== --}}
      <div class="preferences-section">
        <h2 class="section-title"><i class="fas fa-mobile-alt"></i> Social</h2>

        <div class="preference-item">
          <div>
            <div class="preference-label">Perfil público</div>
            <div class="preference-description">Permite que otros usuarios encuentren tu perfil</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Compartir actividad</div>
            <div class="preference-description">Permite que otros vean lo que escuchas</div>
          </div>
          <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Recomendaciones de amigos</div>
            <div class="preference-description">Recibe sugerencias basadas en amigos</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Notificaciones sociales</div>
            <div class="preference-description">Avisos cuando amigos siguen artistas o comparten música</div>
          </div>
          <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
        </div>

        <div class="preference-item">
          <div>
            <div class="preference-label">Sesiones colaborativas</div>
            <div class="preference-description">Permite crear playlists en tiempo real con amigos</div>
          </div>
          <div class="toggle-switch" onclick="toggleSwitch(this)"></div>
        </div>
      </div>

      <button class="save-button" onclick="savePreferences()">
        <i class="fas fa-save"></i> Guardar Todas las Preferencias
      </button>
    </div>
  </main>

  {{-- ====== FOOTER / PLAYER DERECHO ====== --}}
  @include('components.footer')
</body>
</html>
