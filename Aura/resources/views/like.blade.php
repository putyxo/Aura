<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AURA — Favoritos</title>
  <link rel="stylesheet" href="{{ asset('css/like.css') }}">
</head>
<body>
          @yield('content')
@include('components.traductor')
  <div class="with-sidebar">
    @include('components.sidebar')

    <main class="main-content">
      <div class="empty-state">
        <div class="empty-box">
          <h2>Aún no se ha dado like a nada</h2>
        </div>
      </div>
    </main>

    @include('components.footer')
  </div>
</body>
</html>
