<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Canciones por Álbum</title>

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
  </style>
</head>
<body>
          @yield('content')
@include('components.traductor')
<div class="wrap">

  <h1>Canciones por álbum</h1>

  {{-- ===== Buscador simple por query-string ===== --}}
  <form class="search" method="get">
    <input type="text" name="name" placeholder="Escribe el nombre del álbum…" value="{{ request('name', $albumName ?? '') }}" />
    <button type="submit" class="traducible">Buscar</button>
  </form>

  @php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Schema;

    // ===== Helpers robustos para Google Drive =====
    if (!function_exists('drive_extract_id')) {
        function drive_extract_id($url) {
            if (!$url) return null;
            $url = trim($url);
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

    // ===== Obtener el nombre del álbum desde query-string o variable pasada =====
    $wanted = trim(request('name', $albumName ?? ''));
    $album = null;

    if ($wanted !== '') {
        // 1) Búsqueda case-insensitive por título
        $album = \App\Models\Album::with('songs')
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($wanted)])
            ->first();

        // 2) Intento por slug si existe la columna
        if (!$album && Schema::hasColumn('albums','slug')) {
            $album = \App\Models\Album::with('songs')
                ->where('slug', Str::slug($wanted))
                ->first();
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
  @endphp

  @if($wanted === '')
    <p class="muted">Escribe el nombre del álbum arriba y presiona “Buscar”.</p>
  @elseif(!$album)
    <p>No se encontró un álbum llamado <strong>{{ $wanted }}</strong>.</p>
  @elseif(!$album->songs->count())
    <h2>{{ $album->title }}</h2>
    <p class="muted">Este álbum no tiene canciones asociadas.</p>
  @else
    <h2>Pistas del álbum: {{ $album->title }}</h2>

    <ul class="tracks">
      @foreach($album->songs as $song)
        @php
          // AUDIO
          $audioUrl = Str::startsWith($song->audio_path, ['http://','https://'])
              ? $song->audio_path
              : Storage::url($song->audio_path);
          $drivePreview = Str::contains($audioUrl, 'drive.google') ? drive_audio_preview($audioUrl) : null;

          // COVER: primero cover de la canción; si no, la del álbum
          $rawCover = $song->cover_path ?? $album->cover_path;
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
            <div class="title">{{ $song->title }}</div>

            @if($drivePreview)
              {{-- Audio desde Google Drive --}}
              <iframe src="{{ $drivePreview }}" width="100%" height="120" frameborder="0" allow="autoplay"></iframe>
            @else
              {{-- Audio HTML5 normal --}}
              <audio controls preload="none">
                <source src="{{ $audioUrl }}" type="audio/mpeg">
              </audio>
            @endif
          </div>
        </li>
      @endforeach
    </ul>

    
  @endif

</div>
</body>
</html>
