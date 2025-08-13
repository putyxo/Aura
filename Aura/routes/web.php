<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadMusicController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\DriveMediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/menu', function () {
    return view('menu');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/menu', fn() => view('menu'));


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/musica/subir', [UploadMusicController::class, 'create'])->name('musica.subir');
    Route::post('/musica/subir-cancion', [UploadMusicController::class, 'storeSong'])->name('songs.store');
    Route::post('/musica/subir-albums', [UploadMusicController::class, 'storeAlbum'])->name('albums.store');
});

Route::get('/google-drive/auth', [GoogleDriveController::class, 'redirectToGoogle']);
Route::get('/google-drive/callback', [GoogleDriveController::class, 'handleCallback']);
Route::post('/google-drive/upload', [GoogleDriveController::class, 'upload'])->name('google.upload');

require __DIR__.'/auth.php';

Route::get('/phpinfo', function () {
    dd(PHP_BINARY, php_ini_loaded_file());
});

Route::get('/media/drive/{id}', [DriveMediaController::class, 'stream'])
     ->name('media.drive')
     ->middleware('auth'); // opcional, si quieres protegerlo
