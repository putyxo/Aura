export default function initPreferencias() {
  console.log("Inicializando scripts de Preferencias...");
/* ================================
   0) PALETA (morado + oscuros)
==================================*/
const COLORS = {
  violetA: '#7c3aed',
  violetB: '#a78bfa',
  violetDeep: '#4c1d95',
  violetEdge: '#6d28d9',
  track: 'rgba(255,255,255,.10)',
  glass: 'rgba(0,0,0,.30)',
};

/* ================================
   1) MEDICIÓN LAYOUT (sidebar, player, header)
==================================*/
(function () {
  const root = document.documentElement;
  const setVar = (name, px) => Number.isFinite(px) && px >= 0 && root.style.setProperty(name, px + 'px');

  function measure() {
    const sbEl  = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
    const rpEl  = document.getElementById('rightPlayer');
    const hdEl  = document.querySelector('.ah-header, header.ah-header, #header, [data-header]');

    setVar('--sbW', sbEl ? Math.round(sbEl.getBoundingClientRect().width)  : 90);
    setVar('--rpW', rpEl ? Math.round(rpEl.getBoundingClientRect().width)  : 360);
    setVar('--hdrH', hdEl ? Math.round(hdEl.getBoundingClientRect().height) : 72);
  }

  window.addEventListener('load', measure, { once: true });
  window.addEventListener('resize', measure);

  if (window.ResizeObserver) {
    const ro = new ResizeObserver(measure);
    const sbEl = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
    const rpEl = document.getElementById('rightPlayer');
    const hdEl = document.querySelector('.ah-header, header.ah-header, #header, [data-header]');
    sbEl && ro.observe(sbEl);
    rpEl && ro.observe(rpEl);
    hdEl && ro.observe(hdEl);
  }

  const mo = new MutationObserver(measure);
  const sbEl = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
  const hdEl = document.querySelector('.ah-header, header.ah-header, #header, [data-header]');
  sbEl && mo.observe(sbEl, { attributes: true, attributeFilter: ['class', 'style'] });
  hdEl && mo.observe(hdEl, { attributes: true, attributeFilter: ['class', 'style'] });
  document.body && mo.observe(document.body, { attributes: true, attributeFilter: ['class'] });

  measure();
})();

/* ================================
   2) LÓGICA DE PREFERENCIAS (UI)
   (SIN partículas ni fondo)
==================================*/

// Cuenta
function redirectToAccountSettings(e) {
  const button = e.currentTarget;
  button.style.background = `linear-gradient(45deg, ${COLORS.violetA}, ${COLORS.violetB})`;
  button.style.transform = 'scale(0.95)';
  setTimeout(() => {
    alert('Redirigiendo a configuración de cuenta...');
    button.style.background = COLORS.glass;
    button.style.transform = 'scale(1)';
  }, 200);
}

// Switch simple
function toggleSwitch(el) {
  el.classList.toggle('active');
  el.style.animation = 'none';
  el.offsetHeight; // reflow
  el.style.animation = 'pulse 0.3s ease';
}

// Actualizar texto por id
function updateValue(elementId, value) {
  const node = document.getElementById(elementId);
  if (node) node.textContent = value;
}

// Nivel de exploración
function getExplorationLevel(v) {
  const value = Number(v);
  if (value <= 2) return 'Muy bajo';
  if (value <= 4) return 'Bajo';
  if (value <= 6) return 'Medio';
  if (value <= 8) return 'Alto';
  if (value <= 9) return 'Muy alto';
  return 'Extremo';
}

// Toggle de idioma
function toggleLanguage(wrapper) {
  const options = wrapper.querySelectorAll('.language-option');
  const slider = wrapper.querySelector('.language-slider');
  const active = wrapper.querySelector('.language-option.active');
  options.forEach(o => o.classList.remove('active'));
  if (active && active.dataset.lang === 'es') {
    options[1].classList.add('active');
    slider.style.transform = 'translateX(56px)';
  } else {
    options[0].classList.add('active');
    slider.style.transform = 'translateX(0)';
  }
}

// Zoom
function updateZoom(value) {
  const figure = document.getElementById('zoom-figure');
  const preview = document.getElementById('zoom-preview');
  const display = document.getElementById('zoom-value');
  if (!figure || !preview || !display) return;

  const scale = value / 100;
  figure.style.transform = `scale(${scale})`;
  figure.style.fontSize = (24 * scale) + 'px';

  if (value < 75) {
    figure.style.filter = `drop-shadow(0 0 10px ${COLORS.violetB})`;
    preview.style.borderColor = 'rgba(167,139,250,.5)';
  } else if (value > 125) {
    figure.style.filter = `drop-shadow(0 0 15px ${COLORS.violetA})`;
    preview.style.borderColor = 'rgba(124,58,237,.6)';
  } else {
    figure.style.filter = `drop-shadow(0 0 10px ${COLORS.violetB})`;
    preview.style.borderColor = 'rgba(167,139,250,.35)';
  }
  display.textContent = value + '%';
}

// Ecualizador (tonos morados)
function updateEQ(slider) {
  const value = parseInt(slider.value, 10);
  const display = slider.parentNode.querySelector('.eq-value');
  const band = slider.closest('.eq-band');

  if (display) display.textContent = (value > 0 ? '+' : '') + value + 'dB';

  const pct = (value + 12) / 24 * 100;
  if (value > 0) {
    display && (display.style.color = COLORS.violetB);
    slider.style.background = `linear-gradient(to top,
      rgba(167,139,250,.35) 0%,
      rgba(167,139,250,.35) ${pct}%,
      ${COLORS.track} ${pct}%)`;
  } else if (value < 0) {
    display && (display.style.color = COLORS.violetEdge);
    slider.style.background = `linear-gradient(to top,
      rgba(109,40,217,.35) 0%,
      rgba(109,40,217,.35) ${pct}%,
      ${COLORS.track} ${pct}%)`;
  } else {
    display && (display.style.color = COLORS.violetB);
    slider.style.background = COLORS.track;
  }

  if (band) {
    band.style.animation = 'pulse .3s ease';
    setTimeout(() => (band.style.animation = ''), 300);
  }
}

function applyPreset(name) {
  const presets = {
    balanced:   [0,0,0,0,0,0,0,0,0,0],
    bass:       [6,4,2,1,0,-1,-2,-2,-1,0],
    vocal:      [-2,-1,0,1,3,3,2,1,0,-1],
    electronic: [3,2,0,-1,2,3,2,3,4,3],
    rock:       [4,3,1,0,-1,0,2,3,4,4]
  };
  const values = presets[name];
  if (!values) return;

  const sliders = document.querySelectorAll('.eq-slider');
  const buttons = document.querySelectorAll('.preset-btn');
  buttons.forEach(b => b.classList.remove('active'));
  const activeBtn = document.querySelector(`[onclick="applyPreset('${name}')"]`);
  activeBtn && activeBtn.classList.add('active');

  sliders.forEach((s, i) => {
    setTimeout(() => {
      s.value = values[i];
      updateEQ(s);
    }, i * 90);
  });
}

// Guardar preferencias (gradiente morado)
function savePreferences() {
  const button = document.querySelector('.save-button');
  if (!button) return;

  const original = button.innerHTML;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
  button.style.background = `linear-gradient(45deg, ${COLORS.violetA}, ${COLORS.violetB})`;
  button.disabled = true;

  setTimeout(() => {
    button.innerHTML = '<i class="fas fa-check"></i> ¡Guardado Exitosamente!';
    button.style.background = `linear-gradient(45deg, ${COLORS.violetDeep}, ${COLORS.violetA})`;
    createSuccessEffect();
    setTimeout(() => {
      button.innerHTML = original;
      button.style.background = `linear-gradient(45deg, ${COLORS.violetA}, ${COLORS.violetB}, ${COLORS.violetDeep})`;
      button.disabled = false;
    }, 2000);
  }, 1200);
}

function createSuccessEffect() {
  const palette = [COLORS.violetB, COLORS.violetA, COLORS.violetEdge];
  for (let i = 0; i < 28; i++) {
    const particle = document.createElement('div');
    particle.style.position = 'fixed';
    particle.style.width = '6px';
    particle.style.height = '6px';
    particle.style.background = palette[i % palette.length];
    particle.style.borderRadius = '50%';
    particle.style.pointerEvents = 'none';
    particle.style.zIndex = '9999';
    particle.style.left = '50%';
    particle.style.top = '80%';
    document.body.appendChild(particle);

    const angle = (Math.PI * 2 * i) / 28;
    const velocity = Math.random() * 200 + 100;

    particle.animate(
      [
        { transform: 'translate(-50%, -50%) scale(1)', opacity: 1 },
        { transform: `translate(${Math.cos(angle) * velocity}px, ${Math.sin(angle) * velocity - 200}px) scale(0)`, opacity: 0 }
      ],
      { duration: 1000, easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)' }
    ).onfinish = () => particle.remove();
  }
}

/* ================================
   3) INIT
==================================*/
document.addEventListener('DOMContentLoaded', () => {
  // Iniciar EQ displays
  document.querySelectorAll('.eq-slider').forEach(s => updateEQ(s));

  // Animación al entrar
  const sections = document.querySelectorAll('.preferences-section');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) e.target.style.animation = 'fadeInUp .6s ease-out forwards'; });
  }, { threshold: 0.1 });
  sections.forEach(sec => observer.observe(sec));

  // Sliders lineales (no EQ)
  const sliders = document.querySelectorAll('.slider');
  const setTrack = (el) => {
    const p = (el.value - el.min) / (el.max - el.min) * 100;
    el.style.background = `linear-gradient(to right,
      ${COLORS.violetA} 0%,
      ${COLORS.violetB} ${p}%,
      ${COLORS.track} ${p}%,
      ${COLORS.track} 100%)`;
  };
  sliders.forEach(sl => { setTrack(sl); sl.addEventListener('input', function(){ setTrack(this); }); });

  // Feedback táctil para switches
  document.querySelectorAll('.toggle-switch').forEach(sw => {
    sw.addEventListener('click', function () {
      this.style.transform = 'scale(0.95)';
      setTimeout(() => (this.style.transform = 'scale(1)'), 100);
    });
  });

  // Aviso calidad "lossless"
  document.querySelectorAll('.dropdown-select').forEach(sel => {
    sel.addEventListener('change', function () {
      if (this.value === 'lossless') alert('⚠️ La calidad sin pérdida consumirá más datos móviles.');
    });
  });

  // Estado inicial de zoom
  const zoom = document.getElementById('zoom');
  if (zoom) updateZoom(zoom.value);
});

