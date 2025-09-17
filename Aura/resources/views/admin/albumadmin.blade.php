<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AURA — Administración de Álbumes</title>
    @vite('resources/css/admin.css')
    @vite('resources/js/admin.js')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    .avatar-img {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #333
    }

    .inspector-grid {
        display: flex;
        gap: 2rem;
        padding: 1rem
    }

    .inspector-left {
        flex: 1;
        display: flex;
        gap: 1rem;
        align-items: flex-start
    }

    .inspector-avatar img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #444
    }

    .inspector-info h4 {
        margin: 0;
        font-size: 1.2rem;
        color: #fff
    }

    .inspector-info p {
        margin: .3rem 0;
        font-size: .9rem;
        color: #aaa
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: .5rem 0 0
    }

    .info-list li {
        font-size: .9rem;
        color: #ccc;
        margin-bottom: .3rem
    }

    .inspector-right {
        flex: 1;
        display: flex;
        flex-direction: column
    }

    .inspector-actions {
        margin-top: 1rem;
        display: flex;
        gap: .5rem
    }

    .studio__toolbar {
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .studio__title {
        margin: 0;
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
    @include('components.footer')

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
                            <h1 class="studio__title"><i class="fa-solid fa-film"></i> Álbumes</h1>
                        </div>
                        <div class="admin-nav" style="display:flex; gap:10px; justify-content:center; margin:20px 0;">
    <a href="{{ route('admin') }}" class="btn-admin">
        <i class="fa-solid fa-music"></i> Canciones
    </a>
    <a href="{{ route('usuarioadmin') }}" class="btn-admin">
        <i class="fa-solid fa-users"></i> Usuarios
    </a>
</div><br>


                        <div class="studio__table" id="studioTable">
                            <div class="studio__thead">
                                <div class="c1"></div>
                                <div class="c2">Usuario</div>
                                <div class="c3">Álbum</div>
                                <div class="c4">Género</div>
                                <div class="c5">Fecha</div>
                                <div class="c6">Canciones</div>
                                <div class="c7 ta-r">Acciones</div>
                            </div>

                            @forelse($albumes as $album)
                            <div class="studio__row reveal" data-id="{{ $album->id }}">

                                <div class="c1">
                                    <img src="{{ $album->user->avatar_url ?? asset('img/default-avatar.png') }}"
                                        alt="Avatar de {{ $album->user->nombre ?? 'Usuario' }}" class="avatar-img"
                                        loading="lazy">

                                </div>



                                <!-- Usuario -->
                                <div class="c2">
                                    <div class="vtitle">

                                        {{ $album->user->nombre_artistico ?? $album->user->nombre ?? $album->user->email }}
                                    </div>
                                    <small>Usuario: {{ $album->user->nombre }}</small>
                                </div>


                                <!-- Título -->
                                <div class="c3"><span class="pill pill--publico">{{ $album->title }}</span></div>

                                <!-- Género -->
                                <div class="c4"><span class="pill pill--none">{{ $album->genre ?? '—' }}</span></div>

                                <!-- Fecha -->
                                <div class="c5">
                                    {{ $album->release_date 
      ? \Carbon\Carbon::parse($album->release_date)->format('d M Y') 
      : $album->created_at->format('d M Y') }}
                                </div>


                                <!-- Canciones -->
                                <div class="c6">
                                    <span class="songcount" aria-label="Canciones subidas">
                                        <i class="fa-solid fa-music"></i>
                                        <b class="songs__num">{{ $album->songs->count() }}</b>
                                        <small>canciones</small>
                                    </span>
                                </div>

                                <!-- Acciones -->
                                <div class="c7">
                                    <div class="actions ta-r">
                                        <form action="{{ route('album.destroy', $album->id) }}" method="POST"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar este álbum junto con sus canciones?')">
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
                            <div class="studio__row">
                                <div class="c2">No hay álbumes registrados.</div>
                            </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Reveal
    (function() {
        const io = new IntersectionObserver(es => es.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('reveal--show');
                io.unobserve(e.target)
            }
        }), {
            threshold: .1
        });
        document.querySelectorAll('.studio__row.reveal').forEach(el => io.observe(el));
    })();
    </script>
