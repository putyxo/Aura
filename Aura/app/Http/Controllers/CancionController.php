<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateLyricsJob;
use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CancionController extends Controller
{
    /**
     * Eliminar una canción (archivos locales + relaciones).
     */
    public function destroy(Cancion $cancion, Request $request)
    {
        // Verifica que la canción pertenezca al usuario actual
        if ((int) $cancion->user_id !== (int) Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        // --- Eliminar archivos locales (si las rutas son relativas) ---
        $songCoverPath = $cancion->cover_path ?? $cancion->portada ?? null;
        $songAudioPath = $cancion->audio_path ?? $cancion->audio ?? null;

        $this->deleteLocalIfRelative($songAudioPath);
        $this->deleteLocalIfRelative($songCoverPath);

        // --- Quitar relaciones (si no usas ON DELETE CASCADE) ---
        $cancion->likedBy()->detach();
        $cancion->playlists()->detach();

        // --- Eliminar la canción ---
        $cancion->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Canción eliminada correctamente ✅']);
        }

        return redirect()->back()->with('success', 'Canción eliminada correctamente ✅');
    }

    /**
     * ❤️ Alternar like/unlike a una canción.
     * Ruta: POST /canciones/{cancion}/like
     */
    public function toggleLike(Cancion $cancion)
    {
        $user = Auth::user();

        // Usamos la relación de la canción (no dependemos de $user->likes())
        $yaLeDioLike = $cancion->likedBy()->where('users.id', $user->id)->exists();

        if ($yaLeDioLike) {
            $cancion->likedBy()->detach($user->id);
            return response()->json(['liked' => false, 'message' => 'Like eliminado']);
        } else {
            $cancion->likedBy()->attach($user->id);
            return response()->json(['liked' => true, 'message' => 'Like agregado']);
        }
    }

    /**
     * ✅ Saber si el usuario ya dio like a una canción.
     * Ruta: GET /canciones/{cancion}/liked
     */
    public function liked(Cancion $cancion)
    {
        $user = Auth::user();
        $liked = $cancion->likedBy()->where('users.id', $user->id)->exists();

        return response()->json(['liked' => $liked]);
    }

    /**
     * 🎵 Vista de canciones que el usuario ha marcado con like.
     * Ruta: GET /like
     */
    public function like()
    {
        $user = Auth::user();

        // Traemos las canciones donde el pivot likes tiene al user actual
        $likedSongs = Cancion::with('user')
            ->whereHas('likedBy', fn ($q) => $q->where('users.id', $user->id))
            ->get();

        return view('like', compact('likedSongs'));
    }

    /**
     * 📝 Disparar (o reintentar) la generación de letras para una canción.
     * Ruta: POST /canciones/{cancion}/lyrics
     */
    public function generateLyrics(Cancion $cancion)
    {
        // Solo el dueño puede solicitar
        if ((int) $cancion->user_id !== (int) Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        // Limpia estado y relanza
        $cancion->update([
            'lyrics'        => null,
            'lyrics_status' => 'pending',
            'lyrics_error'  => null,
        ]);

        GenerateLyricsJob::dispatch($cancion->id)->onQueue('default');

        return response()->json([
            'ok'      => true,
            'message' => 'Generación de letras iniciada.',
        ]);
    }

    /* ================== Helpers privados ================== */

    /**
     * Elimina un archivo del disco 'public' si la ruta es RELATIVA (no URL absoluta).
     */
    private function deleteLocalIfRelative(?string $path): void
    {
        if (!$path) return;

        // Si es URL http(s), no borrar aquí
        if (preg_match('~^https?://~i', $path)) {
            return;
        }

        // Elimina en el disco 'public' (storage/app/public)
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return;
            }
        } catch (\Throwable $e) {
            // noop
        }

        // Como fallback, intenta en el disco por defecto
        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable $e) {
            // noop
        }
    }
}
