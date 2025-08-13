<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $table = 'albums';

    protected $fillable = [
        'user_id', 'title', 'genre', 'cover_path', 'release_date',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function songs() {
        return $this->hasMany(Cancion::class, 'album_id');
    }
}