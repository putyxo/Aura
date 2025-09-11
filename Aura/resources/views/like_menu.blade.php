<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AURA — Me gusta (Álbumes)</title>

  {{-- Fuente + Iconos --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- Vite CSS (reutilizamos like.css) --}}
  @vite(['resources/css/like.css'])
</head>
<body>
<div class="lk-app">
  <div class="lk-with-sidebar">
    @include('components.sidebar')
    @include('components.header')
    @include('components.traductor')
    @include('components.fondo')

    {{-- HOTFIX: garantiza que $likedAlbums exista como Collection --}}
    @php
      if (!isset($likedAlbums) || is_null($likedAlbums)) {
          $likedAlbums = collect();
      } elseif (is_array($likedAlbums)) {
          $likedAlbums = collect($likedAlbums);
      }
    @endphp

    <main class="main-content lk-page" data-page="likes-albums">
      <div class="lk-shell">

        {{-- ================== HERO ================== --}}
        <section class="lk-hero" aria-label="Tus Álbumes que te gustan">
          <div class="lk-hero__bg"></div>

          <div class="lk-hero__row">
            <div class="lk-hero__content">
              <div class="lk-hero__icon"><i class="fa-solid fa-album-collection" style="font-style:normal"><i class="fa-solid fa-record-vinyl"></i></i></div>
              <div>
                <h1 class="lk-hero__title">Álbumes que te gustan</h1>
                <p class="lk-hero__sub">
                  {{ number_format($likedAlbums->count()) }} álbum{{ $likedAlbums->count()===1?'':'es' }} guardado{{ $likedAlbums->count()===1?'':'s' }}.
                </p>
              </div>
            </div>

            <div class="lk-hero__actions">
              <div class="lk-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="lkSearch" type="search" placeholder="Buscar álbum o artista..." aria-label="Buscar en Álbumes que te gustan" autocomplete="off">
                <button class="lk-clear" id="lkClear" aria-label="Limpiar búsqueda"><i class="fa-solid fa-xmark"></i></button>
              </div>

              <div class="lk-sort">
                <select id="lkSort" aria-label="Ordenar álbumes">
                  <option value="recent">Más recientes</option>
                  <option value="title">Título (A–Z)</option>
                  <option value="artist">Artista (A–Z)</option>
                  <option value="year">Año</option>
                </select>
              </div>
            </div>
          </div>
        </section>

        {{-- Toast --}}
        @if(session('ok'))
          <div class="lk-toast lk-toast--ok" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('ok') }}</span>
          </div>
        @endif

        {{-- ================== GRID ================== --}}
        <section class="lk-grid" id="lkGrid" data-count="{{ $likedAlbums->count() }}">

          @php use Illuminate\Support\Str; @endphp

          @forelse($likedAlbums as $album)
            @php
              // Campos tolerantes (ES/EN) para diferentes esquemas
              $title  = $album->title ?? $album->titulo ?? $album->name ?? 'Álbum sin título';
              $artist = optional($album->user)->nombre_artistico
                        ?? optional($album->user)->nombre
                        ?? ($album->artist ?? 'Artista');

              $cover  = $album->cover_path ?? $album->portada ?? $album->imagen ?? null;
              // IDs Drive numéricos -> URL propia si usas media.drive
              if ($cover && is_numeric($cover)) $cover = route('media.drive', ['id' => $cover]);
              $cover = $cover ?: asset('img/default-cover.jpg');

              $release  = $album->release_date ?? $album->fecha_lanzamiento ?? null;
              $year     = $release ? (\Carbon\Carbon::parse($release)->format('Y')) : '—';

              // Conteo de canciones (si la relación existe o viene como songs_count)
              $songsCount = $album->songs_count
                          ?? (property_exists($album,'songs') ? (optional($album->songs)->count() ?? null) : null);
              $songsCount = $songsCount ?? '—';

              $searchKey = Str::lower(($title ?? '').' '.($artist ?? ''));
            @endphp

            <article
              class="lk-tile"
              title="{{ $title }}"
              data-name="{{ $searchKey }}"
              data-title="{{ Str::lower($title) }}"
              data-artist="{{ Str::lower($artist) }}"
              data-year="{{ is_numeric($year)? $year : 0 }}"
              data-recent="{{ $album->pivot->created_at ?? $album->created_at ?? now() }}"
              data-album-id="{{ $album->id }}"
            >
              {{-- Enlace a la página del álbum --}}
              <a class="lk-tile__link" href="{{ url('albums/'.$album->id) }}" aria-label="Abrir {{ $title }}"></a>

              <div class="lk-cover">
                <img src="{{ $cover }}" alt="Portada {{ $title }}" width="260" height="260" loading="lazy" decoding="async">

                {{-- Play álbum (lanza evento global para que lo maneje tu reproductor del footer) --}}
                <button type="button" class="lk-play" aria-label="Reproducir álbum {{ $title }}" data-play-album="{{ $album->id }}">
                  <i class="fa-solid fa-play"></i>
                </button>

                {{-- Quitar de Me gusta (álbum) --}}
                <button type="button" class="lk-like is-liked"
                        title="Quitar de Me gusta"
                        aria-label="Quitar de Me gusta"
                        data-unlike-album="{{ $album->id }}">
                  <i class="fa-solid fa-heart"></i>
                </button>
              </div>

              <div class="lk-meta">
                <div class="lk-title" title="{{ $title }}">{{ $title }}</div>
                <div class="lk-artist">{{ $artist }}</div>
                <div class="lk-stats">
                  <span><i class="fa-solid fa-music"></i> {{ is_numeric($songsCount) ? $songsCount.' pistas' : $songsCount }}</span>
                  <span class="lk-dot">•</span>
                  <span><i class="fa-solid fa-calendar"></i> {{ $year }}</span>
                </div>
              </div>

              {{-- Fallback sin JS para quitar Me gusta (álbum) --}}
              <form class="lk-like-form" method="POST" action="{{ url('likes/albums/'.$album->id) }}">
                @csrf
                @method('DELETE')
              </form>
            </article>
          @empty
            <div class="lk-empty" style="grid-column:1/-1;">
              <i class="fa-solid fa-heart-crack"></i>
              <h3>Aún no tienes álbumes en Me gusta</h3>
              <p>Explora música y pulsa <i class="fa-solid fa-heart"></i> en los álbumes que te encanten.</p>
            </div>
          @endforelse
        </section>

      </div>
    </main>

    @include('components.footer')
  </div>
