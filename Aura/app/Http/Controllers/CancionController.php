<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
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
=======
use App\Jobs\GenerateLyricsJob;
use App\Models\Album;
use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CancionController extends Controller
{
    // (Opcional) si usas show:
    public function show(Cancion $cancion)
    {
        $cancion->load('user', 'album.user');
        return view('canciones.show', compact('cancion'));
    }

    /**
     * Actualiza campos simples de la canción (por ahora: título y/o portada).
     */
    public function update(Request $request, int $cancion)
    {
        $song = Cancion::findOrFail($cancion);

        // Dueño: por user_id directo o por dueño del álbum
        $ownerId = $song->user_id ?? optional(Album::find($song->album_id))->user_id;
        if ((int)$ownerId !== (int)Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'cover' => ['nullable', 'image', 'max:8192'],
        ]);

        $changed = [];

        if (array_key_exists('title', $validated)) {
            $song->title = $validated['title'];
            $changed['title'] = $song->title;
        }

        if ($request->hasFile('cover')) {
            $img  = $request->file('cover');
            $slug = \Str::slug($song->title ?: 'cancion') . '-' . time();
            $ext  = $img->getClientOriginalExtension();
            $path = $img->storeAs('covers', $slug . '.' . $ext, 'public');

            $old = $song->cover_path ?? $song->portada ?? null;
            if ($old && !preg_match('~^https?://~i', $old)) {
                Storage::disk('public')->delete(ltrim(preg_replace('#^/?public/#', '', $old), '/'));
            }

            $song->cover_path = $path;
            $changed['cover_path'] = $path;
        }

        $song->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'   => true,
                'song' => $song->only(['id', 'title', 'cover_path']),
                'changed' => $changed,
            ]);
        }

        return back()->with('ok', 'Canción actualizada correctamente.');
    }

    /**
     * Eliminar una canción (archivos locales + relaciones).
     */
    public function destroy(Cancion $cancion, Request $request)
    {
        if ((int) $cancion->user_id !== (int) Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        $songCoverPath = $cancion->cover_path ?? $cancion->portada ?? null;
        $songAudioPath = $cancion->audio_path ?? $cancion->audio ?? null;

        $this->deleteLocalIfRelative($songAudioPath);
        $this->deleteLocalIfRelative($songCoverPath);

        $cancion->likedBy()->detach();
        $cancion->playlists()->detach();

        $cancion->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Canción eliminada correctamente ✅']);
        }

        return redirect()->back()->with('success', 'Canción eliminada correctamente ✅');
    }

    /**
     * ❤️ Alternar like/unlike a una canción. (normalmente usas LikeApiController)
     */
    public function toggleLike(Cancion $cancion)
    {
        $user = Auth::user();
        $yaLeDioLike = $cancion->likedBy()->where('users.id', $user->id)->exists();

        if ($yaLeDioLike) {
            $cancion->likedBy()->detach($user->id);
            return response()->json(['liked' => false, 'message' => 'Like eliminado']);
        } else {
            $cancion->likedBy()->attach($user->id);
            return response()->json(['liked' => true, 'message' => 'Like agregado']);
        }
    }

    public function liked(Cancion $cancion)
    {
        $user = Auth::user();
        $liked = $cancion->likedBy()->where('users.id', $user->id)->exists();

        return response()->json(['liked' => $liked]);
    }

    public function like()
    {
        $user = Auth::user();

        $likedSongs = Cancion::with('user')
            ->whereHas('likedBy', fn ($q) => $q->where('users.id', $user->id))
            ->get();

        return view('like', compact('likedSongs'));
    }

    public function generateLyrics(Cancion $cancion)
    {
        if ((int) $cancion->user_id !== (int) Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

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

    private function deleteLocalIfRelative(?string $path): void
    {
        if (!$path) return;
        if (preg_match('~^https?://~i', $path)) return;

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return;
            }
        } catch (\Throwable $e) {}

        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable $e) {}
    }

    public function lyrics(\App\Models\Cancion $cancion)
    {
        if ($cancion->lyric) {
            return response()->json([
                'song_id' => $cancion->id,
                'lyrics'  => $cancion->lyric->content,
                'synced'  => (bool) $cancion->lyric->synced,
                'status'  => 'ready',
            ]);
        }

        \App\Jobs\GenerateLyricsJob::dispatch($cancion->id);

        return response()->json([
            'song_id' => $cancion->id,
            'lyrics'  => null,
            'synced'  => false,
            'status'  => 'pending',
        ]);
    }
>>>>>>> recup-ayer
}
