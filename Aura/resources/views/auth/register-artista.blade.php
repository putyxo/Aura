@vite('resources/css/login.css')
@vite('resources/js/fondo-animado.js')



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/country-select-js@2.0.1/build/css/countrySelect.min.css">
<script src="https://cdn.jsdelivr.net/npm/country-select-js@2.0.1/build/js/countrySelect.min.js"></script>
  <title>Registro</title>
</head>
<body>
      @yield('content')
@include('components.traductor')
  <!-- CANVAS PARA FONDO ANIMADO -->
     <canvas id="canvas"></canvas>
  <canvas class="glslCanvas" id="canvas"></canvas>

<div class="overlay"></div>


  <div class="main-container">
    <div class="form-section">


<div class="back-logo-container">
<a href="javascript:history.back()" class="back-logo-container" title="Volver atrás">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ff5db1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" width="28" height="28" viewBox="0 0 24 24">
    <path d="M15 18l-6-6 6-6"/>
  </svg>
  <img src="{{ asset('img/Aura_LOGO.PNG') }}" class="logo-img" />
</a>
</div>

      <div class="form-container">
        <form method="POST" action="{{ route('register-artista') }}">
            @csrf
          
            <h1>Regístrate</h1>
            <div class="form-group">
              <input type="text" name="nombre" required />
              <label class="traducible">Nombre de usuario</label>
            </div>

            <div class="form-group">
                <input type="text" name="nombre_artistico" required />
                <label class="traducible">Nombre Artistico</label>
              </div>
          
            <div class="form-group">
              <input type="email" name="email" required />
              <label class="traducible">Correo Electrónico</label>
            </div>
          
            <div class="form-group">
              <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" />
              <label class="traducible">Fecha Nacimiento</label>
            </div>
          
            <div class="form-group">
              <input type="password" name="password" required />
              <label class="traducible">Contraseña</label>
            </div>
          
            <div class="form-group">
              <input type="password" name="password_confirmation" required />
              <label class="traducible">Confirmar contraseña</label>
            </div>
          
            <button type="submit" class="btn-registrarse traducible">Registrarse</button>
          
            <div class="remember">
              <div class="right traducible"><a href="#">¿Necesitas ayuda?</a></div>
            </div>
          
            <div class="signup">
              <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Iniciar sesión</a>.</p>
            </div>
          
            <p class="captcha">
              Esta página está supervisada por Aura para garantizar que el acceso sea seguro y confiable.
              <a href="#">Lee más</a>.
            </p>
          </form>
          
      </div>
    </div>

    <div class="plans-section">
      <h2 class="traducible">Selecciona tu plan</h2>
      <div class="plans">
        <div class="plan-card basic">
          <h3>Gratis</h3>
          <p class="traducible">$0/mes</p>
          <ul>
            <li class="traducible">Escucha aleatoria</li>
            <li class="traducible">Con anuncios</li>
            <li class="traducible">Calidad estándar</li>
          </ul>
        </div>
        <div class="plan-card premium">
          <h3>Premium</h3>
          <p class="traducible">$4.99/mes</p>
          <ul>
            <li class="traducible">Sin anuncios</li>
            <li class="traducible">Modo offline</li>
            <li class="traducible">Mejor calidad</li>
          </ul>
        </div>
        <div class="plan-card familia">
          <h3 class="traducible">Familiar</h3>
          <p>$7.99/mes</p>
          <ul>
            <li class="traducible">Hasta 6 cuentas</li>
            <li class="traducible">Control parental</li>
            <li class="traducible">Sin anuncios</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
  @if($errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const errorMsg = @json($errors->first());
      const noti = document.createElement('div');
      noti.textContent = errorMsg;
      noti.style.position = 'fixed';
      noti.style.top = '20px';
      noti.style.right = '20px';
      noti.style.backgroundColor = '#ff5db1';
      noti.style.color = 'white';
      noti.style.padding = '12px 16px';
      noti.style.borderRadius = '8px';
      noti.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
      noti.style.zIndex = '9999';
      document.body.appendChild(noti);
      setTimeout(() => noti.remove(), 5000);
    });
  </script>
@endif
</body>
</html>
