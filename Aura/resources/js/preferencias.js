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
