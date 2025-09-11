<?php

namespace App\Http\Controllers;

use App\Models\Cancion;

class LikeApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // POST /api/canciones/{cancion}/like/toggle -> { liked: bool }
    public function toggle(Cancion $cancion)
    {
        $user = auth()->user();

        $exists = $user->likedSongs()->whereKey($cancion->getKey())->exists();
        if ($exists) {
            $user->likedSongs()->detach($cancion->getKey());
            return response()->json(['liked' => false]);
        }

        $user->likedSongs()->attach($cancion->getKey());
        return response()->json(['liked' => true]);
    }

    // GET /api/canciones/{cancion}/liked -> { liked: bool }
    public function liked(Cancion $cancion)
    {
        $user = auth()->user();
        $is = $user->likedSongs()->whereKey($cancion->getKey())->exists();
        return response()->json(['liked' => $is]);
    }
}
