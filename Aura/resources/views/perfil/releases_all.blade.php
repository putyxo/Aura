<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Lanzamientos — {{ $user->nombre_artistico ?? $user->name }} · Aura</title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  @vite('resources/css/ed_perfil.css')

  <style>
    .container{max-width:1200px;margin:32px auto;padding:0 16px}
    .toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
    .toolbar h2{margin:0}
    .btn-back{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;background:#1e1e2a;border:1px solid rgba(255,255,255,.08)}
    .btn-back:hover{background:#2c2c3c}

    .grid-4x2{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
    @media (max-width:1100px){.grid-4x2{grid-template-columns:repeat(3,1fr)}}
    @media (max-width:768px){.grid-4x2{grid-template-columns:repeat(2,1fr)}}

    .card.album-card{border-radius:14px;overflow:hidden;background:#101018;box-shadow:0 0 0 1px rgba(255,255,255,.06) inset}
    .card .card-img{position:relative;aspect-ratio:1/1;overflow:hidden}
    .card .card-img img{width:100%;height:100%;object-fit:cover;display:block}
    .badge-tipo{position:absolute;top:8px;left:8px;font-size:12px;background:rgba(0,0,0,.6);padding:4px 8px;border-radius:999px}
    .card h4{font-size:14px;margin:10px 12px 4px}
    .card p{font-size:12px;color:#b9b9c5;margin:0 12px 12px}

    .pagination {display:flex;gap:8px;justify-content:center;margin:22px 0}
    .pagination a,.pagination span{padding:6px 10px;border-radius:8px;background:#13131a;border:1px solid rgba(255,255,255,.06)}
    .pagination .active{background:#2a2a3a}
  </style>
</head>
<body>
  @include('components.header')

  <div class="container">
    <div class="toolbar">
      <a class="btn-back" href="{{ route('perfil.show', $user->id) }}"><i class="fa-solid fa-arrow-left"></i> Volver al perfil</a>
      <h2>Todos los lanzamientos de {{ $user->nombre_artistico ?? $user->name }}</h2>
      <div></div>
    </div>

    <div class="grid-4x2">
      @foreach($lanzamientos as $item)
        <div class="card album-card">
          <div class="card-img">
            <img src="{{ $item['cover'] ? drive_img_url($item['cover'], 300) : asset('img/default-album.png') }}" alt="" loading="lazy" decoding="async">
            <span class="badge-tipo">{{ $item['tipo'] === 'album' ? 'Álbum' : 'Canción' }}</span>
          </div>
          <h4>{{ $item['titulo'] }}</h4>
          <p>{{ $item['anio'] }}</p>
        </div>
      @endforeach
    </div>

    <div class="pagination">
      {!! $pagination->withQueryString()->onEachSide(1)->links() !!}
    </div>
  </div>

  @include('components.footer')
</body>
</html>
