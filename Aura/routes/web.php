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
use Intervention\Image\Facades\Image;

// Modelos usados en closures
use App\Models\Album;

// ===== Página principal =====
Route::get('/', fn () => view('welcome'))->name('welcome');

/*
|---------------------------------------------------------------------------  
| Rutas públicas (sin auth)
|---------------------------------------------------------------------------  
| Mantén aquí lo que deba existir sin sesión iniciada.
*/
Route::get('/perfil/{userId}/lanzamientos', [PerfilController::class, 'releasesAll'])
    ->name('perfil.releasesAll');

/*
|---------------------------------------------------------------------------  
| Estado público de "like" por canción (el reproductor lo consulta sin login)
|---------------------------------------------------------------------------  
*/
Route::get('/canciones/{cancion}/liked', [LikeApiController::class, 'liked'])
    ->name('canciones.liked');
Route::get('/api/canciones/{cancion}/liked', [LikeApiController::class, 'liked'])
    ->name('api.canciones.like.state');

// ===== Rutas protegidas (requieren login) =====
Route::middleware('auth')->group(function () {

    /*
    |---------------------------------------------------------------------------  
    | ME GUSTA — ÁLBUMES (UI)
    |---------------------------------------------------------------------------  
    | Frontend principal en /likes/albums.
    | Se mantienen alias antiguos con redirección para evitar 404.
    */
    Route::get('/likes/albums', [LikeController::class, 'albumsPage'])->name('likes.albums');
    Route::get('/like_menu', fn () => redirect()->route('likes.albums'))->name('like_menu');
    Route::get('/me-gusta/albums', fn () => redirect()->route('likes.albums'))->name('likes.albums.alias');

    // Quitar de Me gusta (álbum) — borra likes de todas sus canciones
    Route::delete('/likes/albums/{album}', [LikeController::class, 'unlikeAlbum'])
        ->name('likes.albums.destroy');

    // Ruta para eliminar una playlist
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy'])->name('playlists.destroy');

    /*
    |---------------------------------------------------------------------------  
    | Me Gusta por canción (JSON: usado por el footer)
    |---------------------------------------------------------------------------  
    */
    Route::post('/canciones/{cancion}/like', [LikeApiController::class, 'toggle'])
        ->name('canciones.like');
    Route::post('/api/canciones/{cancion}/like/toggle', [LikeApiController::class, 'toggle'])
        ->name('api.canciones.like.toggle');

    // (Antigua) Página "Me gusta" de canciones (si la usas)
    Route::get('/like', [CancionController::class, 'like'])->name('like');

    /*
    |---------------------------------------------------------------------------  
    | Playlists (llamadas rápidas desde el footer / modal)
    |---------------------------------------------------------------------------  
    */
    Route::get('/api/my-playlists', [PlaylistController::class, 'myPlaylists']);
    Route::post('/api/playlists/create', [PlaylistController::class, 'quickStore']);
    Route::post('/playlists/{playlist}/add-song/{cancion}', [PlaylistController::class, 'addSong']);

    // ===== Vistas principales (Blade suelto) =====
    Route::get('/menu', fn() => view('menu'))->name('menu');
    Route::get('/menu_artista', fn() => view('menu_artista'))->name('menu_artista');
    Route::get('/playlist_card', fn() => view('playlist_card'))->name('playlist_card');
    Route::get('/preferencias', fn() => view('preferencias'))->name('preferencias');
    Route::get('/cuenta', fn() => view('cuenta'))->name('cuenta');
    Route::get('/editar-perfil', fn() => view('editar-perfil'))->name('editar-perfil');
    Route::get('/seguridad', fn() => view('seguridad'))->name('seguridad');
    Route::get('/cambiar-usuario', fn() => view('cambiar-usuario'))->name('cambiar-usuario');
    Route::get('/recientes', fn() => view('recientes'))->name('recientes');
    Route::get('/estadisticas', fn() => view('estadisticas'))->name('estadisticas');
    Route::get('/artistasadmin', fn() => view('artistasadmin'))->name('artistasadmin');
    Route::get('/menu_album', [ProfileController::class, 'menuAlbum'])->name('menu_album');

    // ===== Admin =====
    Route::get('/admin', fn() => view('/admin/admin'))->name('admin');
    Route::get('/albumadmin', fn() => view('/admin/albumadmin'))->name('albumadmin');
    Route::get('/artistasadmin', fn() => view('/admin/artistasadmin'))->name('artistasadmin');
    Route::get('/usuarioadmin', fn() => view('/admin/usuarioadmin'))->name('usuarioadmin');
    /*
    |---------------------------------------------------------------------------  
    | Álbumes
    |---------------------------------------------------------------------------  
    | Incluye alias /album/{id} y /albums/{id} para compatibilidad.
    */
    // Menú de álbumes en perfil (verifica que exista en PerfilController)
    Route::get('/menu_album', [PerfilController::class, 'albumsMenu'])->name('menu_album');

    // Ver álbum
    Route::get('/album/{id}', [AlbumController::class, 'show'])->name('album.show.legacy');
    Route::get('albums/{id}', [AlbumController::class, 'show'])->name('album.show');

    // Eliminar álbum
    Route::delete('/album/{id}', [AlbumController::class, 'destroy'])->name('album.destroy.legacy');
    Route::delete('/albums/{id}', [AlbumController::class, 'destroy'])->name('album.destroy');

    // Listado general de álbumes
    Route::get('/albumes', function () {
        $albumes = Album::with('user')->latest()->get();
        return view('album_principal', compact('albumes'));
    })->name('album.index');

    /*
    |---------------------------------------------------------------------------  
    | Media desde Google Drive (stream/preview)
    |---------------------------------------------------------------------------  
    */
    Route::get('/media/{id}', [DriveMediaController::class, 'stream'])->name('media.drive');

    /*
    |---------------------------------------------------------------------------  
    | Perfil (artistas/usuarios)
    |---------------------------------------------------------------------------  
    */
    Route::get('/perfil/{id}', [PerfilController::class, 'show'])->name('perfil.show');
    Route::post('/perfil/update', [PerfilController::class, 'update'])->name('perfil.update');
    Route::post('/perfil/song', [PerfilController::class, 'storeSong'])->name('perfil.storeSong');
    Route::post('/perfil/follow/{userId}', [PerfilController::class, 'follow'])->name('perfil.follow');
    Route::post('/perfil/unfollow/{userId}', [PerfilController::class, 'unfollow'])->name('perfil.unfollow');
    Route::get('/follow_artist', [PerfilController::class, 'followArtistList'])->name('follow_artist');

    /*
    |---------------------------------------------------------------------------  
    | Búsqueda
    |---------------------------------------------------------------------------  
    */
    Route::get('/buscar', [SearchController::class, 'buscar'])->name('buscar');

    /*
    |---------------------------------------------------------------------------  
    | Dashboard (verificación de email si usas Breeze/Jetstream)
    |---------------------------------------------------------------------------  
    */
    Route::get('/dashboard', fn () => view('dashboard'))->middleware(['verified'])->name('dashboard');

    /*
    |---------------------------------------------------------------------------  
    | Profile (Breeze/Jetstream)
    |---------------------------------------------------------------------------  
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |---------------------------------------------------------------------------  
    | Música (subidas)
    |---------------------------------------------------------------------------  
    */
    Route::get('/musica/subir', [UploadMusicController::class, 'create'])->name('musica.subir');
    Route::post('/musica/subir-cancion', [UploadMusicController::class, 'storeSong'])->name('songs.store');
    Route::post('/musica/subir-albums', [UploadMusicController::class, 'storeAlbum'])->name('albums.store');

    /*
    |---------------------------------------------------------------------------  
    | Búsqueda (vistas varias)
    |---------------------------------------------------------------------------  
    */
    Route::get('/busqueda_album', [MiControlador::class, 'mostrarVista'])->name('busqueda_album');
    Route::get('/busqueda_individual', [MiControlador::class, 'mostrarVistaIndividual'])->name('busqueda_individual');

    //Album
    Route::delete('/menu_album', [AlbumController::class, 'destroy'])->name('album.destroy');

    // ===== API JSON opcional para likes (sin colisión con la HTML) =====  
    Route::post('/api/canciones/{cancion}/like/toggle', [LikeApiController::class, 'toggle'])
        ->name('api.canciones.like.toggle');
    Route::get('/api/canciones/{cancion}/liked', [LikeApiController::class, 'liked'])
        ->name('api.canciones.like.state');
    // ===== Eliminar canción (coincide con tu Blade: route('cancion.destroy', $id)) =====  
    Route::delete('/cancion/{cancion}', [CancionController::class, 'destroy'])->name('cancion.destroy');
    Route::delete('/canciones/{cancion}', [CancionController::class, 'destroy'])->name('canciones.destroy');
});

