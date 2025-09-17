<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Album extends Model
{
    protected $table = 'albums';

    protected $fillable = [
        'user_id',
        'title',
        'genre',
        'cover_path',
        'release_date',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    // Para que la vista tenga la URL pública lista
    protected $appends = ['cover_url'];

    /* ========= Relaciones ========= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   public function songs()
{
    return $this->hasMany(\App\Models\Cancion::class, 'album_id');
}

}
