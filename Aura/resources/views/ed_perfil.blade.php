<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Perfil — {{ $user->nombre_artistico ?? 'Invitado' }} · Aura</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  @vite('resources/css/ed_perfil.css')
  <style>
    /* Avatar circular */
    .avatar-wrap {
      position: relative;
      width: 180px;
      height: 180px;
      border-radius: 50%;
      overflow: hidden;
      margin: 0 auto;
      box-shadow: 0 0 12px rgba(0,0,0,.4);
    }
    .avatar-wrap img.avatar-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      transition: object-position .3s ease, transform .3s ease;
    }
  </style>
</head>
<body>
@include('components.sidebar')
@include('components.footer')
        @include('components.header')
@if (!function_exists('drive_image_view'))
    @php
    function drive_image_view($url) {
        if (!$url) return null;

        // /file/d/FILE_ID/
        if (preg_match('~/d/([^/]+)~', $url, $m)) {
            return "https://drive.google.com/uc?export=view&id={$m[1]}";
        }

        // ?id=FILE_ID
        if (preg_match('~[?&]id=([^&]+)~', $url, $m)) {
            return "https://drive.google.com/uc?export=view&id={$m[1]}";
        }

        return $url;
    }
    @endphp
@endif

<main class="main-content">

  <!-- ===== HERO PERFIL ===== -->
  <section class="profile-hero">
<div class="profile-banner"
     style="background-image: url('{{ drive_img_url($user->banner, 1920) }}&v={{ time() }}')">
</div>

      <div class="banner-overlay"></div>

      @if(Auth::check() && Auth::id() === $user->id)
        <div class="edit-btn">
          <button class="btn-primary" id="editBtn"><i class="fa-solid fa-pen"></i> Editar</button>
        </div>
      @endif

      <div class="profile-header">
        <h1 id="artistName">{{ $user->nombre_artistico ?? 'Artista' }}</h1>
      </div>

      <div class="btn-follow-container">
        @auth
          @if(Auth::id() !== $user->id)
            @if(Auth::user()->isFollowing($user->id))
              <form action="{{ route('perfil.unfollow', $user->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-follow">Dejar de seguir</button>
              </form>
            @else
              <form action="{{ route('perfil.follow', $user->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-follow">Seguir</button>
              </form>
            @endif
          @endif
        @endauth
      </div>

      <div class="profile-footer">
        <span class="listeners"><i class="fa-solid fa-headphones"></i> {{ $user->oyentes_mensuales ?? 0 }} oyentes mensuales</span>
        <span class="followers"><i class="fa-solid fa-user-group"></i> {{ $user->seguidores ?? 0 }} seguidores</span>
      </div>

      <div class="avatar-wrap xl">
        @if($user && $user->avatar)
<img class="avatar-img"
     src="{{ drive_img_url($user->avatar, 500) }}&v={{ time() }}"
     alt="{{ $user->nombre_artistico ?? $user->nombre }}">

        @else
          <div class="avatar-fallback">{{ strtoupper(substr($user->nombre_artistico ?? $user->nombre ?? 'U',0,1)) }}</div>
        @endif
      </div>
    </div>
  </section>

 <!-- ===== CONTENIDO MÚSICA ===== -->
  <section class="music-layout">

    <!-- 🎵 Canciones -->
    <div class="music-column">
      <h2><i class="fa-solid fa-music"></i> Canciones</h2>
      <div class="songs-list">
        @foreach($canciones as $song)
          @php
            $coverUrl = $song->cover_url
                ? drive_img_url($song->cover_url, 300)
                : asset('img/default-cancion.png');
              $rawAudio = $song->audio_url;
            $audioUrl = null;

            if ($rawAudio) {
                if (Str::contains($rawAudio, 'drive.google')) {
                    if (preg_match('~/d/([^/]+)~', $rawAudio, $m)) {
                        $id = $m[1];
                    } elseif (preg_match('~[?&]id=([^&]+)~', $rawAudio, $m)) {
                        $id = $m[1];
                    } else {
                        $id = null;
                    }
                    $audioUrl = $id ? route('media.drive', ['id' => $id]) : $rawAudio;
                } else {
                    $audioUrl = $rawAudio;
                }
            }
          @endphp