// ===== Recurso RESTful de Playlists =====
// (Déjalo público aquí; si lo quieres privado, muévelo al middleware 'auth')
Route::resource('playlists', PlaylistController::class);

// ===== Google Drive OAuth / Upload =====
Route::get('/google-drive/auth', [GoogleDriveController::class, 'redirectToGoogle']);
Route::get('/google-drive/callback', [GoogleDriveController::class, 'handleCallback']);
Route::post('/google-drive/upload', [GoogleDriveController::class, 'upload'])->name('google.upload');

// ===== Debug =====
Route::get('/phpinfo', fn () => dd(PHP_BINARY, php_ini_loaded_file()));

// ===== Auth scaffolding (Breeze/Jetstream/etc.) =====
require __DIR__ . '/auth.php';

// ===== Test helper =====
Route::get('/test-helper', function () {
    return drive_direct_url('https://drive.google.com/file/d/1OdB2xNkFQsg9S6yG-PaLM8W79_WuK1js/view');
});

// ===== Traductor =====
Route::post('/traducir', [TraductorController::class, 'traducir'])->name('traducir');

use App\Http\Controllers\EqualizerController;

Route::post('/equalizer/save', [EqualizerController::class, 'save'])
    ->name('eq.save')
    ->middleware('auth');

Route::get('/preferencias', [\App\Http\Controllers\PreferenciasController::class, 'index'])
    ->name('preferencias')
    ->middleware('auth');
