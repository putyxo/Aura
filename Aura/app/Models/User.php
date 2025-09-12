<?php

namespace App\Models;

use App\Models\Cancion; // <- IMPORTANTE: tu modelo de canciones
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\User
 *
 * Campos extra (según tu migración): avatar, fecha_nacimiento, genero_favorito,
 * es_artista, nombre_artistico, biografia, imagen_portada, banner, verificado.
 *
 * Relaciones incluidas:
 * - followers / followings (tabla pivot 'follows')
 * - likedSongs (tabla pivot 'likes')
 *
 * Accessors:
 * - avatar_url, banner_url, imagen_portada_url (si usas media.drive para servir imgs)
 */
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

    /* ==============================
       FOLLOWERS / FOLLOWINGS
       ============================== */

    public function followings()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id');
    }

    public function followers()
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
       ACCESSORS AVATAR / BANNER / PORTADA
       ============================== */

    public function getAvatarUrlAttribute()
    {
        return $this->avatar
            ? route('media.drive', ['id' => $this->avatar])
            : asset('img/default-avatar.png');
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner
            ? route('media.drive', ['id' => $this->banner])
            : asset('img/default-banner.png');
    }

    public function getImagenPortadaUrlAttribute()
    {
        return $this->imagen_portada
            ? route('media.drive', ['id' => $this->imagen_portada])
            : asset('img/default-cover.png');
    }

public function likes1()
{
    return $this->belongsToMany(Cancion::class, 'likes', 'user_id', 'song_id')
                ->withTimestamps();
}

public function equalizer()
{
    return $this->hasOne(UserEqualizer::class);
}
    /* ==============================
       ME GUSTA (LIKES)
       ============================== */

    public function likedSongs()
    {
        // Tabla pivot: likes (user_id, song_id, timestamps)
        return $this->belongsToMany(Cancion::class, 'likes', 'user_id', 'song_id')
                    ->withTimestamps();
    }

    // Alias opcional
    public function likes()
    {
        return $this->likedSongs();
    }
}