@php
  $coverUrl = $song->cover_url
      ? drive_img_url($song->cover_url, 300)
      : asset('img/default-cancion.png');
@endphp
         <div class="song-row">
  <div class="song-left">
    <img src="{{ $coverUrl }}" alt="cover">
    <div class="song-info">
      <h4>{{ $song->title }}</h4>
      <p>{{ $user->nombre_artistico ?? 'Desconocido' }}</p>
    </div>
  </div>
  <div class="song-duration">{{ $song->duration ?? '0:00' }}</div>

  <!-- botón invisible para tu reproductor -->
  <button class="cancion-item"
          style="display:none"
          data-id="{{ $song->id }}"
          data-src="{{ $audioUrl }}"
          data-title="{{ $song->title }}"
          data-artist="{{ $user->nombre_artistico ?? 'Desconocido' }}"
          data-cover="{{ $coverUrl }}">
  </button>

  @if(Auth::check() && Auth::id() === $user->id)
    <form action="{{ route('cancion.destroy', $song->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta canción?')">
      @csrf
      @method('DELETE')
      <button type="submit" class="delete-btn"><i class="fa-solid fa-trash"></i></button>
    </form>
  @endif
</div>
        @endforeach
      </div>

      <div class="view-more-btn">
        <button id="toggleViewMore">Ver más</button>
      </div>
    </div>

    <!-- 📀 Álbumes -->
    <div class="music-column">
      <h2><i class="fa-solid fa-compact-disc"></i> Álbumes</h2>
      <div class="grid albums-grid">
        @forelse($albumes as $album)
          <div class="card album-card">
            <a href="{{ route('album.show', $album->id) }}" class="card-link">
              <div class="card-img">
                <img src="{{ $album->cover_path ? drive_img_url($album->cover_path, 300) : asset('img/default-album.png') }}" alt="Portada del álbum">
                <div class="img-overlay"></div>
              </div>
              <h4>{{ $album->title }}</h4>
              <p>Por {{ $album->user->nombre_artistico ?? $album->user->nombre }}</p>
            </a>
            @if(Auth::check() && Auth::id() === $user->id)
              <form action="{{ route('album.destroy', $album->id) }}" method="POST" class="delete-form" onsubmit="return confirm('¿Eliminar este álbum?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-btn"><i class="fa-solid fa-trash"></i></button>
              </form>
            @endif
            <div class="play-btn"><i class="fa-solid fa-play"></i></div>
          </div>
        @empty
          <div class="empty">No hay álbumes todavía 📀</div>
        @endforelse
      </div>
    </div>

  </section>

  <!-- ⚡ Últimos lanzamientos -->
  <section class="section">
    <h2><i class="fa-solid fa-bolt"></i> Últimos lanzamientos</h2>
    <div class="grid releases">
      @forelse($lanzamientos as $item)
        <div class="card hover-zoom">
          <div class="card-img">
            <img src="{{ $item['cover'] ? drive_img_url($item['cover'], 300) : asset('img/default-release.png') }}" alt="release">
            <div class="img-overlay"></div>
          </div>
          <h4>@if($item['tipo'] === 'album') 📀 @else 🎵 @endif {{ $item['titulo'] }}</h4>
          <p>{{ $item['anio'] }}</p>
        </div>
      @empty
        <div class="empty">Aún no hay lanzamientos 🔥</div>
      @endforelse
    </div>
  </section>
</main>

