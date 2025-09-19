<?php

use Illuminate\Support\Facades\Route;

// Controladores
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadMusicController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\DriveMediaController;
use App\Http\Controllers\MiControlador;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CancionController;
use App\Http\Controllers\TraductorController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LikeApiController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\LyricsController;
use App\Http\Controllers\EqualizerController;
use App\Http\Controllers\PreferenciasController;
use App\Http\Controllers\Auth\PasswordController;
use App\Models\Cancion;
use App\Models\Album;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\AudioStreamController;

// ===== Página principal =====
Route::get('/', fn () => view('menu'))->name('menu');

/*
|--------------------------------------------------------------------------
| Rutas públicas (sin auth)
|--------------------------------------------------------------------------
*/

// 🔎 Endpoint JSON de búsqueda (público)
Route::get('/search', [SearchController::class, 'buscar'])->name('search.json');

// Lanzamientos públicos de un perfil
Route::get('/perfil/{userId}/lanzamientos', [PerfilController::class, 'releasesAll'])
    ->name('perfil.releasesAll');

/*
|--------------------------------------------------------------------------
| Estado público de "like" por canción (consultado por el reproductor)
|--------------------------------------------------------------------------
*/
Route::get('/canciones/{cancion}/liked', [LikeApiController::class, 'liked'])
    ->name('canciones.liked');
Route::get('/api/canciones/{cancion}/liked', [LikeApiController::class, 'liked'])
    ->name('api.canciones.like.state');

