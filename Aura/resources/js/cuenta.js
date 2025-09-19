export default function initMenu() {
console.log("Inicializando scripts de cuenta...");
(function(){
  const root = document.querySelector('#page-account');

  // Banner progresivo
  const heroB = root?.querySelector('.hero-banner');
  if (heroB?.dataset.hires){
    const hi = new Image(); hi.src = heroB.dataset.hires; hi.decoding = 'async';
    hi.onload = ()=>{ heroB.style.backgroundImage = `url('${heroB.dataset.hires}')`; };
  }

  // Intersection reveal
  const items = root?.querySelectorAll('.a-reveal') || [];
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} });
  },{threshold:.16});
  items.forEach(el=>io.observe(el));

  // Modales open/close + ESC + bloquear scroll
  let opened = null;
  function openModal(sel){
    const m = document.querySelector(sel); if(!m) return;
    m.setAttribute('aria-hidden','false'); opened = m;
    document.body.classList.add('modal-open');
    m.scrollTop = 0; // siempre empieza arriba

    // Close on ESC
    m.__esc = (e)=>{ if(e.key === 'Escape') closeModal(m); };
    window.addEventListener('keydown', m.__esc, {passive:true});
  }
  function closeModal(mod){
    const m = mod || opened; if(!m) return;
    m.setAttribute('aria-hidden','true'); opened=null;
    document.body.classList.remove('modal-open');
    if(m.__esc){ window.removeEventListener('keydown', m.__esc); m.__esc=null; }
  }
  document.querySelectorAll('[data-open]').forEach(btn=> btn.addEventListener('click', ()=> openModal(btn.dataset.open)));
  document.querySelectorAll('.modal').forEach(m=>{
    m.addEventListener('click',(e)=>{ if(e.target===m) closeModal(m); });
    m.querySelectorAll('[data-close]').forEach(c=> c.addEventListener('click', ()=> closeModal(m)));
  });

  document.querySelectorAll('.toggle-pass').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const input = btn.previousElementSibling; // input justo antes del botón
      if(input.type === 'password'){
        input.type = 'text';
        btn.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
      } else {
        input.type = 'password';
        btn.innerHTML = '<i class="fa-solid fa-eye"></i>';
      }
    });
  });

  // Previews imagenes
  const avatarInput = document.getElementById('avatarInput');
  const avatarPrev  = document.getElementById('avatarPreview');
  const avatarLive  = document.getElementById('avatarPreviewLive');
  const bannerInput = document.getElementById('bannerInput');
  const bannerPrev  = document.getElementById('bannerPreview');
  root?.querySelector('.file-preview.avatar')?.addEventListener('click', ()=> avatarInput?.click());
  root?.querySelector('.file-preview.banner')?.addEventListener('click', ()=> bannerInput?.click());
  avatarInput?.addEventListener('change', ()=>{
    const f = avatarInput.files?.[0]; if(!f) return;
    const url = URL.createObjectURL(f);
    if(avatarPrev){ avatarPrev.src=url; avatarPrev.style.display='block'; }
    if(avatarLive){ avatarLive.src=url; }
  });
  bannerInput?.addEventListener('change', ()=>{
    const f = bannerInput.files?.[0]; if(!f) return;
    const url = URL.createObjectURL(f);
    if(bannerPrev){ bannerPrev.src=url; bannerPrev.style.display='block'; }
    if(heroB){ heroB.style.backgroundImage = `url('${url}')`; }
  });

  // Validaciones front
  document.getElementById('formEditProfile')?.addEventListener('submit', (e)=>{
    const a = document.getElementById('newEmail')?.value.trim() || '';
    const b = document.getElementById('newEmailConfirm')?.value.trim() || '';
    if((a || b) && a !== b){
      e.preventDefault(); auraToast('El nuevo correo y su confirmación deben coincidir.');
    }
  });

  const pwForm = document.getElementById('formPassword');
  pwForm?.addEventListener('submit', (e)=>{
    const p1 = document.getElementById('newPass')?.value || '';
    const a  = document.getElementById('newPass2')?.value || '';
    const msg = document.getElementById('pwMsg');
    if(p1 !== a){ 
      e.preventDefault(); 
      if(msg) msg.textContent='Las contraseñas no coinciden.'; 
      auraToast('Las contraseñas no coinciden.'); 
    }
    else if(msg){ msg.textContent=''; }
  });

  // Toast helper + modo demo
  window.auraToast = function(msg='Modo demo: acción visual.'){
    const t = document.getElementById('auraToast'); if(!t) return;
    t.textContent = msg; t.classList.add('show');
    clearTimeout(window.__auraToastT); window.__auraToastT = setTimeout(()=> t.classList.remove('show'), 2200);
  };
  document.querySelectorAll('form[data-demo="1"], form[action="#"]').forEach(f=>{
    f.addEventListener('submit', (e)=>{ e.preventDefault(); auraToast(); });
  });

  // Partículas dentro del modal de seguridad (visuales)
  function createSecParticles(){
    const c = document.getElementById('secParticles'); if(!c || c.dataset.ok) return; c.dataset.ok = 1;
    const count = 18;
    for(let i=0;i<count;i++){
      const p = document.createElement('div');
      p.className = 'particle';
      p.style.left = (Math.random()*100)+'%';
      p.style.animationDelay = (Math.random()*20)+'s';
      p.style.animationDuration = (15+Math.random()*10)+'s';
      c.appendChild(p);
    }
  }
  document.querySelector('[data-open="#modalSecurityInfo"]')?.addEventListener('click', createSecParticles);
})();

// Animación automática de notificaciones
document.querySelectorAll('.notify').forEach(el => {
  requestAnimationFrame(() => el.classList.add('show')); // fade-in
  setTimeout(() => {
    el.classList.remove('show'); // fade-out
    setTimeout(() => el.remove(), 600); // espera animación
  }, 3000);
});

// Confirmación de cambio de rol
document.getElementById('btnConfirmRole')?.addEventListener('click', ()=>{
  const isArtist = document.body.dataset.isArtist === "1"; // ← de Blade
  const artistRadio = document.querySelector('input[name="modo"][value="artista"]');
  const userRadio   = document.querySelector('input[name="modo"][value="usuario"]');

  if(!isArtist && artistRadio?.checked){
    // Usuario → Artista → pedir nombre artístico
    document.querySelector('#modalSwitchRole')?.setAttribute('aria-hidden','true');
    document.querySelector('#modalArtistName')?.setAttribute('aria-hidden','false');
  } else {
    // Enviar el form original
    document.querySelector('#modalSwitchRole form')?.submit();
  }
});
}