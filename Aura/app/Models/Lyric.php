<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lyric extends Model
{
    protected $table = 'lyrics';

    // 👉 agrega json_segments aquí
    protected $fillable = [
        'song_id',
        'content',
        'json_segments',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Cancion::class, 'song_id', 'id');
    }
}
