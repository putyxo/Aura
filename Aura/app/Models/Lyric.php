<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lyric extends Model
{
    protected $table = 'lyrics';

    protected $fillable = ['song_id', 'content'];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Cancion::class, 'song_id', 'id');
    }
}
