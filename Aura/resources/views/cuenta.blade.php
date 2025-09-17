<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ __('account.title') }} — Aura</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

@php
  use Illuminate\Support\Facades\Storage;

  $u = auth()->user() ?? $user ?? null;

  // Helper local (NO Drive): de ruta relativa en storage/public a URL pública
  $media = function ($path) {
    if (!$path) return null;
    return preg_match('/^https?:\/\//', $path) ? $path : asset(Storage::url($path));
  };

  $bannerSrc = $u && $u->banner ? $media($u->banner) : asset('img/default-banner.jpg');
  $qs = (strpos($bannerSrc, '?') !== false) ? '&' : '?';
  $bannerLow  = $bannerSrc . $qs . 'v=' . time();
  $bannerHigh = $bannerSrc . $qs . 'v=' . time();

  $nombre   = $u->nombre_artistico ?? $u->nombre ?? 'Usuario';
  $correo   = $u->email ?? '—';
  $miembroDesde = isset($u->created_at) ? optional($u->created_at)->format('Y') : null;
@endphp

@php
use Illuminate\Support\Facades\Route as RouteFacade;
$actionUpdate      = RouteFacade::has('perfil.update')         ? route('perfil.update')         : '#';
$actionToggleRole  = RouteFacade::has('perfil.toggleRole')     ? route('perfil.toggleRole')     : '#';
$actionChangePass  = RouteFacade::has('perfil.changePassword') ? route('perfil.changePassword') : '#';
$actionLanguage    = RouteFacade::has('perfil.language')       ? route('perfil.language')       : '#';
$actionSupport     = RouteFacade::has('support.send')          ? route('support.send')          : '#';
$actionVerify      = RouteFacade::has('verificacion.solicitar')? route('verificacion.solicitar'): '#';
$actionCerts       = RouteFacade::has('verificacion.certificados')? route('verificacion.certificados'): '#';
@endphp

<link rel="preload" as="image" href="{{ $bannerLow }}">

