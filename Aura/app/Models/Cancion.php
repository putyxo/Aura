<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cancion extends Model
{
    protected $table = 'songs';  // Asegúrate que tu tabla sea la correcta

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

    // Relación con el modelo User
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Relación con el modelo Album
    public function album(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Album::class);
    }

    // Accesor para la portada
    public function getCoverUrlAttribute(): string
    {
        return $this->cover_path 
            ? asset('storage/'.$this->cover_path) 
            : asset('img/default-song.png');
    }

    // Accesor para el audio
    public function getAudioUrlAttribute(): string
    {
        return $this->audio_path 
            ? asset('storage/'.$this->audio_path) 
            : '';
    }
}
