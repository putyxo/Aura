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

/*
|--------------------------------------------------------------------------|
| Web Routes                                                               |
|--------------------------------------------------------------------------|
*/

// ===== Página principal =====
Route::get('/', fn() => view('welcome'))->name('welcome'); // ahora abre index

// ===== Páginas públicas =====
Route::get('/welcome', fn() => view('welcome'))->name('welcome');
Route::get('/menu', fn() => view('menu'))->name('menu');
Route::get('/menu_artista', fn() => view('menu_artista'))->name('menu_artista');
Route::get('/menu_album', fn() => view('menu_album'))->name('menu_album');
Route::get('/playlist_card', fn() => view('playlist_card'))->name('playlist_card');
Route::get('/like', fn() => view('like'))->name('like');
Route::get('/follow_artist', fn() => view('follow_artist'))->name('follow_artist');
Route::get('/prueba', fn() => view('prueba'))->name('prueba');
Route::get('/recientes', fn() => view('recientes'))->name('recientes');


// Playlist show individual
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');

// Vista de playlists (blade directo)
Route::get('/playlist', fn() => view('playlist'))->name('playlist');

// ===== Perfil =====
Route::get('/perfil/{id}', [PerfilController::class, 'show'])->name('perfil.show');
Route::post('/perfil/update', [PerfilController::class, 'update'])->name('perfil.update');
Route::post('/perfil/song', [PerfilController::class, 'storeSong'])->name('perfil.storeSong');
// Ruta para seguir a un usuario
Route::post('/perfil/follow/{userId}', [PerfilController::class, 'follow'])->name('perfil.follow');

// Ruta para dejar de seguir a un usuario
Route::post('/perfil/unfollow/{userId}', [PerfilController::class, 'unfollow'])->name('perfil.unfollow');


// ===== Buscador =====
Route::get('/buscar', [SearchController::class, 'buscar'])->name('buscar');

// ===== Rutas protegidas (requieren login) =====
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'))
        ->middleware(['verified'])
        ->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Música
    Route::get('/musica/subir', [UploadMusicController::class, 'create'])->name('musica.subir');
    Route::post('/musica/subir-cancion', [UploadMusicController::class, 'storeSong'])->name('songs.store');
    Route::post('/musica/subir-albums', [UploadMusicController::class, 'storeAlbum'])->name('albums.store');

    // Búsqueda
    Route::get('/busqueda_album', [MiControlador::class, 'mostrarVista']);
    Route::get('/busqueda_album', fn() => view('busqueda_album'))->name('busqueda_album');
    Route::get('/busqueda_individual', [MiControlador::class, 'mostrarVistaIndividual']);
    Route::get('/busqueda_individual', fn() => view('busqueda_individual'))->name('busqueda_individual');
    Route::get('/follow_artist', [PerfilController::class, 'followArtistList'])->name('follow_artist');


    // Playlist (grilla y creación)
    Route::get('/playlist', [PlaylistController::class, 'index'])->name('playlist');
    Route::post('/playlists', [PlaylistController::class, 'store'])->name('playlists.store');
});

// ===== Recursos de Playlist =====
Route::resource('playlists', PlaylistController::class);

// ===== Google Drive =====
Route::get('/google-drive/auth', [GoogleDriveController::class, 'redirectToGoogle']);
Route::get('/google-drive/callback', [GoogleDriveController::class, 'handleCallback']);
Route::post('/google-drive/upload', [GoogleDriveController::class, 'upload'])->name('google.upload');

// ===== Media =====
Route::get('/media/drive/{id}', [DriveMediaController::class, 'stream'])
    ->name('media.drive');

// ===== Debug =====
Route::get('/phpinfo', fn() => dd(PHP_BINARY, php_ini_loaded_file()));

// ===== Auth =====
require __DIR__.'/auth.php';


Route::get('/busqueda_album', [MiControlador::class, 'mostrarVista']);


Route::get('/busqueda_album', function () {
    return view('busqueda_album'); // resources/views/nombre_de_la_vista.blade.php
})->name('busqueda_album');

Route::get('/busqueda_individual', [MiControlador::class, 'mostrarVistaIndividual']);


Route::get('/busqueda_individual', function () {
    return view('busqueda_individual'); // resources/views/nombre_de_la_vista.blade.php
})->name('busqueda_individual');


    