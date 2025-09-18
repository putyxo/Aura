<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Preferencias — Aura</title>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Vite -->
    @vite(['resources/css/preferencias.css', 'resources/js/preferencias.js'])
</head>

<style>
#app {
    overflow: hidden;
    /* bloquea el scroll dentro de ese contenedor */
    height: 100vh;
    /* asegura que ocupe toda la pantalla */
}

:root {
    --panel: #121a24;
    --ink: #d8eeff;
    --muted: #9ec1df;
    --line: #1c2a3b;
}

* {
    box-sizing: border-box
}

body {
    margin: 0;
    min-height: 100svh;
    display: grid;
    place-items: center;
    background: linear-gradient(180deg, #0b0f14, #0e141c);
    color: var(--ink);
    font-family: system-ui, Segoe UI, Inter, Arial, sans-serif;
}

.card {
    width: min(680px, 95vw);
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 18px;
    box-shadow: 0 22px 48px rgba(0, 0, 0, .35);
}

.head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap
}

h1 {
    margin: 0;
    font-size: 18px;
    letter-spacing: .3px
}

.small {
    font-size: 12px;
    color: var(--muted)
}

.row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap
}

button,
input[type="range"] {
    background: #0f1622;
    color: var(--ink);
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 10px 12px;
    font-weight: 600;
    cursor: pointer;
}

button:hover {
    border-color: #2c425c
}

.eq-panel {
    display: grid;
    gap: 12px;
    margin-top: 12px
}

.bands {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 12px;
    align-items: end;
}

