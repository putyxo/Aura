{{-- resources/views/components/fondo.blade.php --}}
@once
<div class="fondo-aura">
  <div class="fondo-aura__base"></div>
  <div class="fondo-aura__aurora"></div>
  <div class="fondo-aura__overlay"></div>

  {{-- Partículas pre-renderizadas (estables, sin JS) --}}
  <div class="fondo-aura__particles">
    @for ($i = 0; $i < 32; $i++)
      <span class="fondo-particle"></span>
    @endfor
  </div>
</div>

<style>
  /* ===== Fondo global reutilizable (morado + oscuros) ===== */
  .fondo-aura{
    position:fixed; inset:-0; pointer-events:none; z-index:-999;
    --bg-deep: #090012;
    --bg-dark: #0c0714;
    --bg-spot-1: #1b0b2b;
    --bg-spot-2: #1a1033;
    --violet-400: #a78bfa;
    --violet-600: #7c3aed;
    --violet-700: #6d28d9;
  }

  /* Capa base (gradientes) */
  .fondo-aura__base{
    position:absolute; inset:0;
    background:
      radial-gradient(1200px 700px at 10% 10%, var(--bg-spot-1) 0%, transparent 60%),
      radial-gradient(1000px 600px at 90% 0%,  var(--bg-spot-2) 0%, transparent 60%),
      linear-gradient(135deg, #000 0%, #000 20%, var(--bg-dark) 55%, var(--bg-deep) 100%);
  }

  /* Auroras moradas animadas (suaves) */
  .fondo-aura__aurora{
    position:absolute; inset:-20% -30% -30% -30%;
    background:
      radial-gradient(40% 60% at 20% 30%, rgba(167,139,250,.35), transparent 60%),
      radial-gradient(35% 55% at 80% 10%, rgba(124,58,237,.28), transparent 60%),
      radial-gradient(60% 80% at 55% 100%, rgba(109,40,217,.28), transparent 60%);
    filter: blur(30px);
    opacity:.95;
    animation: fondoAuroraFloat 14s ease-in-out infinite alternate;
  }
  @keyframes fondoAuroraFloat{
    from { transform: translateY(0) scale(1); }
    to   { transform: translateY(-6%) scale(1.05); }
  }

  /* Overlay sutil */
  .fondo-aura__overlay{
    position:absolute; inset:0;
    background:
      linear-gradient(45deg, transparent 40%, rgba(255,255,255,.02) 50%, transparent 60%),
      linear-gradient(-45deg, transparent 40%, rgba(255,255,255,.02) 50%, transparent 60%);
  }

  /* ===== Partículas (sin JS, patrón estable) ===== */
  .fondo-aura__particles{ position:absolute; inset:0; }
  .fondo-particle{
    position:absolute; bottom:-20px;
    width:4px; height:4px; border-radius:50%;
    background: linear-gradient(45deg, var(--violet-700), var(--violet-400));
    animation: fondoDot linear infinite;
    opacity: 0;
  }
  @keyframes fondoDot{
    0%   { transform: translateY(0) rotate(0);   opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    100% { transform: translateY(-110vh) rotate(360deg); opacity: 0; }
  }

  /* Distribución fija (no cambia entre páginas) */
  /* Puedes aumentar a 48/64 duplicando el patrón */
  .fondo-particle:nth-child(1)  { left: 3%;  animation-duration:16s; animation-delay:0s;   }
  .fondo-particle:nth-child(2)  { left: 8%;  animation-duration:18s; animation-delay:.6s;  }
  .fondo-particle:nth-child(3)  { left: 12%; animation-duration:15s; animation-delay:1.2s; }
  .fondo-particle:nth-child(4)  { left: 17%; animation-duration:19s; animation-delay:1.8s; }
  .fondo-particle:nth-child(5)  { left: 21%; animation-duration:17s; animation-delay:.9s;  }
  .fondo-particle:nth-child(6)  { left: 26%; animation-duration:16s; animation-delay:2.1s; }
  .fondo-particle:nth-child(7)  { left: 30%; animation-duration:20s; animation-delay:.3s;  }
  .fondo-particle:nth-child(8)  { left: 35%; animation-duration:14s; animation-delay:1.5s; }
  .fondo-particle:nth-child(9)  { left: 39%; animation-duration:18s; animation-delay:2.7s; }
  .fondo-particle:nth-child(10) { left: 43%; animation-duration:17s; animation-delay:.2s;  }
  .fondo-particle:nth-child(11) { left: 47%; animation-duration:19s; animation-delay:1.7s; }
  .fondo-particle:nth-child(12) { left: 52%; animation-duration:16s; animation-delay:2.3s; }
  .fondo-particle:nth-child(13) { left: 56%; animation-duration:15s; animation-delay:.8s;  }
  .fondo-particle:nth-child(14) { left: 60%; animation-duration:20s; animation-delay:1.4s; }
  .fondo-particle:nth-child(15) { left: 65%; animation-duration:18s; animation-delay:2.6s; }
  .fondo-particle:nth-child(16) { left: 69%; animation-duration:16s; animation-delay:.4s;  }
  .fondo-particle:nth-child(17) { left: 73%; animation-duration:19s; animation-delay:1.1s; }
  .fondo-particle:nth-child(18) { left: 77%; animation-duration:17s; animation-delay:2.0s; }
  .fondo-particle:nth-child(19) { left: 81%; animation-duration:14s; animation-delay:.5s;  }
  .fondo-particle:nth-child(20) { left: 85%; animation-duration:20s; animation-delay:1.9s; }
  .fondo-particle:nth-child(21) { left: 89%; animation-duration:18s; animation-delay:.7s;  }
  .fondo-particle:nth-child(22) { left: 93%; animation-duration:16s; animation-delay:1.6s; }
  .fondo-particle:nth-child(23) { left: 96%; animation-duration:19s; animation-delay:2.2s; }
  .fondo-particle:nth-child(24) { left: 24%; animation-duration:17s; animation-delay:1.0s; }
  .fondo-particle:nth-child(25) { left: 13%; animation-duration:15s; animation-delay:2.4s; }
  .fondo-particle:nth-child(26) { left: 41%; animation-duration:20s; animation-delay:.1s;  }
  .fondo-particle:nth-child(27) { left: 58%; animation-duration:18s; animation-delay:1.3s; }
  .fondo-particle:nth-child(28) { left: 72%; animation-duration:16s; animation-delay:2.9s; }
  .fondo-particle:nth-child(29) { left: 5%;  animation-duration:19s; animation-delay:.4s;  }
  .fondo-particle:nth-child(30) { left: 88%; animation-duration:17s; animation-delay:1.8s; }
  .fondo-particle:nth-child(31) { left: 33%; animation-duration:14s; animation-delay:2.5s; }
  .fondo-particle:nth-child(32) { left: 67%; animation-duration:20s; animation-delay:.9s;  }

  /* Accesibilidad: menos movimiento */
  @media (prefers-reduced-motion: reduce){
    .fondo-aura__aurora{ animation: none !important; }
    .fondo-particle{ animation: none !important; opacity:.25; }
  }
</style>
@endonce