</body>

</html>


<script>
// Reveal
(function() {
    const io = new IntersectionObserver(es => es.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('reveal--show');
            io.unobserve(e.target)
        }
    }), {
        threshold: .1
    });
    document.querySelectorAll('.studio__row.reveal').forEach(el => io.observe(el));
})();

// Pulse
document.addEventListener('click', e => {
    const b = e.target.closest('[data-animate="pulse"]');
    if (!b) return;
    b.classList.remove('pulse');
    void b.offsetWidth;
    b.classList.add('pulse');
});

let activeId = null;
const openModal = (sel) => {
    const el = document.querySelector(sel);
    if (!el) return;
    el.classList.add('show');
    el.setAttribute('aria-hidden', 'false');
};
const closeAll = () => {
    document.querySelectorAll('.modal.show').forEach(m => {
        m.classList.remove('show');
        m.setAttribute('aria-hidden', 'true');
    });
};

// Cerrar modales
document.addEventListener('click', e => {
    if (e.target.matches('.modal,[data-close]')) closeAll();
});

// Botones de acciones
document.addEventListener('click', e => {
    const act = e.target.closest('.btn-act');
    if (!act) return;
    e.stopPropagation();
    activeId = act.getAttribute('data-id') || act.closest('.studio__row')?.dataset.id || null;
    const modalSel = act.getAttribute('data-open');
    if (modalSel) openModal(modalSel);
});

// Click en fila -> Inspector
document.addEventListener('click', e => {
    const row = e.target.closest('.studio__row');
    if (!row || e.target.closest('.actions')) return;
    activeId = row.dataset.id;

    const title = row.querySelector('.c2 .vtitle')?.textContent.trim() || 'Sin título';
    const urlEl = row.querySelector('.c2 .vlink');
    const url = urlEl?.href || '#';
    const vis = row.querySelector('.c3 .pill')?.textContent.trim() || '—';
    const res = row.querySelector('.c4 .pill')?.textContent.trim() || '—';
    const date = row.querySelector('.c5')?.textContent.trim() || '—';
    const songs = row.querySelector('.c6 .songs__num, .c6 .songcount b')?.textContent.trim() || '0';

    document.getElementById('inspectorTitle').textContent = title;
    const link = document.getElementById('inspectorUrl');
    link.textContent = url;
    link.href = url;
    document.getElementById('inspectorVis').textContent = vis;
    document.getElementById('inspectorRes').textContent = res;
    document.getElementById('inspectorDate').textContent = date;
    const s = document.getElementById('inspectorSongs');
    if (s) s.textContent = songs;

    const th = row.querySelector('.thumb');
    const thumb = document.getElementById('inspectorThumb');
    if (thumb) {
        thumb.style.background = getComputedStyle(th || document.body).background || '#2a2b31';
        thumb.style.border = '1px solid var(--border)';
    }

    openModal('#modal-inspector');
});

// Acciones inspector
document.getElementById('inspectorAccept').onclick = () => {
    closeAll();
    openModal('#modal-approve');
}
document.getElementById('inspectorDeny').onclick = () => {
    const notes = document.getElementById('inspectorNotes').value;
    const denyArea = document.getElementById('denyReason');
    if (denyArea) denyArea.value = notes;
    closeAll();
    openModal('#modal-deny');
}
document.getElementById('inspectorDelete').onclick = () => {
    closeAll();
    openModal('#modal-delete');
}

// Confirmaciones
document.getElementById('approveConfirm').onclick = () => {
    closeAll();
    alert("Video " + activeId + " aceptado");
}
document.getElementById('denyConfirm').onclick = () => {
    closeAll();
    alert("Video " + activeId + " denegado. Motivo: " + (document.getElementById('denyReason')?.value || ''));
}
document.getElementById('deleteConfirm').onclick = () => {
    document.querySelector(`.studio__row[data-id="${activeId}"]`)?.remove();
    closeAll();
    alert("Video " + activeId + " eliminado");
}
</script>