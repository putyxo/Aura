<?php

namespace App\Models;

use App\Jobs\GenerateLyricsJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cancion extends Model
{
    protected $table = 'songs';

    // ↑ Agregamos lyrics, lyrics_status, lyrics_error
    protected $fillable = [
        'user_id',
        'album_id',
        'title',
        'genre',
        'audio_path',
        'cover_path',
        'duration',
        'status',
        'lyrics',
        'lyrics_status',
        'lyrics_error',
    ];

    protected $casts = [
        'lyrics'        => 'string',
        'lyrics_status' => 'string',
        'lyrics_error'  => 'string',
    ];

    public $timestamps = true;

    // Entrega URLs listas para usar en la vista
    protected $appends = ['audio_url', 'cover_url'];

    /* ========= Relaciones ========= */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    // ❤️ Usuarios que han dado like (pivot: likes, cols: song_id, user_id)
    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes', 'song_id', 'user_id')
                    ->withTimestamps();
    }

    // 📂 Playlists a las que pertenece esta canción (pivot: playlist_song)
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

    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio_path ?? '';
    }
}
