<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Álbumes — {{ ($user->nombre_artistico ?? $user->nombre ?? 'Artista') ?? 'Artista' }}</title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  @vite('resources/css/menu_album.css')

  @php
    // ====== FALLBACKS PARA EVITAR "Undefined variable $user" ======
    use Illuminate\Support\Str;
    use App\Models\Album;
    use Illuminate\Support\Facades\Auth as AuthFacade;

    // Si no llega $user desde el controlador, usamos el autenticado (puede ser null)
    $user = $user ?? auth()->user();

    // Followers count seguro
    $followersCount = $followersCount
        ?? (($user && method_exists($user, 'followers')) ? $user->followers()->count() : 0);

    // Si no llegan $albumes, intenta cargarlos por user_id (si hay usuario)
    $albumes = $albumes
        ?? (($user && $user->id) ? Album::where('user_id', $user->id)->get() : collect());

    // Obtener la URL del banner del usuario (usa tu helper drive_img_url si existe)
    $bannerLow  = ($user && $user->banner) ? drive_img_url($user->banner, 640)  . '&v=' . time() : asset('img/default-banner.jpg');
    $bannerHigh = ($user && $user->banner) ? drive_img_url($user->banner, 1920) . '&v=' . time() : asset('img/default-banner.jpg');

    // Normalizar y paginar álbumes (4 por página)
    $albumsNormalized = collect($albumes)->map(function($a) {
        return (object)[
            'id'      => $a->id,
            'titulo'  => $a->title ?? $a->titulo ?? 'Sin título',
            'portada' => $a->cover_path ?? $a->portada ?? null,
        ];
    });
    $albumPages = $albumsNormalized->chunk(4); // 4 álbumes por página
  @endphp
</head>
<body>
  @includeIf('components.sidebar')
  @includeIf('components.header')
  @includeIf('components.traductor')

  <main class="main-content">
    <!-- Banner y detalles del usuario -->
    <div class="user-banner" style="background-image: url('{{ $bannerHigh }}');">
      <div class="banner-overlay"></div>
      <div class="user-info">
        <h2 class="user-name">{{ $user->nombre_artistico ?? $user->nombre ?? 'Invitado' }}</h2>
        <p class="user-followers">{{ $followersCount }} seguidores</p>
      </div>
    </div>

    <div class="albums-head">
      <div class="section-title">
        <h2><i class="fa-solid fa-compact-disc"></i> Álbumes</h2>
      </div>
      <div class="page-label" id="albumsPageLabel"></div>
    </div>

    <div class="albums-wrap no-clip">
      <!-- Navegación de álbumes -->
      <button class="albums-arrow left" id="albumsPrev" aria-label="Anterior">
        <i class="fa-solid fa-chevron-left"></i>
      </button>

      <div class="albums-viewport" id="albumsViewport">
        <div class="albums-track" id="albumsTrack" data-pages="{{ $albumPages->count() }}">
          @forelse($albumPages as $page)
            <div class="albums-page">
              <div class="albums-grid-2x2">
                @foreach($page as $album)
                  @php
                    $albumCover = $album->portada
                      ? drive_img_url($album->portada, 360) . '&v=' . time()
                      : asset('img/default-album.png');

                    $isOwner = $user && Auth::check() && Auth::id() === ($user->id ?? null);
                  @endphp

                  <div class="card album-card {{ $isOwner ? 'has-trash' : '' }}">
                    <a href="{{ route('album.show', $album->id) }}" class="card-link">
                      <div class="card-img">
                        <img src="{{ $albumCover }}" alt="Portada" loading="lazy" decoding="async">
                        <span class="album-play"><i class="fa-solid fa-play"></i></span>
                      </div>
                      <h4 class="album-title">{{ $album->titulo }}</h4>
                      <p class="album-sub">Por {{ $user->nombre_artistico ?? $user->nombre ?? 'Artista' }}</p>
                    </a>

                    @if($isOwner)
                      <button class="trash-float open-delete"
                              title="Eliminar álbum"
                              data-type="album"
                              data-action="{{ route('album.destroy', $album->id) }}"
                              data-title="{{ $album->titulo }}"
                              data-cover="{{ $albumCover }}">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          @empty
            <div class="albums-page">
              <div class="empty-state">
                <i class="fa-regular fa-folder-open"></i>
                <p>No hay álbumes todavía.</p>
              </div>
            </div>
          @endforelse
        </div>
      </div>

      <!-- Navegación de páginas -->
      <button class="albums-arrow right" id="albumsNext" aria-label="Siguiente">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
  </main>

  @includeIf('components.footer')

  <script>
    (function() {
      const track = document.getElementById('albumsTrack');
      const prev = document.getElementById('albumsPrev');
      const next = document.getElementById('albumsNext');
      const label = document.getElementById('albumsPageLabel');
      if (!track) return;

      let page = 0, pages = parseInt(track.dataset.pages || '0', 10);

      // Ajuste inicial para que el ancho del track sea N*100% y cada page sea 100%
      function ensureWidths() {
        const pagesEls = track.querySelectorAll('.albums-page');
        track.style.width = (pagesEls.length * 100) + '%';
        pagesEls.forEach(el => el.style.width = (100 / pagesEls.length) + '%');
      }

      function update() {
        track.style.transform = `translateX(-${page * 100}%)`;
        if (prev) prev.disabled = (page === 0);
        if (next) next.disabled = (page >= pages - 1 || pages <= 0);
        if (label) label.textContent = pages ? `Página ${page + 1} de ${pages}` : '';
      }

      prev?.addEventListener('click', () => { if (page > 0) { page--; update(); } });
      next?.addEventListener('click', () => { if (page < pages - 1) { page++; update(); } });

      ensureWidths();
      update();
    })();
  </script>
</body>
</html>
