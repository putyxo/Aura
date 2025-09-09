<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cuenta — Aura</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

@php
  $u = auth()->user() ?? $user ?? null;

  $bannerLow  = $u && $u->banner ? drive_img_url($u->banner, 640)  . '&v=' . time() : asset('img/default-banner.jpg');
  $bannerHigh = $u && $u->banner ? drive_img_url($u->banner, 1920) . '&v=' . time() : asset('img/default-banner.jpg');

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
</style>
</head>
<body>
<div id="page-account">
  @include('components.sidebar')
  @include('components.traductor')
  @include('components.header')
  @include('components.fondo')

  <main class="main-content">
    <section class="account-grid">

      <!-- ===== HERO ===== -->
      <div class="account-hero pref-hero a-reveal">
        <div class="hero-wrap">
          <div class="hero-banner" data-hires="{{ $bannerHigh }}" style="background-image:url('{{ $bannerLow }}')"></div>
          <div class="hero-scrim"></div><div class="hero-border"></div>

          <div class="hero-head">
            <div class="text-shield"><h1 class="hero-title">Tu cuenta</h1></div>
            <div class="text-shield" style="padding:8px 14px"><p class="hero-sub">Gestiona tu perfil, seguridad y preferencias</p></div>
          </div>

          <div class="hero-meta">
            <span class="meta-chip"><i class="fa-solid fa-user"></i> {{ $nombre }}</span>
            <span class="meta-chip"><i class="fa-solid fa-envelope"></i> {{ $correo }}</span>
            @if($miembroDesde)<span class="meta-chip"><i class="fa-solid fa-calendar-check"></i> Miembro desde {{ $miembroDesde }}</span>@endif
          </div>
        </div>

        <div class="avatar-wrap">
          @if($u && $u->avatar)
            <img id="avatarPreviewLive" class="avatar-img" src="{{ drive_img_url($u->avatar, 500) }}&v={{ time() }}" alt="{{ $nombre }}" loading="lazy" decoding="async">
          @else
            <div class="avatar-fallback">{{ strtoupper(substr($nombre,0,1)) }}</div>
          @endif
        </div>
      </div>

      <!-- ===== ACCIONES RÁPIDAS ===== -->
      <section class="quick-actions a-reveal">
        <div class="actions-grid">
          <article class="card">
            <h3><i class="fa-solid fa-user-pen"></i> Editar perfil</h3>
            <p class="muted">Actualiza tu nombre, correo, avatar y banner.</p>
            <div class="card-actions">
              @auth
                <button class="btn btn-primary" data-open="#modalEditProfile"><i class="fa-solid fa-pen-to-square"></i> Abrir editor</button>
              @else
                <a class="btn btn-primary" href="{{ url('/login') }}"><i class="fa-solid fa-right-to-bracket"></i> Inicia sesión</a>
              @endauth
            </div>
          </article>

          <article class="card">
            <h3><i class="fa-solid fa-user-gear"></i> Cambiar a artista/usuario</h3>
            <p class="muted">Alterna el modo de tu cuenta.</p>
            <div class="card-actions">
              <button class="btn btn-secondary" data-open="#modalSwitchRole"><i class="fa-solid fa-person-rays"></i> Cambiar</button>
            </div>
          </article>

          <article class="card">
            <h3><i class="fa-solid fa-lock"></i> Contraseña & seguridad</h3>
            <p class="muted">Cambia tu contraseña y revisa opciones básicas.</p>
            <div class="card-actions">
              <button class="btn btn-secondary" data-open="#modalPassword"><i class="fa-solid fa-shield-halved"></i> Gestionar</button>
            </div>
          </article>
        </div>
      </section>

      <!-- ===== HERRAMIENTAS ===== -->
      <section class="tools a-reveal">
        <article class="card">
          <h3><i class="fa-solid fa-star"></i> Verificación</h3>
          <p class="muted">Solicita la verificación para mayor visibilidad.</p>
          <div class="tool-icons" style="display:flex;gap:12px;flex-wrap:wrap;margin-top:6px">
            <button class="btn btn-ghost" data-open="#modalVerify"><i class="fa-solid fa-badge-check"></i> Verificar</button>
            <button class="btn btn-ghost" data-open="#modalCertificates"><i class="fa-solid fa-certificate"></i> Certificados</button>
          </div>
          <div class="card-actions">
            <button class="btn btn-primary" data-open="#modalVerify"><i class="fa-solid fa-check-double"></i> Solicitar</button>
          </div>
        </article>
      </section>

      <!-- ===== SEGURIDAD ===== -->
      <section class="security a-reveal">
        <article class="card">
          <h3><i class="fa-solid fa-shield-halved"></i> Seguridad</h3>
          <p class="muted">Gestiona inicio de sesión, dispositivos y sesiones activas.</p>
          <div class="card-actions">
            <button class="btn btn-secondary" data-open="#modalSecurityInfo"><i class="fa-solid fa-lock-keyhole"></i> Abrir seguridad</button>
          </div>
        </article>
      </section>

      <!-- ===== IDIOMA ===== -->
      <section class="language a-reveal">
        <article class="card">
          <h3><i class="fa-solid fa-language"></i> Idioma</h3>
          <p class="muted">Selecciona tu idioma preferido en Aura.</p>
          <div class="card-actions">
            <button class="btn btn-ghost" data-open="#modalLanguage"><i class="fa-solid fa-globe"></i> Elegir idioma</button>
          </div>
        </article>
      </section>

      <!-- ===== SOPORTE ===== -->
      <section class="support a-reveal">
        <article class="card">
          <h3><i class="fa-solid fa-headset"></i> Soporte</h3>
          <p class="muted">¿Necesitas ayuda? Escríbenos, estamos para ayudarte.</p>
          <div class="card-actions">
            <button class="btn btn-primary" data-open="#modalSupport"><i class="fa-solid fa-envelope-open-text"></i> Contactar</button>
          </div>
        </article>
      </section>

    </section>
  </main>

  @include('components.footer')

  @if(Auth::check())

  <!-- ===== MODALES (todos) ===== -->
  <!-- Editar perfil -->
  <div class="modal" id="modalEditProfile" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fa-solid fa-user-pen"></i> Editar perfil</h3>
        <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
      </div>

      <form action="{{ $actionUpdate }}" method="POST" enctype="multipart/form-data" id="formEditProfile" data-demo="{{ $actionUpdate==='#' ? '1' : '0' }}">
        @csrf

        <div class="fieldset">
          <span class="legend"><i class="fa-solid fa-user"></i> Identidad</span>
          <div class="modal-grid">
            <div class="modal-field">
              <label class="label"><i class="fa-solid fa-id-badge"></i> Nombre artístico actual</label>
              <input class="form-ctrl" type="text" value="{{ $nombre }}" readonly>
            </div>
            <div class="modal-field">
              <label class="label"><i class="fa-solid fa-pen"></i> Nuevo nombre artístico</label>
              <div class="input-wrap">
                <i class="fa-solid fa-user left-ico"></i>
                <input class="form-ctrl with-ico" name="nuevo_nombre_artistico" type="text" placeholder="Escribe el nuevo nombre artístico">
              </div>
            </div>
          </div>
        </div>

        <div class="fieldset" style="margin-top:14px">
          <span class="legend"><i class="fa-solid fa-at"></i> Correo electrónico</span>
          <div class="modal-grid">
            <div class="modal-field col-2">
              <label class="label"><i class="fa-solid fa-lock lock"></i> Correo actual <span class="badge locked"><i class="fa-solid fa-lock"></i> Bloqueado</span></label>
              <div class="input-wrap">
                <i class="fa-solid fa-envelope left-ico"></i>
                <input class="form-ctrl with-ico" type="email" value="{{ $u->email ?? '' }}" disabled>
              </div>
            </div>
            <div class="modal-field">
              <label class="label"><i class="fa-solid fa-envelope-open-text"></i> Nuevo correo</label>
              <div class="input-wrap">
                <i class="fa-solid fa-envelope left-ico"></i>
                <input class="form-ctrl with-ico" name="email" id="newEmail" type="email" placeholder="tu-nuevo@email.com" autocomplete="email">
              </div>
            </div>
            <div class="modal-field">
              <label class="label"><i class="fa-solid fa-check-double"></i> Confirmar nuevo correo</label>
              <div class="input-wrap">
                <i class="fa-solid fa-envelope left-ico"></i>
                <input class="form-ctrl with-ico" id="newEmailConfirm" type="email" placeholder="Repite el nuevo correo" autocomplete="email">
              </div>
            </div>
          </div>
        </div>

        <div class="fieldset" style="margin-top:14px">
          <span class="legend"><i class="fa-solid fa-image"></i> Imágenes y bio</span>
          <div class="modal-grid">
            <div class="modal-field">
              <label class="label"><i class="fa-solid fa-camera"></i> Foto de perfil</label>
              <label class="file-preview avatar">
                <img id="avatarPreview" src="{{ $u && $u->avatar ? drive_img_url($u->avatar, 500).'&v='.time() : '' }}" alt="">
                <input type="file" id="avatarInput" name="avatar" accept="image/*" hidden>
                <div class="overlay"><i class="fa-solid fa-camera"></i></div>
              </label>
            </div>
            <div class="modal-field">
              <label class="label"><i class="fa-solid fa-image"></i> Banner</label>
              <label class="file-preview banner">
                <img id="bannerPreview" src="{{ $u && $u->banner ? drive_img_url($u->banner, 1200).'&v='.time() : '' }}" alt="">
                <input type="file" id="bannerInput" name="banner" accept="image/*" hidden>
                <div class="overlay"><i class="fa-solid fa-camera"></i></div>
              </label>
            </div>
            <div class="modal-field col-2">
              <label class="label"><i class="fa-solid fa-comment"></i> Descripción</label>
              <textarea class="form-ctrl" name="bio" rows="4" placeholder="Escribe una breve biografía...">{{ $u->biografia ?? '' }}</textarea>
            </div>
          </div>
        </div>

        <div class="modal-actions">
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
          <button type="button" class="btn btn-secondary" data-close><i class="fa-solid fa-xmark"></i> Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Cambiar rol -->
  <div class="modal" id="modalSwitchRole" aria-hidden="true">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fa-solid fa-user-gear"></i> Cambiar tipo de cuenta</h3>
        <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form action="{{ $actionToggleRole }}" method="POST" data-demo="{{ $actionToggleRole==='#' ? '1' : '0' }}">
        @csrf
        <div class="fieldset">
          <span class="legend"><i class="fa-solid fa-arrows-rotate"></i> Selecciona modo</span>
          <div class="modal-grid">
            <label class="modal-field field" style="cursor:pointer">
              <span class="label"><i class="fa-solid fa-microphone"></i> Artista</span>
              <input type="radio" name="modo" value="artista"> <small class="muted">Sube música y gestiona lanzamientos.</small>
            </label>
            <label class="modal-field field" style="cursor:pointer">
              <span class="label"><i class="fa-solid fa-user"></i> Usuario</span>
              <input type="radio" name="modo" value="usuario"> <small class="muted">Escucha y crea playlists.</small>
            </label>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-rotate"></i> Cambiar</button>
          <button class="btn btn-secondary" type="button" data-close>Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Contraseña -->
  <div class="modal" id="modalPassword" aria-hidden="true">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fa-solid fa-lock"></i> Cambiar contraseña</h3>
        <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form action="{{ $actionChangePass }}" method="POST" id="formPassword" data-demo="{{ $actionChangePass==='#' ? '1' : '0' }}">
        @csrf
        <div class="modal-grid">
          <div class="modal-field col-2">
            <label class="label"><i class="fa-solid fa-key"></i> Contraseña actual</label>
            <div class="input-wrap"><i class="fa-solid fa-lock left-ico"></i>
              <input class="form-ctrl with-ico" type="password" name="current_password" placeholder="••••••••" autocomplete="current-password" required>
            </div>
          </div>
          <div class="modal-field">
            <label class="label"><i class="fa-solid fa-shield-keyhole"></i> Nueva contraseña</label>
            <div class="input-wrap"><i class="fa-solid fa-shield-halved left-ico"></i>
              <input class="form-ctrl with-ico" type="password" id="newPass" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
            </div>
          </div>
          <div class="modal-field">
            <label class="label"><i class="fa-solid fa-check-double"></i> Confirmar nueva</label>
            <div class="input-wrap"><i class="fa-solid fa-shield-halved left-ico"></i>
              <input class="form-ctrl with-ico" type="password" id="newPass2" placeholder="Repite la contraseña" autocomplete="new-password" required>
            </div>
          </div>
          <div class="modal-field col-2"><small class="muted" id="pwMsg"></small></div>
        </div>
        <div class="modal-actions">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-save"></i> Guardar</button>
          <button class="btn btn-secondary" type="button" data-close>Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Idioma -->
  <div class="modal" id="modalLanguage" aria-hidden="true">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fa-solid fa-language"></i> Idioma</h3>
        <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form action="{{ $actionLanguage }}" method="POST" data-demo="{{ $actionLanguage==='#' ? '1' : '0' }}">
        @csrf
        <div class="modal-grid">
          <button type="submit" name="lang" value="es" class="btn btn-primary"><i class="fa-solid fa-flag"></i> Español</button>
          <button type="submit" name="lang" value="en" class="btn btn-secondary"><i class="fa-solid fa-flag-usa"></i> English</button>
        </div>
        <div class="modal-actions">
          <button class="btn btn-secondary" type="button" data-close>Cerrar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Soporte -->
  <div class="modal" id="modalSupport" aria-hidden="true">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fa-solid fa-headset"></i> Contactar soporte</h3>
        <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form action="{{ $actionSupport }}" method="POST" id="formSupport" data-demo="{{ $actionSupport==='#' ? '1' : '0' }}">
        @csrf
        <div class="modal-grid">
          <div class="modal-field col-2">
            <label class="label"><i class="fa-solid fa-heading"></i> Asunto</label>
            <input class="form-ctrl" type="text" name="subject" placeholder="Escribe un asunto claro" required>
          </div>
          <div class="modal-field col-2">
            <label class="label"><i class="fa-solid fa-message"></i> Mensaje</label>
            <textarea class="form-ctrl" name="message" rows="5" placeholder="Describe tu problema o consulta..." required></textarea>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
          <button class="btn btn-secondary" type="button" data-close>Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Verificación -->
  <div class="modal" id="modalVerify" aria-hidden="true">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fa-solid fa-badge-check"></i> Solicitar verificación</h3>
        <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form action="{{ $actionVerify }}" method="POST" data-demo="{{ $actionVerify==='#' ? '1' : '0' }}">
        @csrf
        <div class="modal-grid">
          <div class="modal-field col-2">
            <label class="label"><i class="fa-solid fa-user"></i> Nombre público</label>
            <input class="form-ctrl" type="text" name="public_name" value="{{ $nombre }}">
          </div>
          <div class="modal-field col-2">
            <label class="label"><i class="fa-solid fa-link"></i> Enlaces (web/redes)</label>
            <textarea class="form-ctrl" name="links" rows="4" placeholder="Agrega enlaces que confirmen tu identidad"></textarea>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Enviar solicitud</button>
          <button class="btn btn-secondary" type="button" data-close>Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Certificados -->
  <div class="modal" id="modalCertificates" aria-hidden="true">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fa-solid fa-certificate"></i> Certificados</h3>
        <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="fieldset">
        <span class="legend"><i class="fa-solid fa-list"></i> Tus certificados</span>
        <p class="muted">Aquí aparecerán tus certificados emitidos por Aura. (Vista demo)</p>
      </div>
      <div class="modal-actions">
        <button class="btn btn-secondary" type="button" data-close>Cerrar</button>
      </div>
    </div>
  </div>

  <!-- Seguridad (doc) -->
  <div class="modal" id="modalSecurityInfo" aria-hidden="true">
    <div class="modal-content xl">
      <div class="modal-header">
        <h3><i class="fa-solid fa-shield-alt"></i> Seguridad y Privacidad</h3>
        <button type="button" class="btn btn-ghost" data-close><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="secdoc">
        <div class="particles" id="secParticles"></div>
        <div class="container">
          <div class="security-header">
            <h1><i class="fas fa-shield-alt"></i> Seguridad y Privacidad</h1>
            <p>Tu seguridad y privacidad son nuestra prioridad. Conoce cómo protegemos tu información y qué medidas puedes tomar para mantener tu cuenta segura.</p>
          </div>

          <div class="privacy-policy">
            <h2><i class="fas fa-file-contract"></i> Política de Privacidad</h2>

            <div class="privacy-section">
              <h3>Recopilación de Información</h3>
              <p>Recopilamos únicamente la información necesaria para brindarte nuestros servicios. Esto incluye información de perfil, preferencias de usuario y datos de uso de la plataforma de manera transparente y con tu consentimiento.</p>
            </div>

            <div class="privacy-section">
              <h3>Uso de la Información</h3>
              <p>Tu información se utiliza exclusivamente para mejorar tu experiencia en AURA, personalizar contenido y mantener la seguridad de la plataforma. Nunca utilizamos tus datos para fines comerciales sin tu autorización.</p>
            </div>

            <div class="privacy-section">
              <h3>Compartir Información</h3>
              <p>No vendemos, alquilamos ni compartimos tu información personal con terceros sin tu consentimiento explícito, excepto cuando sea requerido por ley o para proteger la seguridad de nuestra comunidad.</p>
            </div>

            <div class="privacy-section">
              <h3>Retención de Datos</h3>
              <p>Conservamos tu información solo durante el tiempo necesario para cumplir con los propósitos descritos en esta política o según lo requiera la ley. Tienes el derecho de solicitar la eliminación de tus datos en cualquier momento.</p>
            </div>

            <div class="privacy-section">
              <h3>Tus Derechos</h3>
              <p>Tienes derecho a acceder, corregir, eliminar o transferir tu información personal. También puedes oponerte al procesamiento de tus datos en cualquier momento. Facilitamos herramientas intuitivas para ejercer estos derechos.</p>
            </div>
          </div>

          <div class="contact-security">
            <h3><i class="fas fa-headset"></i> Contacto de Seguridad</h3>
            <p>Si tienes preguntas sobre seguridad o privacidad, o necesitas reportar un incidente de seguridad:</p>
            <p>Email: <a href="mailto:aura@gmail.com" class="security-email">aura@gmail.com</a></p>
            <p><strong>Respuesta garantizada en 24 horas</strong></p>
            <p>Nuestro equipo está disponible 24/7.</p>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn btn-secondary" type="button" data-close>Cerrar</button>
      </div>
    </div>
  </div>

  @endif
</div>

<!-- Toast -->
<div id="auraToast" role="status" aria-live="polite">Modo demo: acción visual.</div>

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
</script>
</body>
</html>
