<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Cancion;

class CancionController extends Controller
{
    /**
     * Eliminar una canción
     */
    public function destroy($id)
    {
        $cancion = Cancion::findOrFail($id);

        if ($cancion->audio_path && Storage::disk('public')->exists($cancion->audio_path)) {
            Storage::disk('public')->delete($cancion->audio_path);
        }

        if ($cancion->cover_path && Storage::disk('public')->exists($cancion->cover_path)) {
            Storage::disk('public')->delete($cancion->cover_path);
        }

        $cancion->delete();

        return redirect()->back()->with('success', 'Canción eliminada correctamente ✅');
    }

    /**
     * ❤️ Alternar like/unlike a una canción
     */
    public function toggleLike(Cancion $cancion)
    {
        $user = Auth::user();

        if ($user->likes()->where('song_id', $cancion->id)->exists()) {
            $user->likes()->detach($cancion->id);
            return response()->json(['liked' => false, 'message' => 'Like eliminado']);
        } else {
            $user->likes()->attach($cancion->id);
            return response()->json(['liked' => true, 'message' => 'Like agregado']);
        }
    }

    /**
     * ❤️ Saber si el usuario ya dio like
     */
    public function liked(Cancion $cancion)
    {
        $liked = Auth::user()->likes()->where('song_id', $cancion->id)->exists();
        return response()->json(['liked' => $liked]);
    }

    /**
     * 🎵 Listar las canciones que el usuario dio like
     */
/**
 * 🎵 Vista de canciones que el usuario ha marcado con like
 */
public function like()
{
    $user = Auth::user();

    // Traer todas las canciones que el usuario dio like
    $canciones = $user->likes()->with('user')->get();

    // Reusar la vista "like.blade.php"
    return view('like', compact('canciones'));
}
}
