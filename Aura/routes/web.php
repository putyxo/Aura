<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadMusicController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\DriveMediaController;
use App\Http\Controllers\MiControlador;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CancionController;
use App\Models\Album;

// ===== Página principal =====
Route::get('/', fn() => view('welcome'))->name('welcome');

// ===== Rutas protegidas (requieren login) =====
Route::middleware('auth')->group(function () {
 // ❤️ Like de canciones
    Route::post('/canciones/{cancion}/like', [CancionController::class, 'toggleLike'])
        ->name('canciones.like');
    
        Route::get('/canciones/{cancion}/liked', [CancionController::class, 'liked'])
    ->name('canciones.liked');

    Route::get('/like', [CancionController::class, 'like'])
     ->name('like');
    // 📂 Listar playlists del usuario autenticado
    Route::get('/api/my-playlists', [PlaylistController::class, 'myPlaylists']);
    Route::post('/api/playlists/create', [PlaylistController::class, 'quickStore']);

    // ➕ Agregar canción a playlist
    Route::post('/playlists/{playlist}/add-song/{cancion}', [PlaylistController::class, 'addSong']);
    // Vistas principales
    Route::get('/menu', fn() => view('menu'))->name('menu');
    Route::get('/menu_artista', fn() => view('menu_artista'))->name('menu_artista');
    Route::get('/playlist_card', fn() => view('playlist_card'))->name('playlist_card');
    Route::get('/follow_artist', fn() => view('follow_artist'))->name('follow_artist');
    Route::get('/prueba', fn() => view('prueba'))->name('prueba');
    Route::get('/recientes', fn() => view('recientes'))->name('recientes');


    // Albumes
// En web.php
Route::get('/album/{id}', [AlbumController::class, 'show'])->name('album.show');

    Route::get('/albumes', function () {
        $albumes = Album::with('user')->get();
        return view('album_principal', compact('albumes'));
    });

    // ❤️ Like canciones
    Route::post('/canciones/{cancion}/like', [CancionController::class, 'toggleLike'])->name('canciones.like');

    // 📂 Playlists API
    Route::get('/api/my-playlists', [PlaylistController::class, 'myPlaylists']);
    Route::post('/playlists/{playlist}/add-song/{cancion}', [PlaylistController::class, 'addSong']);

    // Media desde Drive
    Route::get('/media/{id}', [DriveMediaController::class, 'stream'])->name('media.drive');

    // Playlist show individual
    Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');

    // Playlist (grilla y creación)
    Route::get('/playlist', [PlaylistController::class, 'index'])->name('playlist');
    Route::post('/playlists', [PlaylistController::class, 'store'])->name('playlists.store');

    // Perfil
    Route::get('/perfil/{id}', [PerfilController::class, 'show'])->name('perfil.show');
    Route::post('/perfil/update', [PerfilController::class, 'update'])->name('perfil.update');
    Route::post('/perfil/song', [PerfilController::class, 'storeSong'])->name('perfil.storeSong');
    Route::post('/perfil/follow/{userId}', [PerfilController::class, 'follow'])->name('perfil.follow');
    Route::post('/perfil/unfollow/{userId}', [PerfilController::class, 'unfollow'])->name('perfil.unfollow');

    // Buscador
    Route::get('/buscar', [SearchController::class, 'buscar'])->name('buscar');

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'))->middleware(['verified'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Música
// Ruta para mostrar el formulario de subida (GET)
Route::get('/musica/subir', [UploadMusicController::class, 'create'])->name('musica.subir');

// Ruta para manejar la subida de canciones (POST)
Route::post('/musica/subir-cancion', [UploadMusicController::class, 'storeSong'])->name('songs.store');

// Ruta para manejar la subida de álbumes (POST)
Route::post('/musica/subir-albums', [UploadMusicController::class, 'storeAlbum'])->name('albums.store');


    Route::delete('/album/{id}', [AlbumController::class, 'destroy'])->name('album.destroy');
    Route::delete('/cancion/{id}', [CancionController::class, 'destroy'])->name('cancion.destroy');

    // Búsqueda
    Route::get('/busqueda_album', [MiControlador::class, 'mostrarVista'])->name('busqueda_album');
    Route::get('/busqueda_individual', [MiControlador::class, 'mostrarVistaIndividual'])->name('busqueda_individual');
    Route::get('/follow_artist', [PerfilController::class, 'followArtistList'])->name('follow_artist');
});

// ===== Recursos de Playlist (RESTful) =====
Route::resource('playlists', PlaylistController::class);

// ===== Google Drive =====
Route::get('/google-drive/auth', [GoogleDriveController::class, 'redirectToGoogle']);
Route::get('/google-drive/callback', [GoogleDriveController::class, 'handleCallback']);
Route::post('/google-drive/upload', [GoogleDriveController::class, 'upload'])->name('google.upload');

// ===== Debug =====
Route::get('/phpinfo', fn() => dd(PHP_BINARY, php_ini_loaded_file()));

// ===== Auth =====
require __DIR__.'/auth.php';

// ===== Test helper =====
Route::get('/test-helper', function () {
    return drive_direct_url('https://drive.google.com/file/d/1OdB2xNkFQsg9S6yG-PaLM8W79_WuK1js/view');
});
