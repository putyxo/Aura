<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * GET /likes/albums
     * Página: Álbumes que te gustan (derivados de likes de canciones).
     */
    public function albumsPage(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        // Álbumes provenientes de canciones con like (ignora singles sin album_id)
        $albumIds = Like::query()
            ->join('songs', 'songs.id', '=', 'likes.song_id')
            ->where('likes.user_id', $userId)
            ->whereNotNull('songs.album_id')
            ->pluck('songs.album_id')
            ->unique()
            ->values();

        $likedAlbums = Album::with(['user:id,nombre,nombre_artistico'])
            ->whereIn('id', $albumIds)
            ->get();

        return view('likes.albums', compact('likedAlbums'));
    }

    /**
     * DELETE /likes/albums/{album}
     * Quita el "me gusta" de TODO el álbum (borra likes de sus canciones).
     */
    public function unlikeAlbum($albumId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        Like::where('user_id', $userId)
            ->whereIn('song_id', function ($q) use ($albumId) {
                $q->select('id')->from('songs')->where('album_id', $albumId);
            })
            ->delete();

        return back()->with('ok', 'Álbum quitado de Me gusta.');
    }
}
