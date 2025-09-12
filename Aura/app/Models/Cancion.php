<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cancion extends Model
{
    protected $table = 'songs'; // Asegúrate de que la tabla sea 'songs' o el nombre correcto

    protected $fillable = [
        'user_id', 'album_id', 'title', 'genre', 'audio_path', 'cover_path', 'duration', 'status',
    ];

    public $timestamps = true;

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el álbum
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    // Relación con los usuarios que dieron like
    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'likes', 'song_id', 'user_id')
                    ->withTimestamps();
    }

    // Relación con las playlists a las que pertenece la canción
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_song', 'song_id', 'playlist_id')
                    ->withTimestamps();
    }
}
