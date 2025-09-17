<?php

namespace App\Models;

use App\Models\Cancion;
use App\Models\UserEqualizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'avatar',
        'fecha_nacimiento',
        'genero_favorito',
        'es_artista',
        'nombre_artistico',
        'biografia',
        'imagen_portada',
        'banner',
        'verificado',
        'idioma',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'es_artista'        => 'boolean',
            'verificado'        => 'boolean',
        ];
    }

    // Para que al serializar (JSON) salgan estas URLs listas
    protected $appends = ['avatar_url', 'banner_url', 'imagen_portada_url'];

    /* ==============================
       FOLLOWERS / FOLLOWINGS
       ============================== */

    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id');
    }

    public function isFollowing($userId): bool
    {
        return $this->followings()->where('followed_id', $userId)->exists();
    }

    public function isFollowedBy($userId): bool
    {
        return $this->followers()->where('follower_id', $userId)->exists();
    }

    /* ==============================
       RELACIONES EXTRA (Likes)
       ============================== */

    /** Relación principal: canciones que el usuario ha likeado */
    public function likes(): BelongsToMany
    {
        // Pivot: likes (user_id, song_id) → canciones están en tabla 'songs'
        return $this->belongsToMany(Cancion::class, 'likes', 'user_id', 'song_id')
                    ->withTimestamps();
    }

    /** Alias para compatibilidad con controladores/vistas existentes */
    public function likedSongs(): BelongsToMany
    {
        return $this->likes();
    }

    public function equalizer()
    {
        return $this->hasOne(UserEqualizer::class);
    }

    /* ==============================
       ACCESSORS AVATAR / BANNER / PORTADA
       ============================== */

    public function getAvatarUrlAttribute(): string
    {
        $p = $this->avatar;
        if (!$p) return asset('img/default-avatar.png');

        return Str::startsWith($p, ['http://', 'https://'])
            ? $p
            : asset('storage/' . ltrim($p, '/'));
    }

    public function getBannerUrlAttribute(): string
    {
        $p = $this->banner;
        if (!$p) return asset('img/default-banner.png');

        return Str::startsWith($p, ['http://', 'https://'])
            ? $p
            : asset('storage/' . ltrim($p, '/'));
    }

    public function getImagenPortadaUrlAttribute(): string
    {
        $p = $this->imagen_portada;
        if (!$p) return asset('img/default-cover.png');

        return Str::startsWith($p, ['http://', 'https://'])
            ? $p
            : asset('storage/' . ltrim($p, '/'));
    }
}
