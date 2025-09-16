<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\JsonResponse;

class LikeApiController extends Controller
{
    /**
     * Alterna el "me gusta" de una canción para el usuario autenticado.
     * Ruta sugerida (con middleware auth en la ruta):
     * POST /canciones/{cancion}/like
     */
    public function toggle(Cancion $cancion): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $exists = $user->likedSongs()->whereKey($cancion->getKey())->exists();

        if ($exists) {
            $user->likedSongs()->detach($cancion->getKey());
            return response()->json([
                'liked' => false,
                'song'  => ['id' => $cancion->getKey()],
            ]);
        }

        $user->likedSongs()->attach($cancion->getKey());

        $artist = optional($cancion->user)->nombre_artistico
               ?? optional($cancion->user)->nombre
               ?? 'Artista';

        return response()->json([
            'liked' => true,
            'song'  => [
                'id'       => $cancion->getKey(),
                'title'    => $cancion->title ?? $cancion->titulo ?? 'Sin título',
                'artist'   => $artist,
                // usa accessors del modelo para URL correcta en /storage
                'cover'    => $cancion->cover_url ?? ($cancion->cover_path ?? asset('img/default-cover.jpg')),
                'audio'    => $cancion->audio_url ?? ($cancion->audio_path ?? null),
                'duration' => $cancion->duration ?? $cancion->duracion ?? 0,
            ],
        ]);
    }

    /**
     * Indica si el usuario autenticado ya dio "me gusta" a la canción.
     * GET /canciones/{cancion}/liked
     */
    public function liked(Cancion $cancion): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['liked' => false]);
        }

        $is = $user->likedSongs()->whereKey($cancion->getKey())->exists();

        return response()->json(['liked' => $is]);
    }
}