/*
|--------------------------------------------------------------------------
| VER ÁLBUM (PÚBLICO: el dueño puede editar en la UI; visitantes solo ven)
|--------------------------------------------------------------------------
*/
Route::get('/albums/{id}', [AlbumController::class, 'show'])->name('album.show');
Route::get('/album/{id}',  [AlbumController::class, 'show'])->name('album.show.legacy');

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // ===== Cuenta =====
    Route::get('/cuenta', fn () => view('cuenta'))->name('cuenta');
    Route::post('/cuenta/password', [PasswordController::class, 'update'])
        ->name('perfil.changePassword');

    // ===== Idioma (perfil) =====
    Route::post('/perfil/language', [PerfilController::class, 'setLanguage'])
        ->name('perfil.language');

    /*
    |--------------------------------------------------------------------------
    | ME GUSTA — ÁLBUMES (UI)
    |--------------------------------------------------------------------------
    | Frontend principal en /likes/albums.
    */
    Route::get('/likes/albums', [LikeController::class, 'albumsPage'])->name('likes.albums');
    Route::get('/like_menu', fn () => redirect()->route('likes.albums'))->name('like_menu');
    Route::get('/me-gusta/albums', fn () => redirect()->route('likes.albums'))->name('likes.albums.alias');

    // Quitar de Me gusta (álbum) — borra likes de todas sus canciones
    Route::delete('/likes/albums/{album}', [LikeController::class, 'unlikeAlbum'])
        ->name('likes.albums.destroy');

    /*
    |--------------------------------------------------------------------------
    | Me Gusta por canción (JSON: usado por el footer)
    |--------------------------------------------------------------------------
    */
    Route::post('/canciones/{cancion}/like', [LikeApiController::class, 'toggle'])
        ->name('canciones.like');
    Route::post('/canciones/{cancion}/like/toggle', [LikeApiController::class, 'toggle'])
        ->name('canciones.like.toggle');
    Route::post('/api/canciones/{cancion}/like/toggle', [LikeApiController::class, 'toggle'])
        ->name('api.canciones.like.toggle');

    // (Antigua) Página "Me gusta" de canciones (si la usas)
    Route::get('/like', [CancionController::class, 'like'])->name('like');

    // ===== Canción: ver y letras
    Route::get('/cancion/{cancion}', [CancionController::class, 'show'])->name('cancion.show');
    Route::get('/canciones/{cancion}/lyrics', [LyricsController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Playlists (endpoints del modal/JS + aliases API)
    |--------------------------------------------------------------------------
    */
    // Aliases “API” legacy
    Route::get('/api/my-playlists',        [PlaylistController::class, 'myPlaylists']);
    Route::post('/api/playlists/create',   [PlaylistController::class, 'quickStore']);
    Route::post('/playlists/{playlist}/add-song/{cancion}', [PlaylistController::class, 'addSong'])
        ->name('playlists.add-song');

    // ✅ NUEVO: quitar canción de playlist (DELETE)
    Route::delete('/playlists/{playlist}/remove-song/{cancion}', [PlaylistController::class, 'removeSong'])
        ->name('playlists.remove-song');

    // Endpoints con nombre (usados en data-attributes del Blade)
    Route::get('/playlists/mine', [PlaylistController::class, 'myPlaylists'])->name('playlists.mine');
    Route::post('/playlists/quick', [PlaylistController::class, 'quickStore'])->name('playlists.quickStore');

    // CRUD RESTful de playlists (index/show/create/store/edit/update/destroy)
    Route::resource('playlists', PlaylistController::class);

    // ===== Vistas principales (Blade suelto)
    Route::get('/menu', fn () => view('menu'))->name('menu');
    Route::get('/menu_artista', fn () => view('menu_artista'))->name('menu_artista');
    // ⚠️ Esta ruta suelta de vista no pasa $playlist, se mantiene por compat. Evita usarla directamente.
    Route::get('/playlist_card', fn () => view('playlist_card'))->name('playlist_card');
    Route::get('/preferencias', fn () => view('preferencias'))->name('preferencias');
    Route::get('/editar-perfil', fn () => view('editar-perfil'))->name('editar-perfil');
    Route::get('/seguridad', fn () => view('seguridad'))->name('seguridad');
    Route::get('/cambiar-usuario', fn () => view('cambiar-usuario'))->name('cambiar-usuario');
    Route::get('/recientes', fn () => view('recientes'))->name('recientes');

    // Menú de álbumes (acepta ?album=ID para ver álbum de OTRO artista en modo lectura)
    Route::get('/menu_album', [PerfilController::class, 'albumsMenu'])->name('menu_album');

    // Cambiar tipo de cuenta (usuario ↔ artista)
    Route::post('/perfil/toggle-role', [PerfilController::class, 'toggleRole'])
        ->name('perfil.toggleRole');

    // ===== Admin =====
    function checkAdminAccess() {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        return $user->email === 'juanzpaescobar@gmail.com' && Hash::check('Junaco2156+', $user->password);
    }

    // ===== Rutas restringidas =====
    Route::get('/admin', function () {
        if (!checkAdminAccess()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Acceso restringido.']);
        }
        return app(\App\Http\Controllers\CancionController::class)->adminIndex();
    })->name('admin');

    Route::get('/albumadmin', function () {
        if (!checkAdminAccess()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Acceso restringido.']);
        }
        $albumes = \App\Models\Album::with(['user', 'songs'])->get();
        return view('admin.albumadmin', compact('albumes'));
    })->name('albumadmin');

    Route::get('/usuarioadmin', function () {
        if (!checkAdminAccess()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Acceso restringido.']);
        }
        $usuarios = \App\Models\User::all();
        return view('admin.usuarioadmin', compact('usuarios'));
    })->name('usuarioadmin');

    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');

    /*
    |--------------------------------------------------------------------------
    | Álbumes (actualización / eliminación)
    |--------------------------------------------------------------------------
    */
    // Actualizar (título/portada) — acepta PATCH o POST con _method=PATCH
    Route::match(['patch', 'post'], '/albums/{id}', [AlbumController::class, 'update'])
        ->name('albums.update');

    // Eliminar álbum (si usas ProfileController@destroyAlbum)
    Route::delete('/albums/{id}', [ProfileController::class, 'destroyAlbum'])
        ->name('profile.albums.destroy');
    Route::delete('/album/{id}', [ProfileController::class, 'destroyAlbum'])
        ->name('profile.albums.destroy.legacy');

    // Listado general de álbumes (solo para usuarios logueados, opcional)
    Route::get('/albumes', function () {
        $albumes = Album::with('user')->latest()->get();
        return view('album_principal', compact('albumes'));
    })->name('album.index');

    /*
    |--------------------------------------------------------------------------
    | Perfil (artistas/usuarios)
    |--------------------------------------------------------------------------
    */
    Route::get('/perfil/{id}', [PerfilController::class, 'show'])->name('perfil.show');
    Route::post('/perfil/update', [PerfilController::class, 'update'])->name('perfil.update');
    Route::post('/perfil/song', [PerfilController::class, 'storeSong'])->name('perfil.storeSong');
    Route::post('/perfil/follow/{userId}', [PerfilController::class, 'follow'])->name('perfil.follow');
    Route::post('/perfil/unfollow/{userId}', [PerfilController::class, 'unfollow'])->name('perfil.unfollow');
    Route::get('/follow_artist', [PerfilController::class, 'followArtistList'])->name('follow_artist');

    /*
    |--------------------------------------------------------------------------
    | Búsqueda (protegida si la usas dentro del panel)
    |--------------------------------------------------------------------------
    */
    Route::get('/buscar', [SearchController::class, 'buscar'])->name('buscar');

    /*
    |--------------------------------------------------------------------------
    | Dashboard (Breeze/Jetstream - requiere email verificado)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', fn () => view('menu'))->middleware(['verified'])->name('menu');

    /*
    |--------------------------------------------------------------------------
    | Profile (Breeze/Jetstream)
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Música (subidas)
    |--------------------------------------------------------------------------
    */
    Route::get('/musica/subir', [UploadMusicController::class, 'create'])->name('musica.subir');
    Route::post('/musica/subir-cancion', [UploadMusicController::class, 'storeSong'])->name('songs.store');
    Route::post('/musica/subir-albums', [UploadMusicController::class, 'storeAlbum'])->name('albums.store');

    /*
    |--------------------------------------------------------------------------
    | Búsqueda (vistas varias)
    |--------------------------------------------------------------------------
    */
    Route::get('/busqueda_album', [MiControlador::class, 'mostrarVista'])->name('busqueda_album');
    Route::get('/busqueda_individual', [MiControlador::class, 'mostrarVistaIndividual'])->name('busqueda_individual');

    // ===== Canciones: actualizar y eliminar =====
    Route::match(['patch', 'post'], '/canciones/{cancion}', [CancionController::class, 'update'])
        ->name('canciones.update');
    Route::match(['patch', 'post'], '/cancion/{cancion}', [CancionController::class, 'update'])
        ->name('cancion.update');
    Route::delete('/cancion/{cancion}', [CancionController::class, 'destroy'])->name('cancion.destroy');
    Route::delete('/canciones/{cancion}', [CancionController::class, 'destroy'])->name('canciones.destroy');
});

