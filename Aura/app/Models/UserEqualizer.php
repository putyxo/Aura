<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEqualizer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','preamp',
        'band_60','band_170','band_310','band_600',
        'band_1000','band_3000','band_6000',
        'band_12000','band_14000','band_16000'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