/* ===========================================
   4) EXPONER FUNCIONES PARA onclick="..."
===========================================*/
Object.assign(window, {
  redirectToAccountSettings,
  toggleSwitch,
  updateValue,
  getExplorationLevel,
  toggleLanguage,
  updateZoom,
  updateEQ,
  applyPreset,
  savePreferences,
});
(() => {
  const FREQS = [60,170,310,600,1000,3000,6000,12000,14000,16000];
  const dbToGain = db => Math.pow(10, db/20);

  const sliders = document.querySelectorAll('.eq-slider');
  const preamp = document.getElementById('preamp');
  const preampVal = document.getElementById('preampVal');
  const resetBtn = document.getElementById('resetBtn');

  let ac, filters=[], gPreamp, srcNode;

  function ensureCtx(){
    if (ac) return;
    ac = new (window.AudioContext||window.webkitAudioContext)();

    // Crear filtros
    filters = FREQS.map(freq=>{
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
    for (let i=0; i<filters.length-1; i++) filters[i].connect(filters[i+1]);
    filters[filters.length-1].connect(gPreamp);
    gPreamp.connect(ac.destination);

    // Si existe el <audio id="player"> lo conectamos
    const audio = document.getElementById('auraAudio');
if (audio) {
  window.bindEqualizerTo(audio); 
  document.dispatchEvent(new Event('aura:eq-ready'));
}
  }

  // 🚀 Aplica los valores iniciales que vienen desde Blade
  function applyInitialValues(){
    ensureCtx();
    sliders.forEach((sl, idx)=>{
      const db = parseFloat(sl.value);
      filters[idx].gain.value = db;
      document.getElementById('eq-'+FREQS[idx]).textContent = (db>=0? '+'+db: db)+'dB';
    });
    const dbPreamp = parseFloat(preamp.value);
    gPreamp.gain.value = dbToGain(dbPreamp);
    preampVal.textContent = dbPreamp+" dB";
  }

  // Al cargar la página: aplicar todo lo guardado
  document.addEventListener('DOMContentLoaded', applyInitialValues);

  // === Listeners normales ===
  sliders.forEach((sl, idx)=>{
    sl.addEventListener('input', ()=>{
      ensureCtx();
      const db = parseFloat(sl.value);
      filters[idx].gain.value = db;
      document.getElementById('eq-'+FREQS[idx]).textContent = (db>=0? '+'+db: db)+'dB';
    });
  });

  preamp.addEventListener('input', ()=>{
    ensureCtx();
    const db = parseFloat(preamp.value);
    gPreamp.gain.value = dbToGain(db);
    preampVal.textContent = db+' dB';
  });

  resetBtn?.addEventListener('click', ()=>{
    sliders.forEach((sl, idx)=>{
      sl.value=0;
      filters[idx].gain.value=0;
      document.getElementById('eq-'+FREQS[idx]).textContent='0dB';
    });
    preamp.value=0;
    preamp.dispatchEvent(new Event('input'));
  });

  // Presets
  window.applyPreset = function(name){
    const presets={
      balanced:[0,2,-1,1,3,2,1,0,-2,1],
      bass:[5,4,3,2,0,-2,-3,-4,-5,-5],
      vocal:[-2,-1,0,2,4,3,1,0,-1,-2],
      electronic:[4,3,2,1,0,1,2,3,4,5],
      rock:[3,2,1,0,-1,0,1,2,3,4]
    };
    const values=presets[name]||presets['balanced'];
    sliders.forEach((sl, idx)=>{
      sl.value=values[idx];
      filters[idx].gain.value=values[idx];
      document.getElementById('eq-'+FREQS[idx]).textContent=(values[idx]>=0? '+'+values[idx]:values[idx])+'dB';
    });
  };

  // Exponer para reconectar desde fuera si cambias de canción
  window.bindEqualizerTo=function(audioEl){
    if (!audioEl) return;
    ensureCtx();
    const media=ac.createMediaElementSource(audioEl);
    media.connect(filters[0]);
    srcNode=media;
  };
})();

function saveEqRealtime() {
  const form = document.querySelector('form');
  if (!form) return;
  const data = new FormData(form);

  fetch("{{ route('eq.save') }}", {
    method: "POST",
    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
    body: data
  }).catch(console.error);
}

sliders.forEach(sl => {
  sl.addEventListener('input', saveEqRealtime);
});
preamp.addEventListener('input', saveEqRealtime);
}