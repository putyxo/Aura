/* ================================
   1) MEDICIÓN LAYOUT (componentes)
   - Mide ancho de sidebar y player derecho
   - Actualiza --sbW y --rpW para márgenes
==================================*/
(function () {
  const root = document.documentElement;

  function setVar(name, px) {
    if (Number.isFinite(px) && px >= 0) root.style.setProperty(name, px + 'px');
  }

  function measure() {
    const sidebar = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
    const rightPlayer = document.getElementById('rightPlayer');

    const sbW = sidebar ? Math.round(sidebar.getBoundingClientRect().width) : 0;
    const rpW = rightPlayer ? Math.round(rightPlayer.getBoundingClientRect().width) : 0;

    setVar('--sbW', sbW || 90);
    setVar('--rpW', rpW || 360);
  }

  window.addEventListener('load', measure, { once: true });
  window.addEventListener('resize', measure);

  if (window.ResizeObserver) {
    const ro = new ResizeObserver(measure);
    const sidebar = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
    const rightPlayer = document.getElementById('rightPlayer');
    sidebar && ro.observe(sidebar);
    rightPlayer && ro.observe(rightPlayer);
  }
  const mo = new MutationObserver(measure);
  const sidebar = document.querySelector('#sidebar, .sidebar, [data-sidebar]');
  sidebar && mo.observe(sidebar, { attributes: true, attributeFilter: ['class', 'style'] });
  document.body && mo.observe(document.body, { attributes: true, attributeFilter: ['class'] });

  measure();
})();

/* ================================
   2) LÓGICA DE PREFERENCIAS (UI)
==================================*/

// Partículas
function createParticles() {
  const particlesContainer = document.getElementById('particles');
  if (!particlesContainer) return;
  const particleCount = 20;
  for (let i = 0; i < particleCount; i++) {
    const p = document.createElement('div');
    p.classList.add('particle');
    p.style.left = Math.random() * 100 + '%';
    p.style.animationDelay = Math.random() * 15 + 's';
    p.style.animationDuration = (Math.random() * 10 + 10) + 's';
    particlesContainer.appendChild(p);
  }
}

// Cuenta
function redirectToAccountSettings(e) {
  const button = e.currentTarget;
  button.style.background = 'linear-gradient(45deg, #ff1493, #9932cc)';
  button.style.transform = 'scale(0.95)';
  setTimeout(() => {
    alert('Redirigiendo a configuración de cuenta...\n(En una app real, esto abriría la página de configuración)');
    button.style.background = 'rgba(0, 0, 0, 0.3)';
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
    figure.style.filter = 'drop-shadow(0 0 10px rgba(255,105,180,.5))';
    preview.style.borderColor = 'rgba(255,105,180,.5)';
  } else if (value > 125) {
    figure.style.filter = 'drop-shadow(0 0 15px rgba(138,43,226,.8))';
    preview.style.borderColor = 'rgba(138,43,226,.6)';
  } else {
    figure.style.filter = 'drop-shadow(0 0 10px rgba(255,20,147,.5))';
    preview.style.borderColor = 'rgba(255,20,147,.3)';
  }
  display.textContent = value + '%';
}

// Ecualizador
function updateEQ(slider) {
  const value = parseInt(slider.value, 10);
  const display = slider.parentNode.querySelector('.eq-value');
  const band = slider.closest('.eq-band');

  if (display) display.textContent = (value > 0 ? '+' : '') + value + 'dB';

  const pct = (value + 12) / 24 * 100;
  if (value > 0) {
    display && (display.style.color = '#00ff00');
    slider.style.background = `linear-gradient(to top, rgba(0,255,0,.3) 0%, rgba(0,255,0,.3) ${pct}%, rgba(255,255,255,.1) ${pct}%)`;
  } else if (value < 0) {
    display && (display.style.color = '#ff4444');
    slider.style.background = `linear-gradient(to top, rgba(255,68,68,.3) 0%, rgba(255,68,68,.3) ${pct}%, rgba(255,255,255,.1) ${pct}%)`;
  } else {
    display && (display.style.color = '#ff69b4');
    slider.style.background = 'rgba(255,255,255,.1)';
  }

  if (band) {
    band.style.animation = 'pulse .3s ease';
    setTimeout(() => (band.style.animation = ''), 300);
  }
}

function applyPreset(name) {
  const presets = {
    balanced: [0,0,0,0,0,0,0,0,0,0],
    bass: [6,4,2,1,0,-1,-2,-2,-1,0],
    vocal: [-2,-1,0,1,3,3,2,1,0,-1],
    electronic: [3,2,0,-1,2,3,2,3,4,3],
    rock: [4,3,1,0,-1,0,2,3,4,4]
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
    }, i * 100);
  });
}

