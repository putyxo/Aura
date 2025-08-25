<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CancionController extends Controller
{
    /**
     * Eliminar una canción
     */
    public function destroy($id)
    {
        $cancion = Cancion::findOrFail($id);

        // eliminar archivo de audio si existe en storage
        if ($cancion->audio_path && Storage::disk('public')->exists($cancion->audio_path)) {
            Storage::disk('public')->delete($cancion->audio_path);
        }

        // eliminar portada si existe
        if ($cancion->cover_path && Storage::disk('public')->exists($cancion->cover_path)) {
            Storage::disk('public')->delete($cancion->cover_path);
        }

        $cancion->delete();

        return redirect()->back()->with('success', 'Canción eliminada correctamente ✅');
    }
}