@if(Auth::check() && Auth::id() === $user->id)
<div class="modal" id="editModal" aria-hidden="true">
  <div class="modal-content glass">
    <!-- Formulario de edición -->
    <div class="modal-header">
      <h3><i class="fa-solid fa-user-pen"></i> Editar perfil</h3>
      <button type="button" class="btn-advanced"><i class="fa-solid fa-gear"></i> Configuración avanzada</button>
    </div>
    <form action="{{ route('perfil.update') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-row two-cols">
        <div class="col">
          <label>Nombre artístico actual</label>
          <div class="locked-input">
            <input type="text" value="{{ $user->nombre_artistico ?? 'Sin definir' }}" readonly>
            <i class="fa-solid fa-lock lock-icon"></i>
          </div>
        </div>
        <div class="col">
          <label>Nuevo nombre artístico</label>
          <input name="nuevo_nombre_artistico" type="text" placeholder="Escribe el nuevo nombre artístico">
        </div>
      </div>

      <!-- Avatar -->
      <div class="modal-row">
        <div class="label-col">Foto de perfil</div>
        <div class="input-col center-content">
          <label class="file-preview avatar-edit">
            @if($user && $user->avatar)
              <img class="avatar-img"
                   src="{{ route('media.drive', ['id' => $user->avatar]) }}?v={{ time() }}"
                   alt="{{ $user->nombre_artistico ?? $user->nombre }}">
            @else
              <i class="fa-solid fa-user"></i>
            @endif
            <input type="file" name="avatar" accept="image/*" hidden>
            <div class="overlay"><i class="fa-solid fa-camera"></i></div>
          </label>
        </div>
      </div>

      <!-- 🔧 Control de posición del avatar -->
      <div class="modal-row">
        <div class="label-col">Ajustar posición</div>
        <div class="input-col">
          <input type="range" id="avatarPos" min="0" max="100" value="50">
        </div>
      </div>

      <!-- Banner -->
      <div class="modal-row">
        <div class="label-col">Banner</div>
        <div class="input-col">
          <label class="file-preview banner-edit">
            @if($user && $user->banner)
              <img src="{{ route('media.drive', ['id' => $user->banner]) }}?v={{ time() }}" alt="banner">
            @else
              <div class="banner-placeholder">No hay banner</div>
            @endif
            <input type="file" name="banner" accept="image/*" hidden>
            <div class="overlay"><i class="fa-solid fa-camera"></i></div>
          </label>
        </div>
      </div>

      <!-- Biografía -->
      <div class="modal-row">
        <div class="label-col">Descripción</div>
        <div class="input-col">
          <textarea name="bio" rows="4">{{ $user->biografia ?? '' }}</textarea>
        </div>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn-primary glow"><i class="fa-solid fa-save"></i> Guardar</button>
        <button type="button" class="btn-secondary" id="cancelEdit"><i class="fa-solid fa-xmark"></i> Cancelar</button>
      </div>
    </form>
  </div>
</div>
@endif

@vite('resources/js/ed-perfil.js')
<script>
document.addEventListener("DOMContentLoaded", () => {
  const songsList = document.querySelector(".songs-list");
  const viewMoreBtn = document.querySelector(".view-more-btn");
  const toggleBtn = document.getElementById("toggleViewMore");

  if (songsList && songsList.children.length > 5) {
    viewMoreBtn.style.display = "block"; // muestra el botón si hay +5 canciones

    toggleBtn.addEventListener("click", () => {
      songsList.classList.toggle("expanded");

      // texto del botón
      toggleBtn.textContent = songsList.classList.contains("expanded")
        ? "Ver menos ▲"
        : "Ver más ▼";

      // animación suave de scroll cuando expande
      if (songsList.classList.contains("expanded")) {
        songsList.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  }

  // 🎵 Reproducir al hacer click en fila
  document.querySelectorAll(".song-row").forEach(row => {
    row.addEventListener("click", () => {
      const hiddenBtn = row.querySelector(".cancion-item");
      if (hiddenBtn) hiddenBtn.click();
    });
  });
});

</script>
</body>
</html>
