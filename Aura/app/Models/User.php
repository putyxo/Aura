<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
            'password' => 'hashed',
        ];
    }

    /* ==============================
       RELACIONES FOLLOWERS / FOLLOWINGS
       ============================== */

    // Usuarios que sigo (relación muchos a muchos)
    public function followings()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id');
    }

    // Usuarios que me siguen (relación muchos a muchos)
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id');
    }

    // Verificar si sigo a un usuario
    public function isFollowing($userId): bool
    {
        return $this->followings()->where('followed_id', $userId)->exists();
    }

    // Verificar si me siguen
    public function isFollowedBy($userId): bool
    {
        return $this->followers()->where('follower_id', $userId)->exists();
    }

    /* ==============================
       ACCESSORS PARA AVATAR / BANNER / PORTADA
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

public function likes()
{
    return $this->belongsToMany(Cancion::class, 'likes', 'user_id', 'song_id')
                ->withTimestamps();
}
}
