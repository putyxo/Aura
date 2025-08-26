<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cancion extends Model
{
    protected $table = 'songs'; // Tu tabla real es songs

    protected $fillable = [
        'user_id',
        'album_id',
        'title',
        'genre',
        'audio_path',
        'cover_path',
        'duration',
        'status'
    ];

    public $timestamps = true;

    // Relación con el usuario dueño de la canción
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el álbum
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    // ❤️ Usuarios que han dado like
    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes', 'song_id', 'user_id')
                    ->withTimestamps();
    }

    // 📂 Playlists a las que pertenece esta canción
public function playlists()
{
    return $this->belongsToMany(Playlist::class, 'playlist_song', 'song_id', 'playlist_id')
                ->withTimestamps();
}

    // Accesor para la portada
    public function getCoverUrlAttribute(): string
    {
        return $this->cover_path 
            ? $this->cover_path // ya guardas URL completa en DB
            : asset('img/default-song.png');
    }

    // Accesor para el audio
    public function getAudioUrlAttribute(): string
    {
        return $this->audio_path 
            ? $this->audio_path // igual, ya guardas URL completa
            : '';
    }
    public function removeSong(Playlist $playlist, Cancion $cancion)
{
    if ($playlist->user_id !== Auth::id()) {
        return redirect()->back()->with('error', 'No autorizado.');
    }

    $playlist->canciones()->detach($cancion->id);

    return redirect()->back()->with('ok', 'Canción quitada de la playlist.');
}

public function show(Playlist $playlist)
{
    $playlist->load('canciones');
    return view('playlist_card', compact('playlist'));
}
}
