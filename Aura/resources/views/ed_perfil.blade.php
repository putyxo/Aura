<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Perfil — {{ $user->nombre_artistico ?? 'Invitado' }} · Aura</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  @vite('resources/css/ed_perfil.css')
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
    <div class="profile-banner" style="background-image: url('{{ $user->imagen_portada ? asset($user->imagen_portada) : '' }}')">
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
          <img class="avatar-img" src="{{ asset('storage/' . $user->avatar) }}?v={{ time() }}" alt="{{ $user->nombre_artistico ?? $user->nombre }}">
        @else
          <div class="avatar-fallback">{{ strtoupper(substr($user->nombre_artistico ?? $user->nombre ?? 'U',0,1)) }}</div>
        @endif
      </div>
    </div>
  </section>

  <!-- ===== CONTENIDO MÚSICA ===== -->
  <section class="music-layout">
    <!-- Álbumes -->
    <div class="music-column">
      <h2><i class="fa-solid fa-compact-disc"></i> Álbumes</h2>
      <div class="grid">
        @forelse($albumes as $album)
          <div class="card hover-zoom">
            <div class="card-img">
              <img src="{{ asset($album->portada ?? 'img/default-album.png') }}" alt="album">
              <div class="img-overlay"></div>
            </div>
            <h4>{{ $album->titulo }}</h4>
            <p>{{ $album->anio ?? '' }}</p>
          </div>
        @empty
          <div class="empty">No hay álbumes todavía 📀</div>
        @endforelse
      </div>
    </div>

<!-- Canciones -->
<div class="music-column">
  <h2><i class="fa-solid fa-music"></i> Canciones</h2>
  <div class="grid">
    @foreach($canciones as $song)
          @php
            $rawAudio = $song->audio_url;
            $audioUrl = null;

            if ($rawAudio) {
                if (Str::contains($rawAudio, 'drive.google')) {
                    // Extraer ID de Drive
                    if (preg_match('~/d/([^/]+)~', $rawAudio, $m)) {
                        $id = $m[1];
                    } elseif (preg_match('~[?&]id=([^&]+)~', $rawAudio, $m)) {
                        $id = $m[1];
                    } else {
                        $id = null;
                    }
                    // Usar SIEMPRE el proxy de Laravel
                    $audioUrl = $id ? route('media.drive', ['id' => $id]) : $rawAudio;
                } else {
                    $audioUrl = $rawAudio; // Otro enlace externo
                }
            }
          @endphp

          <div class="card hover-zoom">
            <button class="cancion-item"
              type="button"
              data-src="{{ $audioUrl }}"
              data-title="{{ $song->title }}"
              data-artist="{{ $user->nombre_artistico ?? 'Desconocido' }}">
              <h4>{{ $song->title }}</h4>
              <p>{{ $song->duration ?? '0:00' }}</p>
            </button>
          </div>
    @endforeach
  </div>
</div>

  </section>

  <!-- ===== ÚLTIMOS LANZAMIENTOS ===== -->
  <section class="section">
    <h2><i class="fa-solid fa-bolt"></i> Últimos lanzamientos</h2>
    <div class="grid releases">
      @forelse($lanzamientos as $item)
        <div class="card hover-zoom">
          <div class="card-img">
            <img src="{{ asset($item['cover'] ?? 'img/default-release.png') }}" alt="release">
            <div class="img-overlay"></div>
          </div>
          <h4>
            @if($item['tipo'] === 'album') 📀 @else 🎵 @endif {{ $item['titulo'] }}
          </h4>
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
      <!-- Campos de edición... -->
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
              <img src="{{ asset('storage/' . $user->avatar) }}?v={{ time() }}" alt="avatar">
            @else
              <i class="fa-solid fa-user"></i>
            @endif
            <input type="file" name="avatar" accept="image/*" hidden>
            <div class="overlay"><i class="fa-solid fa-camera"></i></div>
          </label>
        </div>
      </div>

      <!-- Banner -->
      <div class="modal-row">
        <div class="label-col">Banner</div>
        <div class="input-col">
          <label class="file-preview banner-edit">
            @if($user && $user->banner)
              <img src="{{ asset($user->banner) }}" alt="banner">
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
</body>
</html>
