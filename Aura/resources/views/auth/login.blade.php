@vite('resources/css/login.css')
@vite('resources/js/fondo-animado.js')


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Aura</title>
</head>
<body>

  <!-- Fondo animado -->
  <canvas id="canvas"></canvas>
  <canvas class="glslCanvas" id="canvas"></canvas>
  <div class="overlay"></div>

  <div class="main-container">
    <div class="form-section">

      <!-- Logo + Flecha Volver -->
      <a href="javascript:history.back()" class="back-logo-container" title="Volver atrás">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ff5db1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" width="28" height="28" viewBox="0 0 24 24">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
        <img src="{{ asset('img/Aura_LOGO.PNG') }}" class="logo-img" />
      </a>

      <!-- Formulario Login -->
      <div class="form-container">
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <h1>Inicia sesión</h1>

          <div class="form-group">
            <input type="text" name="email" required />
            <label>Correo Electrónico</label>
          </div>

          <div class="form-group">
            <input type="password" name="password" required />
            <label>Contraseña</label>
          </div>

          <button type="submit" class="btn-registrarse">Iniciar sesión</button>

          <div class="remember">
            <div class="right"><a href="#">¿Necesitas ayuda?</a></div>
          </div>

          <div class="signup">
            <p>¿Nuevo en Aura? <a href='seleccionar-tipo'>Regístrate ahora</a>.</p>
          </div>

          <p class="captcha">
            Esta página está supervisada por Aura para garantizar que el acceso sea seguro y confiable.
            <a href="#">Lee más</a>.
          </p>
        </form>
      </div>
    </div>

    <!-- Puedes añadir aquí una sección opcional como los planes, si quieres -->
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
</body>
</html>
