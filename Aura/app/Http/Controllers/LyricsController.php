<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\JsonResponse;

class LyricsController extends Controller
{
    /**
     * Devuelve la letra de una canción en formato JSON.
     */
    public function show(Cancion $cancion): JsonResponse
    {
        $lyric = $cancion->lyric;

        if (!$lyric) {
            return response()->json([
                'content' => null,
                'message' => 'No hay letra guardada para esta canción.',
            ]);
        }

        // Limpiar ```lrc ... ``` del contenido
        $content = trim($lyric->content);
        $content = preg_replace('/^```lrc\s*|\s*```$/m', '', $content);

        return response()->json([
            'content' => $content,
        ]);
    }
}
