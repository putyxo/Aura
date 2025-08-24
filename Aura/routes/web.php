<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadMusicController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\DriveMediaController;
use App\Http\Controllers\MiControlador;
use App\Http\Controllers\PlaylistController; // ← MOVER AQUÍ
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TraductorController;





Route::post('/traducir', [TraductorController::class, 'traducir'])->name('traducir');

Route::get('/', fn() => view('welcome'));

Route::get('/menu', fn() => view('menu'));
Route::get('/dashboard', fn() => view('dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/menu', fn() => view('menu'))->name('menu');
Route::get('/menu_artista', fn() => view('menu_artista'))->name('menu_artista');
Route::get('/menu_album', fn() => view('menu_album'))->name('menu_album');
Route::get('/ed_perfil', fn() => view('ed_perfil'))->name('ed_perfil');
Route::get('/playlist_card', fn() => view('playlist_card'))->name('playlist_card');
Route::get('/like', fn() => view('like'))->name('like');
Route::get('/follow_artist', fn() => view('follow_artist'))->name('follow_artist');
Route::get('/prueba', fn() => view('prueba'))->name('prueba');
Route::get('/recientes', fn() => view('recientes'))->name('recientes');
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->name('playlists.show');


/* Vista de playlists (usa el nombre de tu archivo Blade) */
Route::get('/playlist', fn() => view('playlist'))->name('playlist'); // ← si tu Blade es resources/views/playlist.blade.php

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'es'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
});

    
    Route::get('/musica/subir', [UploadMusicController::class, 'create'])->name('musica.subir');
    Route::post('/musica/subir-cancion', [UploadMusicController::class, 'storeSong'])->name('songs.store');
    Route::post('/musica/subir-albums', [UploadMusicController::class, 'storeAlbum'])->name('albums.store');

    Route::get('/busqueda_album', [MiControlador::class, 'mostrarVista']);
    Route::get('/busqueda_album', fn() => view('busqueda_album'))->name('busqueda_album');

    Route::get('/busqueda_individual', [MiControlador::class, 'mostrarVistaIndividual']);
    Route::get('/busqueda_individual', fn() => view('busqueda_individual'))->name('busqueda_individual');

    // Página que MUESTRA la grilla en playlist.blade.php
    Route::get('/playlist', [PlaylistController::class, 'index'])->name('playlist');

    // Endpoint que RECIBE el form del modal
    Route::post('/playlists', [PlaylistController::class, 'store'])->name('playlists.store');
});
Route::resource('playlists', PlaylistController::class);
// Genera:
// playlists.index, playlists.create, playlists.store, playlists.show,
// playlists.edit, playlists.update, playlists.destroy

Route::get('/google-drive/auth', [GoogleDriveController::class, 'redirectToGoogle']);
Route::get('/google-drive/callback', [GoogleDriveController::class, 'handleCallback']);
Route::post('/google-drive/upload', [GoogleDriveController::class, 'upload'])->name('google.upload');

require __DIR__.'/auth.php';

Route::get('/phpinfo', fn() => dd(PHP_BINARY, php_ini_loaded_file()));

Route::get('/media/drive/{id}', [DriveMediaController::class, 'stream'])
    ->name('media.drive')
    ->middleware('auth');
