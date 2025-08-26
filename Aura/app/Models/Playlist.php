<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','nombre','descripcion','cover_url'];

public function canciones()
{
    return $this->belongsToMany(Cancion::class, 'playlist_song', 'playlist_id', 'song_id')
                ->withTimestamps();
}
}