<style>
  :root{
    --ink-0:#0b0b10; --ink-1:#11111a; --ink-2:#171725;
    --fg-1:#f5f3ff; --fg-dim:#c7b7ff;

    --violet-200:#ddd6fe; --violet-300:#c4b5fd; --violet-400:#a78bfa; --violet-500:#8b5cf6; --violet-600:#7c3aed; --violet-700:#6d28d9;

    --line:rgba(255,255,255,.12);
    --shadow-lg:0 24px 60px rgba(0,0,0,.45);
    --radius-xl:24px;

    --avatar-size:220px; --hero-h:420px; --grid-gap:28px;

    /* modal */
    --m-overlay: rgba(5,5,10,.72);
    --m-border: rgba(199,183,255,.45);
    --m-border-strong: rgba(199,183,255,.65);
    --m-bg-a: rgba(22,18,36,.92);
    --m-bg-b: rgba(86,59,155,.22);
    --m-ring: rgba(167,139,250,.55);
    --m-ring-soft: rgba(167,139,250,.18);

    /* safe padding para modales */
    --safe-gap: clamp(16px, 3vw, 44px);
  }

  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    margin:0;
    font-family:'Montserrat',system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;
    background:
      radial-gradient(1200px 600px at 10% -10%, rgba(124,58,237,.25), transparent 60%),
      radial-gradient(1000px 500px at 100% 0%, rgba(76,29,149,.22), transparent 60%),
      var(--ink-0);
    color:var(--fg-1);
    -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
    overflow-x:hidden;
    /* evita saltos al aparecer la barra */
    scrollbar-gutter: stable both-edges;
  }
  a{color:inherit;text-decoration:none}
  h1,h2,h3,h4{margin:0}

  /* Botones */
  .btn{display:inline-flex;align-items:center;gap:10px;padding:12px 18px;border-radius:14px;border:1px solid transparent;font-weight:800;cursor:pointer;transition:.18s ease;color:#0b0720;background:linear-gradient(135deg,#f5f3ff,#e9d5ff);box-shadow:0 10px 22px rgba(124,58,237,.22)}
  .btn:hover{transform:translateY(-2px) scale(1.01);filter:brightness(1.03)}
  .btn-primary{color:#14083a;background:linear-gradient(135deg,var(--violet-300),var(--violet-500));border:1px solid rgba(124,58,237,.35);box-shadow:0 16px 36px rgba(124,58,237,.35)}
  .btn-secondary{color:#120733;background:linear-gradient(135deg,var(--violet-200),var(--violet-400));border:1px solid rgba(167,139,250,.45)}
  .btn-ghost{color:#1a0b44;background:linear-gradient(135deg,rgba(231,229,255,.9),rgba(212,197,255,.72));border:1px solid rgba(167,139,250,.55)}
  .btn-danger{color:#2b0a15;background:linear-gradient(135deg,#fecaca,#fda4af);border:1px solid rgba(244,63,94,.35)}
.toggle-pass {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #a78bfa;
  cursor: pointer;
  font-size: 1rem;
  opacity: 0.8;
}
.toggle-pass:hover { opacity: 1; }
  /* Layout */
  .main-content{padding:32px clamp(16px,3vw,38px) 160px}
  .account-grid{
    max-width:1280px;margin:0 auto;display:grid;gap:var(--grid-gap);
    grid-template-columns:1.08fr .92fr;
    grid-template-areas:
      "hero hero"
      "actions actions"
      "tools security"
      "language support";
  }
  @media (max-width:1024px){
    .account-grid{grid-template-columns:1fr;grid-template-areas:"hero" "actions" "tools" "security" "language" "support"}
    :root{--avatar-size:188px;--hero-h:380px}
  }
  @media (max-width:680px){:root{--avatar-size:150px;--hero-h:340px}}

  /* Hero */
  .account-hero{grid-area:hero;position:relative;margin:12px 0 calc(var(--avatar-size)*.70)}
  .hero-wrap{position:relative;width:92%;margin:0 auto;border-radius:var(--radius-xl);overflow:hidden;box-shadow:0 30px 90px rgba(0,0,0,.6)}
  .hero-banner{height:var(--hero-h);background-size:cover;background-position:center}
  .hero-scrim,.hero-border{position:absolute;inset:0}
  .hero-scrim::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 60% at 50% 18%,rgba(4,4,6,.35) 0%,rgba(4,4,8,.6) 60%,rgba(6,6,12,.9) 100%);mix-blend-mode:multiply}
  .hero-scrim::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(13,10,18,.45) 0%,rgba(8,8,12,.78) 50%,rgba(6,6,12,.94) 100%)}
  .hero-border{box-shadow:inset 0 0 0 1px rgba(255,255,255,.10);pointer-events:none}
  .hero-head{position:absolute;top:26px;left:50%;transform:translateX(-50%);z-index:3;width:min(94%,980px);display:flex;flex-direction:column;align-items:center;gap:10px}
  .pref-hero h1,.hero-title{font-size:clamp(26px,3.2vw,40px);line-height:1.1;background:linear-gradient(100deg,#fff,#fff 100%,#a78bfa 100%,#f5f3ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
  .hero-sub{color:#efe9ff;font-weight:800;text-align:center;text-shadow:0 2px 18px rgba(124,58,237,.35)}
  .text-shield{background:rgba(18,12,34,.38);border:1px solid rgba(255,255,255,.12);padding:12px 18px;border-radius:14px;backdrop-filter:blur(6px) saturate(140%)}
  .hero-meta{position:absolute;left:50%;transform:translateX(-50%);bottom:calc(var(--avatar-size)*.88);z-index:4;display:flex;gap:12px;flex-wrap:wrap;justify-content:center;width:min(94%,1000px)}
  .meta-chip{display:inline-flex;align-items:center;gap:10px;font-weight:900;color:#1a0b44;background:linear-gradient(135deg,#ede9fe,#e9d5ff);padding:10px 14px;border-radius:999px;border:1px solid rgba(167,139,250,.5)}
  .avatar-wrap{position:absolute;left:50%;transform:translateX(-50%);bottom:calc(var(--avatar-size)*-.44);width:var(--avatar-size);height:var(--avatar-size);border-radius:50%;padding:6px;background:linear-gradient(135deg,var(--violet-600),var(--violet-500));display:flex;align-items:center;justify-content:center;box-shadow:0 25px 60px rgba(0,0,0,.85),0 0 46px rgba(124,58,237,.55);z-index:5}
  .avatar-img{width:100%;height:100%;object-fit:cover;border-radius:50%;border:5px solid var(--ink-0)}
  .avatar-fallback{width:100%;height:100%;display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:4.6rem;font-weight:900;background:var(--violet-600)}

  /* Tarjetas */
  .card{position:relative;display:flex;flex-direction:column;gap:14px;background:linear-gradient(145deg,rgba(22,18,36,.85),rgba(76,29,149,.20));border:1px solid rgba(167,139,250,.45);border-radius:22px;padding:22px;box-shadow:var(--shadow-lg);backdrop-filter:blur(18px);overflow:hidden}
  .card:hover{border-color:rgba(124,58,237,.7);transform:translateY(-4px);box-shadow:0 30px 80px rgba(124,58,237,.35)}
  .card h3{font-size:1.2rem;font-weight:900;color:#efe9ff}
  .muted{color:#dcd6ff}

  .quick-actions{grid-area:actions}
  .actions-grid{display:grid;gap:var(--grid-gap);grid-template-columns:repeat(3,1fr);grid-auto-rows:1fr}
  @media (max-width:1100px){.actions-grid{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:560px){.actions-grid{grid-template-columns:1fr}}
  .card .card-actions{margin-top:auto;display:flex;gap:10px;flex-wrap:wrap}

  .tools{grid-area:tools} .security{grid-area:security}
  .language{grid-area:language} .support{grid-area:support}

  /* Controles */
  .form-ctrl{width:100%;padding:14px;border-radius:12px;border:1px solid rgba(167,139,250,.45);background:linear-gradient(135deg,#0f0e17,#171628);color:#efe9ff;box-shadow:inset 0 2px 10px rgba(0,0,0,.35);transition:border-color .18s, box-shadow .18s}
  .form-ctrl[disabled]{opacity:.7;cursor:not-allowed;filter:saturate(.7)}
  .field{display:flex;flex-direction:column;gap:8px}
  .label{display:flex;align-items:center;gap:8px;color:#cfc6ff;font-weight:800;font-size:.92rem}
  .label .lock{color:#c084fc}
  .input-wrap{position:relative}
  .left-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.9;color:#a78bfa}
  input.with-ico{padding-left:38px}
  .input-wrap:focus-within .form-ctrl{
    border-color:var(--m-ring);
    box-shadow:
      0 0 0 1px var(--m-ring),
      0 0 0 6px var(--m-ring-soft),
      inset 0 2px 10px rgba(0,0,0,.35);
  }

  /* Previews */
  .file-preview{position:relative;display:block;overflow:hidden;border-radius:14px;border:1px solid rgba(167,139,250,.45);background:#11111a;cursor:pointer}
  .file-preview img{width:100%;height:100%;object-fit:cover;display:block}
  .file-preview .overlay{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#14083a;background:linear-gradient(135deg,#f5f3ff90,#e9d5ff80);opacity:0;transition:.2s;border-radius:inherit}
  .file-preview:hover .overlay{opacity:1}
  .file-preview.avatar{width:120px;height:120px;border-radius:50%}
  .file-preview.avatar img,.file-preview.avatar .overlay{border-radius:50%}
  .file-preview.banner{height:140px}

  /* ====== MODALES (mejorados) ====== */
  .modal{
    position:fixed;inset:0;display:flex;align-items:center;justify-content:center;
    background:
      radial-gradient(1200px 800px at 20% -10%, rgba(124,58,237,.20), transparent 60%),
      radial-gradient(900px 600px at 110% 10%, rgba(76,29,149,.16), transparent 60%),
      var(--m-overlay);
    backdrop-filter: blur(8px);
    opacity:0;visibility:hidden;transition:.28s ease;
    z-index:1000;

    /* safe areas: evita que toquen bordes o componentes */
    padding: var(--safe-gap);
    padding-left: calc(var(--safe-gap) + env(safe-area-inset-left));
    padding-right: calc(var(--safe-gap) + env(safe-area-inset-right));
    padding-top: calc(var(--safe-gap) + env(safe-area-inset-top));
    padding-bottom: calc(var(--safe-gap) + env(safe-area-inset-bottom));
  }
  .modal[aria-hidden="false"]{opacity:1;visibility:visible}

  .modal-content{
    position:relative;
    background:linear-gradient(145deg,var(--m-bg-a),var(--m-bg-b));
    backdrop-filter: blur(16px) saturate(160%);
    border:1px solid var(--m-border);
    border-radius:22px;
    padding:0 0 8px;
    width:min(100%, 800px);  /* respeta los paddings del overlay */
    margin:0 auto;          /* centrado perfecto */
    color:var(--fg-1);
    box-shadow:
      0 25px 70px rgba(0,0,0,.65),
      0 0 0 1px rgba(255,255,255,.06) inset;
    max-height:calc(100vh - (var(--safe-gap)*2)); /* no choca arriba/abajo */
    overflow:auto; overflow-x:hidden;             /* sin scroll horizontal */
    transform:translateY(10px) scale(.98);opacity:.98;
    animation: modalIn .22s ease forwards;

    /* UX: evita arrastre lateral y saltos */
    touch-action: pan-y;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
  }
  .modal-content.xl{width:min(100%,1100px)}
  @keyframes modalIn{to{transform:translateY(0) scale(1);opacity:1}}

  .modal-content::before{
    content:"";position:absolute;inset:-1px;border-radius: inherit;padding:1px;
    background:linear-gradient(135deg,transparent, var(--m-border-strong), transparent 60%, rgba(255,255,255,.15));
    -webkit-mask:linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite:xor; mask-composite:exclude; pointer-events:none;
    opacity:.6;
  }

  .modal-header{
    position:sticky; top:0; z-index:2;
    display:flex;justify-content:space-between;align-items:center;gap:12px;
    padding:16px 18px;
    background:linear-gradient(180deg, rgba(26,18,42,.96), rgba(26,18,42,.86));
    border-bottom:1px solid rgba(199,183,255,.22);
    border-top-left-radius:22px;border-top-right-radius:22px;
  }
  .modal-header h3{
    display:flex;align-items:center;gap:10px;font-weight:900;
    background:linear-gradient(100deg,#ffffff,#eee6ff 70%,#c4b5fd);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  }

  /* grid sin “empujes” laterales */
  .modal-grid{
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);
    gap:16px;margin:14px 18px 8px
  }
  .modal-field{min-width:0}
  .modal-field.col-2{grid-column:span 2}

  .fieldset{border:1px dashed rgba(167,139,250,.35);border-radius:16px;padding:14px;background:rgba(124,58,237,.06);margin:12px 18px}
  .legend{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;border:1px solid rgba(167,139,250,.45);background:linear-gradient(135deg,#f5f3ff,#e9d5ff);color:#1a0b44;font-weight:900;font-size:.85rem;margin-bottom:10px}
  .badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:.78rem;font-weight:900}
  .badge.locked{background:linear-gradient(135deg,#ede9fe,#e9d5ff);color:#1a0b44;border:1px solid rgba(167,139,250,.5)}

  .modal-actions{
    position:sticky;bottom:0;z-index:2;
    display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;
    padding:12px 18px 14px;background:linear-gradient(180deg, rgba(26,18,42,.84), rgba(26,18,42,.96));
    border-top:1px solid rgba(199,183,255,.20);
    border-bottom-left-radius:22px;border-bottom-right-radius:22px;
  }

  /* Animaciones reveal */
  .a-reveal{opacity:0;transform:translateY(10px);transition:.5s}
  .a-reveal.in{opacity:1;transform:none}

  /* Documento de seguridad en modal */
  .secdoc{position:relative}
  .secdoc .particles{position:absolute;inset:0;pointer-events:none;z-index:-1}
  .secdoc .particle{position:absolute;width:2px;height:2px;background:linear-gradient(45deg,#ba55d3,#9932cc);border-radius:50%;opacity:.6;animation:floatParticle 20s linear infinite}
  @keyframes floatParticle{
    0%{transform:translateY(40vh) rotate(0deg);opacity:0}
    10%{opacity:.6}
    90%{opacity:.6}
    100%{transform:translateY(-60px) rotate(360deg);opacity:0}
  }
  .secdoc .container{padding:20px;max-width:1000px;margin:0 auto}
  .secdoc .security-header{text-align:center;margin:20px 0 30px}
  .secdoc .security-header h1{font-family:'Inter',system-ui,Arial,sans-serif;font-size:2.2rem;font-weight:800;background:linear-gradient(135deg,#ba55d3,#9932cc,#8a2be2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:12px}
  .secdoc .security-header p{font-family:'Inter',system-ui,Arial,sans-serif;font-size:1rem;color:rgba(255,255,255,.85);max-width:700px;margin:0 auto;line-height:1.6}
  .secdoc .privacy-policy{background:linear-gradient(145deg,rgba(26,26,26,.95),rgba(42,26,42,.8));backdrop-filter:blur(18px);padding:28px;border-radius:22px;border:1px solid rgba(138,43,226,.4);margin-bottom:24px}
  .secdoc .privacy-policy h2{color:#ba55d3;font-family:'Inter',system-ui,Arial,sans-serif;font-size:1.6rem;font-weight:700;margin-bottom:18px;text-align:center}
  .secdoc .privacy-section{margin-bottom:18px}
  .secdoc .privacy-section h3{color:#9932cc;font-family:'Inter',system-ui,Arial,sans-serif;font-size:1.05rem;margin-bottom:8px}
  .secdoc .privacy-section p{font-family:'Inter',system-ui,Arial,sans-serif;color:rgba(255,255,255,.9);line-height:1.6;font-size:.95rem}
  .secdoc .contact-security{text-align:center;padding:24px;background:linear-gradient(145deg,rgba(42,26,42,.8),rgba(26,26,26,.9));backdrop-filter:blur(18px);border-radius:22px;border:1px solid rgba(138,43,226,.4)}
  .secdoc .contact-security h3{color:#ba55d3;font-family:'Inter',system-ui,Arial,sans-serif;font-size:1.2rem;margin-bottom:10px}
  .secdoc .contact-security p{font-family:'Inter',system-ui,Arial,sans-serif;color:rgba(255,255,255,.85);margin-bottom:10px}
  .secdoc .security-email{color:#9932cc;font-weight:700;text-decoration:none}
  .secdoc .security-email:hover{color:#ba55d3}

  /* Scrollbars con estilo (body y contenedores de modal) */
  *{scrollbar-width:thin; scrollbar-color:#a78bfa rgba(255,255,255,.06)}
  body::-webkit-scrollbar,
  .modal-content::-webkit-scrollbar{width:10px;height:10px}
  body::-webkit-scrollbar-track,
  .modal-content::-webkit-scrollbar-track{background:rgba(255,255,255,.06);border-radius:16px}
  body::-webkit-scrollbar-thumb,
  .modal-content::-webkit-scrollbar-thumb{
    background:linear-gradient(180deg,#c4b5fd,#8b5cf6);
    border-radius:16px;border:2px solid rgba(20,12,40,.7)
  }
  body::-webkit-scrollbar-thumb:hover,
  .modal-content::-webkit-scrollbar-thumb:hover{
    background:linear-gradient(180deg,#e9d5ff,#a78bfa)
  }

  /* Toast */
  #auraToast{
    position:fixed;left:50%;bottom:28px;transform:translateX(-50%) translateY(8px);
    padding:12px 16px;border-radius:14px;z-index:2000;
    background:linear-gradient(135deg,#c4b5fd,#8b5cf6);
    color:#0f0a27;font-weight:800;border:1px solid rgba(255,255,255,.25);
    box-shadow:0 18px 50px rgba(124,58,237,.35);
    opacity:0;pointer-events:none;transition:opacity .25s, transform .25s;
  }
  #auraToast.show{opacity:1;transform:translateX(-50%) translateY(0)}
  body.modal-open{overflow:hidden}

/* ===== Notificaciones bonitas ===== */
.notify {
  position: fixed;
  top: 120px;
  left: 50%;
  transform: translateX(-50%) translateY(-20px);
  z-index: 9999;

  min-width: 320px;
  max-width: 600px;
  padding: 14px 18px 14px 48px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.95rem;
  text-align: left;
  box-shadow: 0 8px 24px rgba(0,0,0,.25);
  background: #fff;
  color: #222;

  opacity: 0;
  transition: opacity .5s ease, transform .5s ease;
}

/* Mostrar con animación */
.notify.show {
  opacity: 1;
  transform: translateX(-50%) translateY(0);
}

/* Icono dentro de la notificación */
.notify::before {
  font-family: "Font Awesome 6 Free";
  font-weight: 900;
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.2rem;
}

/* Éxito */
.notify.success {
  border-left: 6px solid #22c55e;
  background: linear-gradient(135deg,#ecfdf5,#d1fae5);
  color: #065f46;
}
.notify.success::before {
  content: "\f00c"; /* fa-check */
  color: #22c55e;
}

/* Error */
.notify.error {
  border-left: 6px solid #ef4444;
  background: linear-gradient(135deg,#fef2f2,#fee2e2);
  color: #7f1d1d;
}
.notify.error::before {
  content: "\f057"; /* fa-circle-xmark */
  color: #ef4444;
}

/* Info */
.notify.info {
  border-left: 6px solid #3b82f6;
  background: linear-gradient(135deg,#eff6ff,#dbeafe);
  color: #1e3a8a;
}
.notify.info::before {
  content: "\f05a"; /* fa-circle-info */
  color: #3b82f6;
}

</style>
</head>
<body>
<div id="page-account">

  {{-- 🔔 Notificación --}}
  @if(session('status') === 'support-sent')
  <div class="notify success">✅ Tu mensaje fue enviado al equipo de soporte.</div>
@endif

  @if(session('status') === 'password-updated')
    <div class="notify success">{{ __('account.success_pass') }}</div>
  @endif

  @if($errors->any())
    <div class="notify error">{{ __('account.error_pass') }}</div>
  @endif

  @include('components.sidebar')
  @include('components.traductor')
  @include('components.header')
  @include('components.fondo')

  <main class="main-content">
    <section class="account-grid">

      <!-- HERO -->
      <div class="account-hero pref-hero a-reveal">
        <div class="hero-wrap">
          <div class="hero-banner" data-hires="{{ $bannerHigh }}" style="background-image:url('{{ $bannerLow }}')"></div>
          <div class="hero-scrim"></div><div class="hero-border"></div>

          <div class="hero-head">
            <div class="text-shield"><h1 class="hero-title">{{ __('account.title') }}</h1></div>
            <div class="text-shield" style="padding:8px 14px"><p class="hero-sub">{{ __('account.subtitle') }}</p></div>
          </div>

          <div class="hero-meta">
            <span class="meta-chip"><i class="fa-solid fa-user"></i> {{ $nombre }}</span>
            <span class="meta-chip"><i class="fa-solid fa-envelope"></i> {{ $correo }}</span>
            @if($miembroDesde)<span class="meta-chip"><i class="fa-solid fa-calendar-check"></i> {{ __('account.member_since') }} {{ $miembroDesde }}</span>@endif
          </div>
        </div>

        <div class="avatar-wrap">
          @php
            $avatarSrc = $u && $u->avatar ? $media($u->avatar) : null;
          @endphp
          @if($avatarSrc)
            <img id="avatarPreviewLive" class="avatar-img" src="{{ $avatarSrc }}?v={{ time() }}" alt="{{ $nombre }}">
          @else
            <div class="avatar-fallback">{{ strtoupper(substr($nombre,0,1)) }}</div>
          @endif
        </div>
      </div>

      <!-- ACCIONES -->
      <section class="quick-actions a-reveal">
        <div class="actions-grid">
          <article class="card">
            <h3><i class="fa-solid fa-user-pen"></i> {{ __('account.edit') }}</h3>
            <p class="muted">{{ __('account.edit_desc') }}</p>
            <div class="card-actions">
              <button class="btn btn-primary" data-open="#modalEditProfile"><i class="fa-solid fa-pen-to-square"></i> {{ __('account.open_editor') }}</button>
            </div>
          </article>

          <article class="card">
            <h3><i class="fa-solid fa-user-gear"></i> {{ __('account.switch') }}</h3>
            <p class="muted">{{ __('account.switch_desc') }}</p>
            <div class="card-actions">
              <button class="btn btn-secondary" data-open="#modalSwitchRole"><i class="fa-solid fa-person-rays"></i> {{ __('account.switch') }}</button>
            </div>
          </article>

          <article class="card">
            <h3><i class="fa-solid fa-lock"></i> {{ __('account.password') }}</h3>
            <p class="muted">{{ __('account.password_desc') }}</p>
            <div class="card-actions">
              <button class="btn btn-secondary" data-open="#modalPassword"><i class="fa-solid fa-shield-halved"></i> {{ __('account.manage') }}</button>
            </div>
          </article>
        </div>
      </section>

      <!-- SEGURIDAD -->
      <section class="security a-reveal">
        <article class="card">
          <h3><i class="fa-solid fa-shield-halved"></i> {{ __('account.security') }}</h3>
          <p class="muted">{{ __('account.security_desc') }}</p>
          <div class="card-actions">
            <button class="btn btn-secondary" data-open="#modalSecurityInfo"><i class="fa-solid fa-lock-keyhole"></i> {{ __('account.manage') }}</button>
          </div>
        </article>
      </section>

      <!-- IDIOMA -->
      <section class="language a-reveal">
        <article class="card">
          <h3><i class="fa-solid fa-language"></i> {{ __('account.language') }}</h3>
          <p class="muted">{{ __('account.language_desc') }}</p>
          <div class="card-actions">
            <button class="btn btn-ghost" data-open="#modalLanguage"><i class="fa-solid fa-globe"></i> {{ __('account.choose_lang') }}</button>
          </div>
        </article>
      </section>

      <!-- SOPORTE -->
      <section class="support a-reveal">
        <article class="card">
          <h3><i class="fa-solid fa-headset"></i> {{ __('account.support') }}</h3>
          <p class="muted">{{ __('account.support_desc') }}</p>
          <div class="card-actions">
            <button class="btn btn-primary" data-open="#modalSupport"><i class="fa-solid fa-envelope-open-text"></i> {{ __('account.contact') }}</button>
          </div>
        </article>
      </section>

    </section>
  </main>

  @include('components.footer')

  @if(Auth::check())

  <!-- MODALES -->
  <!-- Editar perfil -->
<div class="modal" id="modalEditProfile" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fa-solid fa-user-pen"></i> {{ __('account.edit') }}</h3>
      <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form action="{{ $actionUpdate }}" method="POST" enctype="multipart/form-data" id="formEditProfile" data-demo="{{ $actionUpdate==='#' ? '1' : '0' }}">
      @csrf

      <div class="fieldset">
        <span class="legend"><i class="fa-solid fa-user"></i> {{ __('account.identity') }}</span>
        <div class="modal-grid">
          <div class="modal-field">
            <label class="label"><i class="fa-solid fa-id-badge"></i> {{ __('account.current_artist_name') }}</label>
            <input class="form-ctrl" type="text" value="{{ $nombre }}" readonly>
          </div>
          <div class="modal-field">
            <label class="label"><i class="fa-solid fa-pen"></i> {{ __('account.new_artist_name') }}</label>
            <div class="input-wrap">
              <i class="fa-solid fa-user left-ico"></i>
              <input class="form-ctrl with-ico" name="nuevo_nombre_artistico" type="text" placeholder="{{ __('account.new_artist_name_ph') }}">
            </div>
          </div>
        </div>
      </div>

      <div class="fieldset" style="margin-top:14px">
        <span class="legend"><i class="fa-solid fa-at"></i> {{ __('account.email') }}</span>
        <div class="modal-grid">
          <div class="modal-field col-2">
            <label class="label"><i class="fa-solid fa-lock lock"></i> {{ __('account.current_email') }} <span class="badge locked"><i class="fa-solid fa-lock"></i> {{ __('account.locked') }}</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-envelope left-ico"></i>
              <input class="form-ctrl with-ico" type="email" value="{{ $u->email ?? '' }}" disabled>
            </div>
          </div>
          <div class="modal-field">
            <label class="label"><i class="fa-solid fa-envelope-open-text"></i> {{ __('account.new_email') }}</label>
            <div class="input-wrap">
              <i class="fa-solid fa-envelope left-ico"></i>
              <input class="form-ctrl with-ico" name="email" id="newEmail" type="email" placeholder="tu-nuevo@email.com" autocomplete="email">
            </div>
          </div>
          <div class="modal-field">
            <label class="label"><i class="fa-solid fa-check-double"></i> {{ __('account.confirm_new_email') }}</label>
            <div class="input-wrap">
              <i class="fa-solid fa-envelope left-ico"></i>
              <input class="form-ctrl with-ico" id="newEmailConfirm" type="email" placeholder="{{ __('account.confirm_new_email_ph') }}" autocomplete="email">
            </div>
          </div>
        </div>
      </div>

      <div class="fieldset" style="margin-top:14px">
        <span class="legend"><i class="fa-solid fa-image"></i> {{ __('account.images_bio') }}</span>
        <div class="modal-grid">
          <div class="modal-field">
            <label class="label"><i class="fa-solid fa-camera"></i> {{ __('account.avatar') }}</label>
            <label class="file-preview avatar">
              <img id="avatarPreview" src="{{ $u && $u->avatar ? $media($u->avatar).'?v='.time() : '' }}" alt="">
              <input type="file" id="avatarInput" name="avatar" accept="image/*" hidden>
              <div class="overlay"><i class="fa-solid fa-camera"></i></div>
            </label>
          </div>
          <div class="modal-field">
            <label class="label"><i class="fa-solid fa-image"></i> {{ __('account.banner') }}</label>
            <label class="file-preview banner">
              <img id="bannerPreview" src="{{ $u && $u->banner ? $media($u->banner).'?v='.time() : '' }}" alt="">
              <input type="file" id="bannerInput" name="banner" accept="image/*" hidden>
              <div class="overlay"><i class="fa-solid fa-camera"></i></div>
            </label>
          </div>
          <div class="modal-field col-2">
            <label class="label"><i class="fa-solid fa-comment"></i> {{ __('account.bio') }}</label>
            <textarea class="form-ctrl" name="bio" rows="4" placeholder="{{ __('account.bio_ph') }}">{{ $u->biografia ?? '' }}</textarea>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> {{ __('account.save') }}</button>
        <button type="button" class="btn btn-secondary" data-close><i class="fa-solid fa-xmark"></i> {{ __('account.cancel') }}</button>
      </div>
    </form>
  </div>
</div>

<!-- Cambiar rol -->
<div class="modal" id="modalSwitchRole" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fa-solid fa-user-gear"></i> {{ __('account.switch_role') }}</h3>
      <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form id="formSwitchRole" action="{{ route('perfil.toggleRole') }}" method="POST">
      @csrf
      <div class="fieldset">
        <span class="legend"><i class="fa-solid fa-arrows-rotate"></i> {{ __('account.choose_mode') }}</span>
        <div class="modal-grid">
          <label class="modal-field field" style="cursor:pointer">
            <span class="label"><i class="fa-solid fa-microphone"></i> {{ __('account.artist') }}</span>
            <input type="radio" name="modo" value="artista" {{ auth()->user()->es_artista ? 'checked' : '' }}>
            <small class="muted">{{ __('account.artist_desc') }}</small>
          </label>
          <label class="modal-field field" style="cursor:pointer">
            <span class="label"><i class="fa-solid fa-user"></i> {{ __('account.user') }}</span>
            <input type="radio" name="modo" value="usuario" {{ !auth()->user()->es_artista ? 'checked' : '' }}>
            <small class="muted">{{ __('account.user_desc') }}</small>
          </label>
        </div>
      </div>

      @if(auth()->user()->es_artista)
        <div class="fieldset" style="margin-top:14px;">
          <span class="legend"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('account.warning') }}</span>
          <p style="color:#f87171;font-weight:700;margin:0;">
            ⚠ {{ __('account.warning_artist_to_user') }}
          </p>
          <input type="hidden" name="confirmar" value="1">
        </div>
      @endif

      <div class="modal-actions">
        <button id="btnConfirmRole" class="btn btn-primary" type="button"><i class="fa-solid fa-rotate"></i> {{ __('account.change') }}</button>
        <button class="btn btn-secondary" type="button" data-close>{{ __('account.cancel') }}</button>
      </div>
    </form>
  </div>
</div>
<!-- Modal ingresar nombre artístico -->
<div class="modal" id="modalArtistName" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fa-solid fa-microphone"></i> {{ __('account.artist_name') }}</h3>
      <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form action="{{ route('perfil.toggleRole') }}" method="POST">
      @csrf
      <input type="hidden" name="modo" value="artista">
      <div class="fieldset">
        <span class="legend"><i class="fa-solid fa-pen"></i> {{ __('account.enter_artist_name') }}</span>
        <div class="modal-grid">
          <div class="modal-field col-2">
            <input class="form-ctrl" name="nombre_artistico" type="text" placeholder="{{ __('account.artist_name_ph') }}" required>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-check"></i> {{ __('account.confirm') }}</button>
        <button class="btn btn-secondary" type="button" data-close>{{ __('account.cancel') }}</button>
      </div>
    </form>
  </div>
</div>
  <!-- Contraseña -->
<div class="modal" id="modalPassword" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fa-solid fa-lock"></i> {{ __('account.change_password') }}</h3>
      <button type="button" class="btn btn-ghost" data-close>
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="{{ route('perfil.changePassword') }}" method="POST" id="formPassword">
      @csrf
      <div class="modal-grid">
        
        <!-- Contraseña actual -->
        <div class="modal-field col-2">
          <label class="label"><i class="fa-solid fa-key"></i> {{ __('account.current_password') }}</label>
          <div class="input-wrap">
            <i class="fa-solid fa-lock left-ico"></i>
            <input class="form-ctrl with-ico" type="password" name="current_password" placeholder="••••••••" autocomplete="current-password" required>
            <button type="button" class="toggle-pass"><i class="fa-solid fa-eye"></i></button>
          </div>
          @error('current_password')
            <small style="color:#f87171; font-weight:700;">{{ $message }}</small>
          @enderror
        </div>

        <!-- Nueva contraseña -->
        <div class="modal-field">
          <label class="label"><i class="fa-solid fa-shield-keyhole"></i> {{ __('account.new_password') }}</label>
          <div class="input-wrap">
            <i class="fa-solid fa-shield-halved left-ico"></i>
            <input class="form-ctrl with-ico" type="password" id="newPass" name="password" placeholder="{{ __('account.new_password_ph') }}" autocomplete="new-password" required>
            <button type="button" class="toggle-pass"><i class="fa-solid fa-eye"></i></button>
          </div>
          @error('password')
            <small style="color:#f87171; font-weight:700;">{{ $message }}</small>
          @enderror
        </div>

        <!-- Confirmar nueva -->
        <div class="modal-field">
          <label class="label"><i class="fa-solid fa-check-double"></i> {{ __('account.confirm_new_password') }}</label>
          <div class="input-wrap">
            <i class="fa-solid fa-shield-halved left-ico"></i>
            <input class="form-ctrl with-ico" type="password" name="password_confirmation" id="newPass2" placeholder="{{ __('account.confirm_new_password_ph') }}" autocomplete="new-password" required>
            <button type="button" class="toggle-pass"><i class="fa-solid fa-eye"></i></button>
          </div>
        </div>

        <div class="modal-field col-2">
          <small class="muted" id="pwMsg"></small>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> {{ __('account.save') }}</button>
        <button class="btn btn-secondary" type="button" data-close>{{ __('account.cancel') }}</button>
      </div>
    </form>
  </div>
</div>
<!-- Idioma -->
<div class="modal" id="modalLanguage" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fa-solid fa-language"></i> {{ __('account.language') }}</h3>
      <button type="button" class="btn btn-ghost" data-close>
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="modal-grid">
      {{-- Botón Español --}}
      <form action="{{ route('languages', 'es') }}" method="POST">
        @csrf
        <button type="submit"
          class="btn {{ app()->getLocale() === 'es' ? 'btn-primary' : 'btn-secondary' }}">
          <i class="fa-solid fa-flag"></i> {{ __('account.spanish') }}
          @if(app()->getLocale() === 'es')
            <i class="fa-solid fa-check" style="margin-left:6px"></i>
          @endif
        </button>
      </form>

      {{-- Botón Inglés --}}
      <form action="{{ route('languages', 'en') }}" method="POST">
        @csrf
        <button type="submit"
          class="btn {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-secondary' }}">
          <i class="fa-solid fa-flag-usa"></i> {{ __('account.english') }}
          @if(app()->getLocale() === 'en')
            <i class="fa-solid fa-check" style="margin-left:6px"></i>
          @endif
        </button>
      </form>
    </div>

    <div class="modal-actions">
      <button class="btn btn-secondary" type="button" data-close>
        {{ __('account.close') }}
      </button>
    </div>
  </div>
</div>


<!-- Modal Soporte -->
<div class="modal" id="modalSupport" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-content">
    
    <!-- Encabezado -->
    <div class="modal-header">
      <h3>
        <i class="fa-solid fa-headset"></i>
        {{ __('account.contact_support') }}
      </h3>
      <button type="button" class="btn btn-ghost" data-close aria-label="{{ __('account.close') }}">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Formulario -->
    <form 
      action="{{ $actionSupport }}" 
      method="POST" 
      id="formSupport" 
      data-demo="{{ $actionSupport==='#' ? '1' : '0' }}"
    >
      @csrf

      <div class="fieldset">
        <span class="legend">
          <i class="fa-solid fa-envelope"></i>
          {{ __('account.support_request') }}
        </span>

        <div class="modal-grid">
          <!-- Asunto -->
          <div class="modal-field col-2">
            <label class="label" for="supportSubject">
              <i class="fa-solid fa-heading"></i>
              {{ __('account.subject') }}
            </label>
            <input 
              id="supportSubject"
              class="form-ctrl"
              type="text"
              name="subject"
              placeholder="{{ __('account.subject_ph') }}"
              maxlength="255"
              required
            >
          </div>

          <!-- Mensaje -->
          <div class="modal-field col-2">
            <label class="label" for="supportMessage">
              <i class="fa-solid fa-message"></i>
              {{ __('account.message') }}
            </label>
            <textarea 
              id="supportMessage"
              class="form-ctrl"
              name="message"
              rows="6"
              maxlength="2000"
              placeholder="{{ __('account.message_ph') }}"
              required
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Acciones -->
      <div class="modal-actions">
        <button class="btn btn-primary" type="submit">
          <i class="fa-solid fa-paper-plane"></i>
          {{ __('account.send') }}
        </button>
        <button class="btn btn-secondary" type="button" data-close>
          <i class="fa-solid fa-xmark"></i>
          {{ __('account.cancel') }}
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Seguridad (doc) -->
<div class="modal" id="modalSecurityInfo" aria-hidden="true">
  <div class="modal-content xl">
    <div class="modal-header">
      <h3><i class="fa-solid fa-shield-alt"></i> {{ __('account.security_privacy') }}</h3>
      <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="secdoc">
      <div class="particles" id="secParticles"></div>
      <div class="container">
        <div class="security-header">
          <h1><i class="fas fa-shield-alt"></i> {{ __('account.security_privacy') }}</h1>
          <p>{{ __('account.security_intro') }}</p>
        </div>

        <div class="privacy-policy">
          <h2><i class="fas fa-file-contract"></i> {{ __('account.privacy_policy') }}</h2>

          <div class="privacy-section">
            <h3>{{ __('account.info_collection_title') }}</h3>
            <p>{{ __('account.info_collection_text') }}</p>
          </div>

          <div class="privacy-section">
            <h3>{{ __('account.info_use_title') }}</h3>
            <p>{{ __('account.info_use_text') }}</p>
          </div>

          <div class="privacy-section">
            <h3>{{ __('account.info_share_title') }}</h3>
            <p>{{ __('account.info_share_text') }}</p>
          </div>

          <div class="privacy-section">
            <h3>{{ __('account.data_retention_title') }}</h3>
            <p>{{ __('account.data_retention_text') }}</p>
          </div>

          <div class="privacy-section">
            <h3>{{ __('account.your_rights_title') }}</h3>
            <p>{{ __('account.your_rights_text') }}</p>
          </div>
        </div>

        <div class="contact-security">
          <h3><i class="fas fa-headset"></i> {{ __('account.security_contact') }}</h3>
          <p>{{ __('account.security_contact_text') }}</p>
          <p>Email: <a href="mailto:aura@gmail.com" class="security-email">aura@gmail.com</a></p>
          <p><strong>{{ __('account.security_response') }}</strong></p>
          <p>{{ __('account.security_available') }}</p>
        </div>
      </div>
    </div>

    <div class="modal-actions">
      <button class="btn btn-secondary" type="button" data-close>{{ __('account.close') }}</button>
    </div>
  </div>
</div>

  @endif
</div>

{{-- Éxito --}}
@if(session('status') === 'password-updated')
  <script>auraToast("✅ Tu contraseña se actualizó correctamente.");</script>
@endif

{{-- ❌ Errores --}}
@if($errors->has('current_password'))
  <script>auraToast("❌ {{ $errors->first('current_password') }}");</script>
@endif

@if($errors->has('password'))
  <script>auraToast("❌ {{ $errors->first('password') }}");</script>
@endif

@if($errors->any() && !$errors->has('current_password') && !$errors->has('password'))
  <script>auraToast("❌ No se pudo actualizar la contraseña, revisa los datos.");</script>
@endif
<script>
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
    a = document.getElementById('newPass2')?.value || '';
    const msg = document.getElementById('pwMsg');
    if(p1 !== a){ e.preventDefault(); if(msg) msg.textContent='Las contraseñas no coinciden.'; auraToast('Las contraseñas no coinciden.'); }
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

// Animación automática
document.querySelectorAll('.notify').forEach(el => {
  requestAnimationFrame(() => el.classList.add('show')); // fade-in

  setTimeout(() => {
    el.classList.remove('show'); // fade-out
    setTimeout(() => el.remove(), 600); // espera animación
  }, 3000);
});

document.getElementById('btnConfirmRole')?.addEventListener('click', ()=>{
  const isArtist = {{ auth()->user()->es_artista ? 'true' : 'false' }};
  const artistRadio = document.querySelector('input[name="modo"][value="artista"]');
  const userRadio   = document.querySelector('input[name="modo"][value="usuario"]');

  if(!isArtist && artistRadio?.checked){
    // Usuario → Artista → pedir nombre artístico
    document.querySelector('#modalSwitchRole')?.setAttribute('aria-hidden','true');
    document.querySelector('#modalArtistName')?.setAttribute('aria-hidden','false');
  } else {
    // Enviar el form original (usuario → usuario o artista → usuario)
    document.querySelector('#modalSwitchRole form')?.submit();
  }
});
</script>
</body>
</html>
