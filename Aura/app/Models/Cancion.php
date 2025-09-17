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
    public function playlists(): BelongsToMany
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

    // Cancion.php

public function getCleanTitleAttribute(): string
{
    $title = $this->title;

    // 🔹 Opciones de limpieza
    // Quitar "Official Video" o "Official Music Video"
    $title = preg_replace('/\s*\(.*Official.*\)/i', '', $title);

    // Quitar "[...]" (ejemplo: "[Official Music Video]")
    $title = preg_replace('/\[.*?\]/', '', $title);

    // Quitar espacios dobles
    $title = preg_replace('/\s+/', ' ', $title);

    return trim($title);
}

public function getNombreArtisticoAttribute(): string
{
    return $this->user->nombre_artistico
        ?? $this->user->name
        ?? 'Artista desconocido';
}

}
