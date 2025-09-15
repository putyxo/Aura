<?php

namespace App\Models;

use App\Jobs\GenerateLyricsJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /* ========= Accessors ========= */

    public function getCoverUrlAttribute(): string
    {
        $p = $this->cover_path;
        if (!$p) {
            return asset('img/default-cancion.png');
        }
        return Str::startsWith($p, ['http://', 'https://'])
            ? $p
            : asset('storage/' . ltrim($p, '/'));
    }

    public function getAudioUrlAttribute(): ?string
    {
        $p = $this->audio_path;
        if (!$p) return null;

        return Str::startsWith($p, ['http://', 'https://'])
            ? $p
            : asset('storage/' . ltrim($p, '/'));
    }

    /* ========= Hooks ========= */

    protected static function booted()
    {
        // Borrar archivos locales si las rutas son relativas
        static::deleting(function (Cancion $song) {
            foreach (['audio_path', 'cover_path'] as $col) {
                $path = $song->{$col};
                if ($path && !Str::startsWith($path, ['http://', 'https://'])) {
                    Storage::disk('public')->delete($path);
                }
            }
        });

        // Al crear, si no hay letra, dispara el job (opcional)
        static::created(function (Cancion $song) {
            if (empty($song->lyrics)) {
                GenerateLyricsJob::dispatch($song->id)->onQueue('default');
            }
        });
    }
}
