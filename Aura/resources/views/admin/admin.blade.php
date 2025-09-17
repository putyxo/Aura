<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AURA — Admin Canciones</title>
    @vite('resources/css/admin.css')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    .cover-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        display: block;
    }

    .cover-img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
    }


    .btn-admin {
        background: #6f36ff;
        color: #fff;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-admin:hover {
        background: #5329c7;
    }
    </style>

</head>

<body>
    @yield('content')
    @include('components.traductor')


    <div class="with-sidebar">
        @include('components.sidebar')
        @include('components.header')
        @include('components.traductor')
        @include('components.fondo')

        <div class="app">
            <div class="with-sidebar">
                <div class="main-content">
                    <div class="page-studio studio">

                        <br>
                        <div class="studio__toolbar"
                            style="display: flex; justify-content: center; align-items: center;">
                            <h1 class="studio__title"><i class="fa-solid fa-music"></i> Canciones</h1>
                        </div>

<div class="admin-nav" style="display:flex; gap:10px; justify-content:center; margin:20px 0;">
   
    <a href="{{ route('albumadmin') }}" class="btn-admin">
        <i  class="fa-solid fa-film"></i> Álbumes
    </a>
    <a href="{{ route('usuarioadmin') }}" class="btn-admin">
        <i  class="fa-solid fa-users"></i> Usuarios
    </a>
</div>
<br>
                        <div class="studio__table" id="studioTable">
                            <div class="studio__thead">
                                <div class="c1"></div>
                                <div class="c2">Titulo</div>
                                <div class="c3">Visibilidad</div>
                                <div class="c4">Copyright</div>
                                <div class="c5">Fecha</div>
                                <div class="c6">Reproductor</div>
                                <div class="c7 ta-r">Acciones</div>
                            </div>

                            @forelse($canciones as $cancion)

                            <div class="studio__row reveal" data-id="{{ $cancion->id }}">


                                <!-- Portada -->
                                <div class="c1">
                                    <span class="thumb"
                                        style="background:url('{{ $cancion->cover_url }}') center/cover"></span>
                                </div>

                                <!-- Usuario + Link -->
                                <div class="c2">
                                    <div class="vtitle">{{ $cancion->clean_title }}</div>
                                    <a class="vlink" href="{{ $cancion->audio_url }}" target="_blank" rel="noopener">
                                        {{ $cancion->nombre_artistico }}
                                    </a>
                                </div>


                                <!-- Visibilidad -->
                                <div class="c3">
                                    <span
                                        class="pill pill--publico">{{ ucfirst($cancion->status ?? 'Desconocido') }}</span>
                                </div>

                                <!-- Copyright -->
                                <div class="c4"><span class="pill pill--none">N/A</span></div>

                                <!-- Fecha -->
                                <div class="c5">{{ $cancion->created_at->format('d M Y') }}</div>

                                <!-- Reproductor -->
                                <div class="c6">
                                    <div class="drive-player">
                                        <div id="player-{{ $cancion->id }}"></div>
                                    </div>

                                    <script>
                                    (function() {
                                        let url = @json($cancion -> audio_url ?? '');
                                        if (!url) return;

                                        let match = url.match(/[-\w]{25,}/);
                                        if (match) {
                                            let fileId = match[0];
                                            let iframe = document.createElement('iframe');
                                            iframe.width = "100%";
                                            iframe.height = "90";
                                            iframe.frameBorder = "0";
                                            iframe.allow = "autoplay";
                                            iframe.style.borderRadius = "12px";
                                            iframe.src = `https://drive.google.com/file/d/${fileId}/preview`;
                                            document.getElementById("player-{{ $cancion->id }}").appendChild(
                                                iframe);
                                        }
                                    })();
                                    </script>
                                </div>


                                <!-- Acciones -->
                                <div class="c7">
                                    <div class="actions ta-r">
                                        <form method="POST" action="{{ route('cancion.destroy', $cancion->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-act btn-delete" title="Borrar">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p style="padding:20px; text-align:center;">No hay canciones cargadas aún.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal eliminar -->
    <div class="modal" id="modal-delete" aria-hidden="true">
        <div class="modal__dialog" role="dialog" aria-modal="true">
            <div class="modal__head">
                <h3>Eliminar canción</h3>
                <button class="modal__close" data-close aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal__body">Acción irreversible. ¿Borrar definitivamente?</div>
            <div class="modal__foot">
                <button class="studio__btn studio__btn--ghost" data-close>Cancelar</button>
                <button class="studio__btn" id="deleteConfirm">Borrar</button>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
    // Animación reveal
    (function() {
        const io = new IntersectionObserver(entries =>
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('reveal--show');
                    io.unobserve(e.target);
                }
            }), {
                threshold: .1
            });
        document.querySelectorAll('.studio__row.reveal').forEach(el => io.observe(el));
    })();

    // Modal borrar
    let activeId = null;
    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;
        e.preventDefault();
        activeId = btn.closest('.studio__row').dataset.id;
        document.getElementById('modal-delete').classList.add('show');
    });
    document.querySelectorAll('[data-close]').forEach(el =>
        el.addEventListener('click', () => document.getElementById('modal-delete').classList.remove('show'))
    );
    document.getElementById('deleteConfirm').onclick = () => {
        if (activeId) {
            document.querySelector(`.studio__row[data-id="${activeId}"] form`).submit();
        }
    };
    </script>
</body>

</html>