.band {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.band label {
    font-size: 12px;
    color: var(--muted)
}

.band input[type="range"] {
    writing-mode: bt-lr;
    -webkit-appearance: slider-vertical;
    width: 18px;
    height: 180px;
    background: transparent;
    padding: 0;
}

.num {
    font: 600 12px/1 system-ui;
    color: #cfe9ff
}

.preamp {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    border-top: 1px dashed var(--line);
    padding-top: 8px;
}
</style>

<body>

    {{-- ====== COMPONENTES GLOBALES ====== --}}
    @include('components.sidebar') {{-- Sidebar fijo a la izquierda --}}
    @include('components.header')
    @include('components.traductor')
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
                    <span class="pref-chip"><i class="fa-solid fa-bolt"></i> Calidad de audio</span>
                    <span class="pref-chip"><i class="fa-solid fa-wave-square"></i> EQ: Balanceado</span>
                </div>
            </section>
            {{-- ====== /HERO NUEVO ====== --}}
            <script>
            const audio = document.querySelector('audio'); // cambia el selector según tu reproductor
            const volumeSlider = document.getElementById('volume');
            const volumeValue = document.getElementById('volume-value');

            // Función que ajusta el volumen
            function updateVolume(val) {
                const percent = parseInt(val, 10);
                volumeValue.textContent = percent + '%';

                // Normalizar a rango 0–1
                if (audio) {
                    audio.volume = percent / 100;
                }

                // Guardar preferencia en localStorage
                localStorage.setItem('defaultVolume', percent);
            }

            // Al cargar la página, restaurar el volumen guardado
            window.addEventListener('DOMContentLoaded', () => {
                const saved = localStorage.getItem('defaultVolume');
                const start = saved ? parseInt(saved, 10) : 100;

                volumeSlider.value = start;
                volumeValue.textContent = start + '%';

                if (audio) {
                    audio.volume = start / 100;
                }
            });
            </script>

        </div>

        {{-- ===== REPRODUCCIÓN ===== --}}
        <div class="preferences-section">
            <h2 class="section-title"><i class="fas fa-play-circle"></i> Reproducción</h2>

            <div class="preference-item">
                <div>
                    <div class="preference-label">Reproducción automática</div>
                    <div class="preference-description">Continúa reproduciendo música similar cuando termine una
                        canción</div>
                </div>
                <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
            </div>



            <div class="preference-item">
                <div>
                    <div class="preference-label">Modo aleatorio</div>
                    <div class="preference-description">Mezcla canciones considerando tu estado de ánimo</div>
                </div>
                <div class="toggle-switch active" onclick="toggleSwitch(this)"></div>
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




        {{-- ===== ECUALIZADOR ===== --}}
        <form method="POST" action="{{ route('eq.save') }}">
            @csrf

            {{-- ===== ECUALIZADOR ===== --}}
            <div class="preferences-section">
                <h2 class="section-title"><i class="fas fa-sliders-h"></i> Ecualizador</h2>

                <div class="equalizer-container" id="bands">
                    @foreach([60,170,310,600,1000,3000,6000,12000,14000,16000] as $freq)
                    <div class="eq-band">
                        <div class="eq-slider-container">
                            <input type="range" min="-12" max="12" step="0.5"
                                value="{{ optional($eq)->{'band_'.$freq} ?? 0 }}" name="eq[{{ $freq }}]"
                                class="eq-slider" data-freq="{{ $freq }}" orient="vertical">
                            <div class="eq-value" id="eq-{{ $freq }}">
                                {{ optional($eq)->{'band_'.$freq} ? ((optional($eq)->{'band_'.$freq} >= 0 ? '+' : '').optional($eq)->{'band_'.$freq}) : '0' }}dB
                            </div>
                        </div>
                        <div class="eq-label">{{ $freq >= 1000 ? ($freq/1000).'k' : $freq }}Hz</div>
                    </div>
                    @endforeach
                </div>

                <div class="preamp"
                    style="margin-top:16px; display:flex; align-items:center; justify-content:space-between;">
                    <div class="row" style="gap:8px">
                        <span class="small">Preamp</span>
                        <span id="preampVal" class="num">{{ optional($eq)->preamp ?? 0 }} dB</span>
                    </div>
                    <div class="row small">
                        <span class="small">Volumen</span>
                        <input id="preamp" name="preamp" type="range" min="-18" max="18" step="-18"
                            value="{{ optional($eq)->preamp ?? 0 }}" />
                    </div>
                </div>


                <div class="eq-presets" style="margin-top:12px;">
                    <button type="button" class="preset-btn active"
                        onclick="applyPreset('balanced')">Balanceado</button>
                    <button type="button" class="preset-btn" onclick="applyPreset('bass')">Graves</button>
                    <button type="button" class="preset-btn" onclick="applyPreset('vocal')">Vocal</button>
                    <button type="button" class="preset-btn" onclick="applyPreset('electronic')">Electrónica</button>
                    <button type="button" class="preset-btn" onclick="applyPreset('rock')">Rock</button>
                    <button id="resetBtn" type="button">🔄 Reset</button>
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


            <center>

                <button type="submit" class="save-button" class="btn btn-primary">Guardar configuración</button>

            </center>
        </form>
        </div>
    </main>
     {{-- ====== FOOTER / PLAYER DERECHO ====== --}}
    @include('components.footer')

</body>

</html>

 <script>
            (() => {
                const FREQS = [60, 170, 310, 600, 1000, 3000, 6000, 12000, 14000, 16000];
                const dbToGain = db => Math.pow(10, db / 20);

                const sliders = document.querySelectorAll('.eq-slider');
                const preamp = document.getElementById('preamp');
                const preampVal = document.getElementById('preampVal');
                const resetBtn = document.getElementById('resetBtn');

                let ac, filters = [],
                    gPreamp, srcNode;

                function ensureCtx() {
                    if (ac) return;
                    ac = new(window.AudioContext || window.webkitAudioContext)();

                    // Crear filtros
                    filters = FREQS.map(freq => {
                        const f = ac.createBiquadFilter();
                        f.type = 'peaking';
                        f.frequency.value = freq;
                        f.Q.value = 1.0;
                        f.gain.value = 0;
                        return f;
                    });

                    // Preamp
                    gPreamp = ac.createGain();
                    gPreamp.gain.value = dbToGain(parseFloat(preamp.value || '0'));

                    // Conectar filtros -> preamp -> destino
                    for (let i = 0; i < filters.length - 1; i++) filters[i].connect(filters[i + 1]);
                    filters[filters.length - 1].connect(gPreamp);
                    gPreamp.connect(ac.destination);

                    // Si existe el <audio id="player"> lo conectamos
                    const audio = document.getElementById('auraAudio');
                    if (audio) {
                        window.bindEqualizerTo(audio);
                        document.dispatchEvent(new Event('aura:eq-ready'));
                    }
                }

                // 🚀 Aplica los valores iniciales que vienen desde Blade
                function applyInitialValues() {
                    ensureCtx();
                    sliders.forEach((sl, idx) => {
                        const db = parseFloat(sl.value);
                        filters[idx].gain.value = db;
                        document.getElementById('eq-' + FREQS[idx]).textContent = (db >= 0 ? '+' + db :
                            db) + 'dB';
                    });
                    const dbPreamp = parseFloat(preamp.value);
                    gPreamp.gain.value = dbToGain(dbPreamp);
                    preampVal.textContent = dbPreamp + " dB";
                }

                // Al cargar la página: aplicar todo lo guardado
                document.addEventListener('DOMContentLoaded', applyInitialValues);

                // === Listeners normales ===
                sliders.forEach((sl, idx) => {
                    sl.addEventListener('input', () => {
                        ensureCtx();
                        const db = parseFloat(sl.value);
                        filters[idx].gain.value = db;
                        document.getElementById('eq-' + FREQS[idx]).textContent = (db >= 0 ? '+' +
                            db : db) + 'dB';
                    });
                });

                preamp.addEventListener('input', () => {
                    ensureCtx();
                    const db = parseFloat(preamp.value);
                    gPreamp.gain.value = dbToGain(db);
                    preampVal.textContent = db + ' dB';
                });

                resetBtn?.addEventListener('click', () => {
                    sliders.forEach((sl, idx) => {
                        sl.value = 0;
                        filters[idx].gain.value = 0;
                        document.getElementById('eq-' + FREQS[idx]).textContent = '0dB';
                    });
                    preamp.value = -18;
                    preamp.dispatchEvent(new Event('input'));
                });

                // Presets
                window.applyPreset = function(name) {
                    const presets = {
                        balanced: [0, 2, -1, 1, 3, 2, 1, 0, -2, 1],
                        bass: [5, 4, 3, 2, 0, -2, -3, -4, -5, -5],
                        vocal: [-2, -1, 0, 2, 4, 3, 1, 0, -1, -2],
                        electronic: [4, 3, 2, 1, 0, 1, 2, 3, 4, 5],
                        rock: [3, 2, 1, 0, -1, 0, 1, 2, 3, 4]
                    };
                    const values = presets[name] || presets['balanced'];
                    sliders.forEach((sl, idx) => {
                        sl.value = values[idx];
                        filters[idx].gain.value = values[idx];
                        document.getElementById('eq-' + FREQS[idx]).textContent = (values[idx] >= 0 ?
                            '+' + values[idx] : values[idx]) + 'dB';
                    });
                };

                // Exponer para reconectar desde fuera si cambias de canción
                window.bindEqualizerTo = function(audioEl) {
                    if (!audioEl) return;
                    ensureCtx();
                    const media = ac.createMediaElementSource(audioEl);
                    media.connect(filters[0]);
                    srcNode = media;
                };
            })();

            function saveEqRealtime() {
                const form = document.querySelector('form');
                if (!form) return;
                const data = new FormData(form);

                fetch("{{ route('eq.save') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: data
                }).catch(console.error);
            }

            sliders.forEach(sl => {
                sl.addEventListener('input', saveEqRealtime);
            });
            preamp.addEventListener('input', saveEqRealtime);

            // Crear un analyser para monitorear en tiempo real
