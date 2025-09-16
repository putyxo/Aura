<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Cancion extends Model
{
    protected $table = 'songs';

    /**
     * Campos masivos (incluye campos de letras si los usas)
     */
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
        'lyrics_status', // pending | processing | done | failed
        'lyrics_error',
    ];

    protected $casts = [
        'lyrics'        => 'string',
        'lyrics_status' => 'string',
        'lyrics_error'  => 'string',
    ];

    public $timestamps = true;

    /**
     * Atributos calculados anexados al JSON
     */
    protected $appends = ['audio_url', 'cover_url'];

    /* ================== Relaciones ================== */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /** ❤️ Usuarios que han dado like (pivot: likes -> song_id, user_id) */
    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes', 'song_id', 'user_id')
                    ->withTimestamps();
    }

    /** Alias común en controladores/vistas: likedByUsers() */
    public function likedByUsers(): BelongsToMany
    {
        return $this->likedBy();
    }

    /** 📂 Playlists a las que pertenece (pivot: playlist_song) */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_song', 'song_id', 'playlist_id')
                    ->withTimestamps();
    }

    /** 🎤 Relación con la letra (si usas tabla lyrics) */
    public function lyric(): HasOne
    {
        return $this->hasOne(Lyric::class, 'song_id');
    }

    /* ================== Accessors / Helpers ================== */

    /** URL pública de la portada (prioriza storage local; fallback a asset) */
    public function getCoverUrlAttribute(): string
    {
        $raw = trim((string)($this->cover_path ?? ''));

        if ($raw === '') {
            return asset('img/default-cancion.png');
        }

        // URL absoluta o ya expuesta en /storage
        if (Str::startsWith($raw, ['http://', 'https://', '/storage/'])) {
            return $raw;
        }

        // Normaliza por si viene con "public/"
        $path = ltrim(preg_replace('#^/?public/#', '', $raw), '/');

        return Storage::url($path); // => /storage/...
    }

    /** URL pública del audio (local/storage o absoluta) */
    public function getAudioUrlAttribute(): ?string
    {
        $raw = trim((string)($this->audio_path ?? ''));

        if ($raw === '') {
            return null;
        }

        if (Str::startsWith($raw, ['http://', 'https://', '/storage/'])) {
            return $raw;
        }

        $path = ltrim(preg_replace('#^/?public/#', '', $raw), '/');

        return Storage::url($path);
    }

    /** ¿El usuario dado ya dio like? */
    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likedBy()->where('users.id', $user->id)->exists();
    }

    /** Alterna like del usuario. Devuelve true si queda likeado. */
    public function toggleLike(User $user): bool
    {
        $pivot = $this->likedBy()->where('users.id', $user->id);
        if ($pivot->exists()) {
            $this->likedBy()->detach($user->id);
            return false;
        }
        $this->likedBy()->attach($user->id);
        return true;
    }

    /**
     * Encola la generación de letras si procede, sin romper si el Job no existe
     * Estados: pending | processing | done | failed
     */
    public function requestLyricsGeneration(bool $force = false): void
    {
        if (!$this->audio_url) return;

        $status = (string) ($this->lyrics_status ?? '');
        $canQueue = $force || (!$this->lyrics && !in_array($status, ['pending','processing'], true));

        if (!$canQueue) return;

        $this->lyrics_status = 'pending';
        $this->lyrics_error  = null;
        $this->saveQuietly();

        // Solo despacha si existe el Job (evita "Class not found")
        if (class_exists(\App\Jobs\GenerateLyricsJob::class)) {
            // Pasa el ID para evitar serializar el modelo completo
            \App\Jobs\GenerateLyricsJob::dispatch($this->getKey());
        }
    }

    /* ================== Scopes útiles (opcionales) ================== */

    /** Scope para incluir columna virtual booleana is_liked para un user */
    public function scopeWithIsLikedBy($query, ?int $userId)
    {
        if (!$userId) return $query;
        return $query->withExists([
            'likedBy as is_liked' => fn ($q) => $q->where('users.id', $userId),
        ]);
    }

    /** Scope simple por estado */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
