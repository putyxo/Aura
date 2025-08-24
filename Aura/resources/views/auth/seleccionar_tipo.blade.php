@vite('resources/css/selecionar-tipo.css')
@vite('resources/js/selecionar-tipo.js')
@vite('resources/js/fondo-animado.js')

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Aura</title>
  <style>

  </style>
</head>
<body>
        @yield('content')
@include('components.traductor')
  <canvas id="canvas"></canvas>
  <div class="overlay"></div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

  <!-- === HOME / SELECCIÓN === -->
  <section class="home active">
    <a href="#" class="logo-back">
      <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
        <path d="M15 18l-6-6 6-6" />
      </svg>
      <img src="../img/Aura_LOGO.PNG" alt="Logo de Aura" />
    </a>

    <div class="container">
      <a class="card" href="register-artista">
        <img src="https://claroblog.com.ni/wp-content/uploads/2024/09/el-alfa-en-nicaragua-en-concierto-con-luces-moradas-scaled.webp" alt="Artista" />
        <p>Artista</p>
      <a>
      <a class="card" href="register">
        <img src="https://images.unsplash.com/photo-1593958812614-2db6a598c71c" alt="Usuario" />
        <p>Usuario</p>
      </a>
    </div>
  </section>

  <!-- === REGISTRO === -->
  <section class="registro">
    <div class="main-container">
      <div class="form-section">
        <div class="back-logo-container" onclick="volverInicio()">
          <svg fill="none" stroke="#ff5db1" stroke-width="3" viewBox="0 0 24 24">
            <path d="M15 18l-6-6 6-6" />
          </svg>
          <img src="../img/Aura_LOGO.PNG" alt="Logo de Aura" />
        </div>

        <div class="form-container">
          <!-- FORM ARTISTA -->
          <form id="form-artista" class="formulario" action="../conexion.php" method="POST">
            <h1>Regístrate como <span>Artista</span></h1>
            <input type="hidden" name="TipoUsuario" value="Artista" />
            <div class="form-group">
              <input type="text" name="Username" required />
              <label>Nombre de usuario</label>
            </div>
            <div class="form-group">
              <input type="email" name="UserEmail" required />
              <label>Correo Electrónico</label>
            </div>
            <div class="form-group">
              <input type="password" name="Userpassword" required />
              <label>Contraseña</label>
            </div>
            <button type="submit" class="btn-registrarse">Registrarse</button>

                      <div class="remember">
            <div class="right"><a href="#">¿Necesitas ayuda?</a></div>
          </div>

          <div class="signup">
            <p>¿Ya tienes una cuenta? <a href="../html/login.php">Iniciar sesión</a>.</p>
          </div>
          <p class="captcha">
            Esta página está supervisada por Aura para garantizar que el acceso sea seguro y confiable.
            <a href="#">Lee más</a>.
          </p>
          </form>

          <!-- FORM USUARIO -->
          <form id="form-usuario" class="formulario" action="../conexion.php" method="POST">
            <h1>Regístrate como <span>Usuario</span></h1>
            <input type="hidden" name="TipoUsuario" value="Usuario" />
            <div class="form-group">
              <input type="text" name="Username" required />
              <label>Nombre de usuario</label>
            </div>
            <div class="form-group">
              <input type="email" name="UserEmail" required />
              <label>Correo Electrónico</label>
            </div>
            <div class="form-group">
              <input type="password" name="Userpassword" required />
              <label>Contraseña</label>
            </div>
            <button type="submit" class="btn-registrarse">Registrarse</button>

                                  <div class="remember">
            <div class="right"><a href="#">¿Necesitas ayuda?</a></div>
          </div>

          <div class="signup">
            <p>¿Ya tienes una cuenta? <a href="../html/login.php">Iniciar sesión</a>.</p>
          </div>
          <p class="captcha">
            Esta página está supervisada por Aura para garantizar que el acceso sea seguro y confiable.
            <a href="#">Lee más</a>.
          </p>

          </form>
        </div>
      </div>

      <!-- SECCIÓN DE PLANES -->
      <div class="plans-section">
      <h2>Elige tu plan</h2>
      <div class="plans">
<div class="plan-card basic selected">

          <h3>Gratis</h3>
          <p>$0/mes</p>
          <ul>
            <li>Escucha aleatoria</li>
            <li>Con anuncios</li>
            <li>Calidad estándar</li>
          </ul>
        </div>
        <div class="plan-card premium">
          <h3>Premium</h3>
          <p>$4.99/mes</p>
          <ul>
            <li>Sin anuncios</li>
            <li>Modo offline</li>
            <li>Mejor calidad</li>
          </ul>
        </div>
        <div class="plan-card familia">
          <h3>Familiar</h3>
          <p>$7.99/mes</p>
          <ul>
            <li>Hasta 6 cuentas</li>
            <li>Control parental</li>
            <li>Sin anuncios</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  </section>
</body>
</html>