const analyser = ac.createAnalyser();
analyser.fftSize = 2048;

// Conectar filtros -> preamp -> analyser -> destino
for (let i = 0; i < filters.length - 1; i++) filters[i].connect(filters[i + 1]);
filters[filters.length - 1].connect(gPreamp);
gPreamp.connect(analyser);
analyser.connect(ac.destination);

window.bindEqualizerTo = function(audioEl) {
    if (!audioEl) return;
    ensureCtx();
    try {
        if (!srcNode) {
            srcNode = ac.createMediaElementSource(audioEl);
            srcNode.connect(filters[0]); // conecta al inicio de la cadena
        }
    } catch(e) {
        console.warn("El audio ya estaba conectado al EQ");
    }
};
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const sliders = document.querySelectorAll(".eq-slider");
  const preamp  = document.getElementById("preamp");
  const preampVal = document.getElementById("preampVal");

  // Sliders de bandas
  sliders.forEach(sl => {
    sl.addEventListener("input", () => {
      const freq = sl.dataset.freq;
      const db   = parseFloat(sl.value);
      // Actualizar texto del valor
      const label = document.getElementById("eq-" + freq);
      if (label) label.textContent = (db >= 0 ? "+" + db : db) + "dB";
      // Enviar evento al EQ global
      document.dispatchEvent(new CustomEvent("eq:apply", { detail: { freq, db } }));
    });
  });

  // Preamp
  preamp.addEventListener("input", () => {
    const db = parseFloat(preamp.value);
    preampVal.textContent = db + " dB";
    document.dispatchEvent(new CustomEvent("eq:preamp", { detail: { db } }));
  });
});
</script>
<script>
(() => {
  // Frecuencias y helpers
  const FREQS = [60,170,310,600,1000,3000,6000,12000,14000,16000];
  const dbToGain = db => Math.pow(10, db/20);

  let ac = null, streamNode = null, filters = [], preampNode = null, teardown = null;

  // Crea un EQ local usando captureStream (no toca tu EQ global)
  function initLiveEq() {
    if (ac) return; // ya inicializado
    const audio = document.getElementById('auraAudio');
    if (!audio) { console.warn('No se encontró #auraAudio'); return; }

    const stream = audio.captureStream?.() || audio.mozCaptureStream?.();
    if (!stream) {
      console.warn('captureStream() no soportado en este navegador.');
      return;
    }

    ac = new (window.AudioContext || window.webkitAudioContext)();
    streamNode = ac.createMediaStreamSource(stream);

    // Cadena de filtros (paralela a la global)
    filters = FREQS.map(freq => {
      const f = ac.createBiquadFilter();
      f.type = 'peaking';
      f.frequency.value = freq;
      f.Q.value = 1.0;
      f.gain.value = 0;
      return f;
    });

    preampNode = ac.createGain();
    const preampEl = document.getElementById('preamp');
    const preDb = parseFloat(preampEl?.value || '0');
    preampNode.gain.value = dbToGain(preDb);

    // Conexiones: stream -> filtros -> preamp -> destino
    streamNode.connect(filters[0]);
    for (let i = 0; i < filters.length - 1; i++) filters[i].connect(filters[i+1]);
    filters[filters.length - 1].connect(preampNode);
    preampNode.connect(ac.destination);

    // Evitar doble audio: silenciamos el <audio> físico mientras estés en esta vista
    const prevMuted = audio.muted;
    audio.muted = true;

    // Limpieza al salir de la página
    teardown = () => {
      try { preampNode.disconnect(); } catch {}
      try { filters.forEach(n => { try { n.disconnect(); } catch {} }); } catch {}
      try { streamNode.disconnect(); } catch {}
      try { ac.close(); } catch {}
      ac = null; streamNode = null; filters = []; preampNode = null;
      audio.muted = prevMuted;
    };

    window.addEventListener('beforeunload', teardown);
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'hidden' && teardown) teardown();
    });
  }

  // Enlaza los sliders para que suenen en vivo
  function bindUI() {
    const sliders = document.querySelectorAll('.eq-slider');
    const preamp  = document.getElementById('preamp');
    const preampVal = document.getElementById('preampVal');

    sliders.forEach((sl, idx) => {
      sl.addEventListener('input', () => {
        if (!ac) initLiveEq();
        const db = parseFloat(sl.value);
        if (filters[idx]) filters[idx].gain.value = db;

        const freq = sl.dataset.freq;
        const label = document.getElementById('eq-' + freq);
        if (label) label.textContent = (db >= 0 ? '+' + db : db) + 'dB';
      });
    });

    preamp?.addEventListener('input', () => {
      if (!ac) initLiveEq();
      const db = parseFloat(preamp.value);
      if (preampNode) preampNode.gain.value = dbToGain(db);
      if (preampVal) preampVal.textContent = db + ' dB';
    });
  }

  // No arrancamos el AudioContext hasta que muevas algo (para no gastar recursos si no hace falta)
  document.addEventListener('DOMContentLoaded', bindUI);
})();
</script>


