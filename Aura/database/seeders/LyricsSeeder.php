<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lyric;

class LyricsSeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 Ejemplo de letra karaoke para la canción "Camisa negra" (id=7 en tu BD)
        Lyric::updateOrCreate(
            ['song_id' => 7],
            [
                'content' => <<<LRC
[00:10.00] Tengo la camisa negra
[00:15.00] Hoy mi amor está de luto
[00:20.00] Tengo en el alma una pena
[00:25.00] Y es por culpa de tu embrujo

[00:32.00] Hoy sé que tú ya no me quieres
[00:37.00] Y eso es lo que más me hiere
[00:42.00] Que tengo la camisa negra
[00:47.00] Y una pena que me duele
LRC,
                'synced' => 1,
            ]
        );
    }
}
