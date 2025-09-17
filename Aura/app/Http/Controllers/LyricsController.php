<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\JsonResponse;

class LyricsController extends Controller
{
    /**
     * Devuelve la letra (y segmentos si existen) de una canción en JSON.
     */
    public function show(Cancion $cancion): JsonResponse
    {
        $lyric = $cancion->lyric;

        if (!$lyric) {
            return response()->json([
                'song_id'  => $cancion->id,
                'lyrics'   => null,
                'segments' => [],
                'synced'   => false,
                'status'   => 'not_found',
                'message'  => 'No hay letra guardada para esta canción.',
            ]);
        }

        // Limpiar delimitadores tipo ```lrc ... ```
        $content = trim($lyric->content);
        $content = preg_replace('/^```lrc\s*|\s*```$/m', '', $content);

        $segments = [];
        if ($lyric->json_segments) {
            $raw = json_decode($lyric->json_segments, true);
            foreach ($raw as $seg) {
                $segments[] = [
                    'start' => $seg['start'] ?? 0,
                    'end'   => $seg['end'] ?? 0,
                    'text'  => $seg['text'] ?? '',
                ];
            }
        }

        return response()->json([
            'song_id'  => $cancion->id,
            'lyrics'   => $content,
            'segments' => $segments,
            'synced'   => !empty($segments),
            'status'   => 'ready',
        ]);
    }
}
