<?php

namespace App\Http\Controllers;

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
     * Actualiza campos de la canción (título/portada).
     * Además soporta propagación desde portada de álbum:
     *  - cover_from_album=1 + cover_path (copiar ruta ya subida del álbum)
     *  - o subir archivo 'cover'
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
            'title'            => ['nullable', 'string', 'max:255'],
            'cover'            => ['nullable', 'image', 'max:8192'],
            'cover_from_album' => ['nullable', 'in:1'],
            'cover_path'       => ['nullable', 'string', 'max:255'],
        ]);

        $changed = [];

        if (array_key_exists('title', $validated)) {
            $song->title = $validated['title'];
            $changed['title'] = $song->title;
        }

        // 1) Copiar ruta desde el álbum si viene cover_from_album y cover_path
        if (!empty($validated['cover_from_album']) && !empty($validated['cover_path'])) {
            $song->cover_path = ltrim($validated['cover_path'], '/');
            $changed['cover_path'] = $song->cover_path;
        }
        // 2) O subir archivo directo
        elseif ($request->hasFile('cover')) {
            $img  = $request->file('cover');
            $slug = \Str::slug($song->title ?: 'cancion') . '-' . time();
            $ext  = $img->getClientOriginalExtension();
            $path = $img->storeAs('songs/covers', $slug . '.' . $ext, 'public');

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
                'ok'      => true,
                'song'    => $song->only(['id', 'title', 'cover_path']),
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

        if (method_exists($cancion, 'likedBy')) {
            $cancion->likedBy()->detach();
        }
        if (method_exists($cancion, 'playlists')) {
            $cancion->playlists()->detach();
        }

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

    public function lyrics(Cancion $cancion)
    {
        $lyric = $cancion->lyric ?? null;

        if ($lyric) {
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
                'lyrics'   => $lyric->content,
                'segments' => $segments,
                'synced'   => !empty($segments),
                'status'   => 'ready',
            ]);
        }

        GenerateLyricsJob::dispatch($cancion->id);

        return response()->json([
            'song_id'  => $cancion->id,
            'lyrics'   => null,
            'segments' => [],
            'synced'   => false,
            'status'   => 'pending',
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
}
/* ==============================
       RELACIONES EXTRA (Likes)
       ============================== */