</div>

{{-- ======= JS mínimo (inline): buscador, orden, unlike y play de álbum ======= --}}
<script>
(() => {
  const $  = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));

  const tiles   = $$('.lk-tile');
  const grid    = $('#lkGrid');
  const search  = $('#lkSearch');
  const clear   = $('#lkClear');
  const sortSel = $('#lkSort');

  // Fmt helpers
  const toKey = v => (v||'').toString().trim().toLowerCase();

  // =============== BÚSQUEDA ===============
  const applyFilter = () => {
    const q = toKey(search?.value);
    tiles.forEach(t => {
      const hay = (t.dataset.name || '');
      t.style.display = hay.includes(q) ? '' : 'none';
    });
  };
  search?.addEventListener('input', applyFilter);
  clear?.addEventListener('click', () => { if (!search) return; search.value=''; applyFilter(); search.focus(); });

  // =============== ORDEN ===============
  const sorters = {
    recent: (a,b) => (new Date(b.dataset.recent||0)) - (new Date(a.dataset.recent||0)),
    title:  (a,b) => (a.dataset.title> b.dataset.title) ? 1 : -1,
    artist: (a,b) => (a.dataset.artist>b.dataset.artist)? 1 : -1,
    year:   (a,b) => (+b.dataset.year||0) - (+a.dataset.year||0),
  };
  const applySort = () => {
    if (!grid || !sortSel) return;
    const val = sortSel.value || 'recent';
    const nodes = tiles.filter(el => el.style.display !== 'none');
    nodes.sort(sorters[val] || sorters.recent).forEach(el => grid.appendChild(el));
  };
  sortSel?.addEventListener('change', applySort);

  // =============== UNLIKE (fallback por form) ===============
  $$('.lk-like').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const tile = e.currentTarget.closest('.lk-tile');
      const form = $('.lk-like-form', tile);
      if (form) form.submit();
    });
  });

  // =============== PLAY ÁLBUM (evento global para el footer) ===============
  $$('.lk-play').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const tile = e.currentTarget.closest('.lk-tile');
      const albumId = tile?.dataset.albumId;
      // Señal al reproductor global
      window.dispatchEvent(new CustomEvent('aura:playAlbum', {
        detail: { albumId, source: 'likes-albums' }
      }));
      // Feedback visual: toggle icon (no maneja pausa real sin el footer)
      const icon = e.currentTarget.querySelector('i');
      if (icon?.classList.contains('fa-play')) {
        icon.classList.replace('fa-play','fa-pause');
      } else {
        icon?.classList.replace('fa-pause','fa-play');
      }
    });
  });

  // Inicial
  applyFilter();
  applySort();
})();
</script>
</body>
</html>
