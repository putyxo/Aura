<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Buscar Canciones</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;background:#111;color:#f5f5f5;margin:0}
    .wrap{max-width:960px;margin:28px auto;padding:0 16px}
    h1,h2{margin:0 0 12px}
    form.search{display:flex;gap:8px;margin:14px 0 18px}
    form.search input[type="text"]{flex:1;padding:10px 12px;border-radius:10px;border:1px solid #333;background:#191919;color:#f5f5f5}
    form.search button{padding:10px 14px;border-radius:10px;border:0;background:#2b7fff;color:#fff;font-weight:600;cursor:pointer}
    ul.tracks{display:flex;flex-direction:column;gap:14px;padding:0;list-style:none}
    li.track{display:grid;grid-template-columns:86px 1fr;gap:12px;align-items:center;background:#151515;border:1px solid #232323;border-radius:14px;padding:12px}
    .cover{width:86px;height:86px;object-fit:cover;border-radius:10px;border:1px solid rgba(255,255,255,.08);background:#222}
    .title{font-weight:700;margin-bottom:6px;line-height:1.1}
    audio{width:100%;max-width:520px}
    .muted{opacity:.7}
    .hi{background:yellow;color:#111;padding:0 2px;border-radius:4px}
  </style>
</head>
<body>
          @yield('content')
@include('components.traductor')
<div class="wrap">
  <h1>Buscar canciones</h1>

  {{-- Buscador simple por query-string --}}
  <form class="search" method="get" action="{{ route('busqueda_individual') }}">
    <input type="text" name="q" placeholder="Escribe el nombre de la canción…"
           value="{{ request('q','') }}" />
    <button type="submit" class="traducible">Buscar</button>
  </form>

  @php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    // ===== Helpers Drive =====
    if (!function_exists('drive_extract_id')) {
        function drive_extract_id($url) {
            if (!$url) return null;
            if (preg_match('#/file/d/([^/]+)/#i', $url, $m)) return $m[1];       // /file/d/ID/
            if (preg_match('#[?&]id=([^&]+)#i', $url, $m)) return $m[1];        // ?id=ID
            return null;
        }
    }
    if (!function_exists('drive_audio_preview')) {
        function drive_audio_preview($url) {
            $id = drive_extract_id($url);
            return $id ? "https://drive.google.com/file/d/{$id}/preview" : null;
        }
    }
    if (!function_exists('drive_image_view')) {
        function drive_image_view($url) {
            $id = drive_extract_id($url);
            return $id ? "https://drive.google.com/uc?export=view&id={$id}" : null;
        }
    }

    // Placeholder inline (no requiere archivo)
    $placeholder = 'data:image/svg+xml;utf8,' . rawurlencode(
      '<svg xmlns="http://www.w3.org/2000/svg" width="86" height="86">
         <rect width="100%" height="100%" rx="10" ry="10" fill="#222"/>
         <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle"
               font-size="12" fill="#aaa">Sin portada</text>
       </svg>'
    );

    // ===== Consulta =====
    $q = trim(request('q',''));
    $songs = collect();

    if ($q !== '') {
        $like = '%'.str_replace(['%','_'], ['\%','\_'], $q).'%';
        // Usa tu modelo App\Models\Cancion y la columna 'title'
        $songs = \App\Models\Cancion::query()
            ->where('title', 'LIKE', $like)
            ->orderBy('title')
            ->limit(100)
            ->get();
    }

    // Resaltar coincidencias
    if (!function_exists('hi_match')) {
        function hi_match($text, $q){
            if($q==='') return e($text);
            $pattern = '/(' . preg_quote($q, '/') . ')/i';
            return preg_replace_callback($pattern, fn($m) => '<span class="hi">'.e($m[0]).'</span>', e($text));
        }
    }
  @endphp

  @if($q === '')
    <p class="muted">Escribe el nombre de la canción y presiona “Buscar”.</p>
  @elseif($songs->isEmpty())
    <p>No se encontraron canciones para <strong>{{ $q }}</strong>.</p>
  @else
    <h2>Resultados para: {{ $q }}</h2>

    <ul class="tracks">
      @foreach($songs as $song)
        @php
          $title = $song->title ?? ('ID '.$song->id);
          $rawAudio = $song->audio_path;
          $rawCover = $song->cover_path;

          // AUDIO: URL absoluta, storage o Drive preview
          $audioUrl = null; $drivePreview = null;
          if ($rawAudio) {
              $audioUrl = Str::startsWith($rawAudio, ['http://','https://']) ? $rawAudio : Storage::url($rawAudio);
              $drivePreview = Str::contains($audioUrl, 'drive.google') ? drive_audio_preview($audioUrl) : null;
          }

          // COVER: URL, storage o placeholder con soporte Drive
          if ($rawCover) {
              if (Str::startsWith($rawCover, ['http://','https://'])) {
                  $coverUrl = Str::contains($rawCover, 'drive.google')
                      ? (drive_image_view($rawCover) ?: $rawCover)
                      : $rawCover;
              } else {
                  $coverUrl = Storage::url($rawCover);
              }
          } else {
              $coverUrl = $placeholder;
          }
        @endphp

        <li class="track">
          <img class="cover" src="{{ $coverUrl }}" alt="Portada"
               onerror="this.onerror=null;this.src='{{ $placeholder }}'">
          <div>
            <div class="title">{!! hi_match($title, $q) !!}</div>

            @if($drivePreview)
              {{-- Audio desde Google Drive --}}
              <iframe src="{{ $drivePreview }}" width="100%" height="120" frameborder="0" allow="autoplay"></iframe>
            @elseif($audioUrl)
              {{-- Audio HTML5 --}}
              <audio controls preload="none">
                <source src="{{ $audioUrl }}" type="audio/mpeg">
              </audio>
            @else
              <div class="muted">Sin audio disponible</div>
            @endif
          </div>
        </li>
      @endforeach
    </ul>

    <p class="muted" style="margin-top:10px">
      Tip: si usas rutas de <code>storage</code>, ejecuta <code>php artisan storage:link</code>.
      Para Google Drive, comparte los archivos como “Cualquiera con el enlace: Lector”.
    </p>
  @endif
</div>
</body>
</html>
