<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\JsonResponse;

class LyricApiController extends Controller
{
    /**
     * Devuelve la letra (LRC) de una canción.
     *
     * GET /canciones/{cancion}/lyrics
     */
    public function show(Cancion $cancion): JsonResponse
    {
        $lyric = $cancion->lyric;

        if (!$lyric) {
            return response()->json([
                'content' => null,
                'message' => 'No hay letra disponible para esta canción.'
            ]);
        }

        // Limpia delimitadores tipo ```lrc ... ```
        $content = preg_replace('/^```lrc|```$/m', '', $lyric->content);

        return response()->json([
            'song_id' => $cancion->id,
            'title'   => $cancion->title,
            'artist'  => optional($cancion->user)->nombre_artistico ?? optional($cancion->user)->nombre ?? 'Desconocido',
            'content' => trim($content),
        ]);
    }
}