// Guardar preferencias
function savePreferences() {
  const button = document.querySelector('.save-button');
  if (!button) return;

  const original = button.innerHTML;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
  button.style.background = 'linear-gradient(45deg, #ffd700, #ffa500)';
  button.disabled = true;

  setTimeout(() => {
    button.innerHTML = '<i class="fas fa-check"></i> ¡Guardado Exitosamente!';
    button.style.background = 'linear-gradient(45deg, #32cd32, #00ff00)';
    createSuccessEffect();
    setTimeout(() => {
      button.innerHTML = original;
      button.style.background = 'linear-gradient(45deg, #ff1493, #9932cc, #4b0082)';
      button.disabled = false;
    }, 3000);
  }, 1500);
}

function createSuccessEffect() {
  for (let i = 0; i < 30; i++) {
    const particle = document.createElement('div');
    particle.style.position = 'fixed';
    particle.style.width = '6px';
    particle.style.height = '6px';
    particle.style.background = `hsl(${Math.random() * 60 + 120}, 70%, 60%)`;
    particle.style.borderRadius = '50%';
    particle.style.pointerEvents = 'none';
    particle.style.zIndex = '9999';
    particle.style.left = '50%';
    particle.style.top = '80%';
    document.body.appendChild(particle);

    const angle = (Math.PI * 2 * i) / 30;
    const velocity = Math.random() * 200 + 100;

    particle.animate([
      { transform: 'translate(-50%, -50%) scale(1)', opacity: 1 },
      { transform: `translate(${Math.cos(angle) * velocity}px, ${Math.sin(angle) * velocity - 200}px) scale(0)`, opacity: 0 }
    ], { duration: 1000, easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)' })
      .onfinish = () => particle.remove();
  }
}

// Init
document.addEventListener('DOMContentLoaded', () => {
  createParticles();

  // Iniciar EQ displays
  document.querySelectorAll('.eq-slider').forEach(s => updateEQ(s));

  // Observer para animar secciones
  const sections = document.querySelectorAll('.preferences-section');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) e.target.style.animation = 'fadeInUp .6s ease-out forwards';
    });
  }, { threshold: 0.1 });
  sections.forEach(sec => observer.observe(sec));

  // Gradiente de sliders (no EQ)
  const sliders = document.querySelectorAll('.slider');
  sliders.forEach(sl => {
    const pct = (sl.value - sl.min) / (sl.max - sl.min) * 100;
    sl.style.background = `linear-gradient(to right,
      rgba(255,20,147,.8) 0%,
      rgba(153,50,204,.8) ${pct}%,
      rgba(255,255,255,.1) ${pct}%,
      rgba(255,255,255,.1) 100%)`;
    sl.addEventListener('input', function () {
      const p = (this.value - this.min) / (this.max - this.min) * 100;
      this.style.background = `linear-gradient(to right,
        rgba(255,20,147,.8) 0%,
        rgba(153,50,204,.8) ${p}%,
        rgba(255,255,255,.1) ${p}%,
        rgba(255,255,255,.1) 100%)`;
    });
  });

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
      if (this.value === 'lossless') {
        alert('⚠️ La calidad sin pérdida consumirá significativamente más datos móviles.');
      }
    });
  });

  // Aplicar estado inicial de zoom
  const zoom = document.getElementById('zoom');
  if (zoom) updateZoom(zoom.value);
});
