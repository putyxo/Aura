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
        <form method="POST" action="{{ route('register') }}">
            @csrf
          
            <h1>Regístrate</h1>
          
            <div class="form-group">
              <input type="text" name="nombre" required />
              <label>Nombre de usuario</label>
            </div>
          
            <div class="form-group">
              <input type="email" name="email" required />
              <label>Correo Electrónico</label>
            </div>
          
            <div class="form-group">
              <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" />
              <label>Fecha Nacimiento</label>
            </div>
          
            <div class="form-group">
              <input type="password" name="password" required />
              <label>Contraseña</label>
              <button type="button" class="toggle-password" data-target="password">
                <svg class="eye-icon eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg class="eye-icon eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
              </button>
            </div>
          
            <div class="form-group">
              <input type="password" name="password_confirmation" required />
              <label>Confirmar contraseña</label>
              <button type="button" class="toggle-password" data-target="password_confirmation">
                <svg class="eye-icon eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg class="eye-icon eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
              </button>
            </div>
          
            <button type="submit" class="btn-registrarse">Registrarse</button>
          
            <div class="remember">
              <div class="right"><a href="#">¿Necesitas ayuda?</a></div>
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
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const toggles = document.querySelectorAll('.toggle-password');
      toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
          const target = this.dataset.target;
          const input = document.querySelector(`input[name="${target}"]`);
          const eyeOpen = this.querySelector('.eye-open');
          const eyeClosed = this.querySelector('.eye-closed');
          if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
          } else {
            input.type = 'password';
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
          }
        });
      });
    });
  </script>
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
