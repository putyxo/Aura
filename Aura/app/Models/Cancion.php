<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_song', 'song_id', 'playlist_id')
                    ->withTimestamps();
    }

    // 🎤 Relación con la letra
    public function lyric(): HasOne
    {
        return $this->hasOne(Lyric::class, 'song_id');
    }

    // Accesor para la portada
    public function getCoverUrlAttribute(): string
    {
        return $this->cover_path 
            ? $this->cover_path 
            : asset('img/default-song.png');
    }

    // Accesor para el audio
    public function getAudioUrlAttribute(): string
    {
        return $this->audio_path ?? '';
    }
}
