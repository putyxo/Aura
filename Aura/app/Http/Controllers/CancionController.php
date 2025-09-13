<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Cancion;
use App\Models\Like;

class CancionController extends Controller
{
    /**
     * Dar like o quitar like de una canción
     */
    public function like($id)
    {
        // Buscar la canción por ID
        $song = Cancion::findOrFail($id);
        
        // Verificar si el usuario ya ha dado like a la canción
        $existingLike = Like::where('user_id', Auth::id())
                            ->where('song_id', $song->id)
                            ->first();

        if ($existingLike) {
            // Si ya tiene like, eliminarlo (funcionalidad tipo toggle)
            $existingLike->delete();
        } else {
            // De lo contrario, crear un nuevo like
            Like::create([
                'user_id' => Auth::id(),
                'song_id' => $song->id,
            ]);
        }

        return redirect()->back();
    }
}
