<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cancion extends Model
{
    protected $table = 'songs';

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Album::class);
    }
}
