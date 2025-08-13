<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','App')</title>
  {{-- Tus CSS globales si tienes --}}
  @stack('styles') {{-- NECESARIO para que aparezca el <style> del fondo --}}
</head>
<body style="margin:0;background:#000;min-height:100vh;">
  @yield('content')
</body>
</html>
