<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\JsonResponse;

class LikeApiController extends Controller
{
    // SIN constructor: el middleware 'auth' lo ponemos en la ruta

    // POST /canciones/{cancion}/like -> { liked: bool, song?: {...} }
    public function toggle(Cancion $cancion): JsonResponse
    {
        $user = auth()->user();

        $exists = $user->likedSongs()->whereKey($cancion->getKey())->exists();
        if ($exists) {
            $user->likedSongs()->detach($cancion->getKey());
            return response()->json(['liked' => false, 'song' => ['id' => $cancion->getKey()]]);
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
                'cover'    => $cancion->cover_path ?? $cancion->portada ?? asset('img/default-cover.jpg'),
                'audio'    => $cancion->audio_path ?? $cancion->ruta_audio ?? null,
                'duration' => $cancion->duration ?? $cancion->duracion ?? 0,
            ],
        ]);
    }

    // GET /canciones/{cancion}/liked -> { liked: bool }
    public function liked(Cancion $cancion): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['liked' => false]);
        }

        $user = auth()->user();
        $is   = $user->likedSongs()->whereKey($cancion->getKey())->exists();

        return response()->json(['liked' => $is]);
    }
}