/*
|--------------------------------------------------------------------------
| Utilidades y varias
|--------------------------------------------------------------------------
*/

// ===== Debug =====
Route::get('/phpinfo', fn () => dd(PHP_BINARY, php_ini_loaded_file()))->name('phpinfo');

// ===== Auth scaffolding (Breeze/Jetstream/etc.) =====
require __DIR__ . '/auth.php';

// ===== Test helper =====
Route::get('/test-helper', function () {
    return drive_direct_url('https://drive.google.com/file/d/1OdB2xNkFQsg9S6yG-PaLM8W79_WuK1js/view');
})->name('test-helper');

// ===== Cambiar idioma (público) =====
Route::post('/languages/{lang}', function (string $lang) {
    if (!in_array($lang, ['es', 'en'])) {
        $lang = 'en';
    }

    if (Auth::check()) {
        $user = Auth::user();
        $user->idioma = $lang;
        $user->save();
    } else {
        Session::put('locale', $lang);
    }

    App::setLocale($lang);

    return back()->with('status', __('account.language_changed'));
})->name('languages');

// ===== Ruta para el traductor =====
Route::post('/traducir', [TraductorController::class, 'traducir'])->name('traducir');

// ===== Locale test / Auth check =====
Route::get('/locale-test', function () {
    if (Auth::check() && Auth::user()->idioma) {
        app()->setLocale(Auth::user()->idioma);
    }
    return [
        'user'   => Auth::user()->idioma ?? null,
        'locale' => app()->getLocale(),
        'text'   => __('messages.welcome'),
    ];
})->name('locale-test');

Route::get('/auth-check', function () {
    return [
        'logged_in' => auth()->check(),
        'user'      => auth()->user(),
    ];
})->name('auth-check');

/*
|--------------------------------------------------------------------------
| Equalizador
|--------------------------------------------------------------------------
*/
Route::post('/equalizer/save', [EqualizerController::class, 'save'])
    ->name('eq.save')
    ->middleware('auth');

Route::get('/preferencias', [PreferenciasController::class, 'index'])
    ->name('preferencias')
    ->middleware('auth');

    Route::get('/audios/{filename}', [AudioStreamController::class, 'stream'])
    ->where('filename', '.*')
    ->name('audios.stream');

    // Soporte
Route::post('/support/send', [SupportController::class, 'send'])
    ->name('support.send');
