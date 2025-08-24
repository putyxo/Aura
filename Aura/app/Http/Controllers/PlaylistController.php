<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    public function index()
    {
        $playlists = Playlist::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('playlist', compact('playlists'));
    }

    public function show(Playlist $playlist)
    {
        return view('playlist_card', compact('playlist'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required','string','max:120'],
            'descripcion' => ['nullable','string','max:1000'],
            'portada'     => ['nullable','image','max:5120'], // 5MB
        ]);

        $coverUrl = null;
        if ($request->hasFile('portada')) {
            $path = $request->file('portada')->store('portadas', 'public');
            $coverUrl = Storage::url($path);
        }

        // Generate unique share link
        $shareLink = $this->generateUniqueShareLink();

        Playlist::create([
            'user_id'     => Auth::id(),
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'cover_url'   => $coverUrl,
            'share_link'  => $shareLink,
        ]);

        return redirect()->route('playlist')->with('ok', 'Playlist creada correctamente.');
    }

    public function accessByShareLink(Request $request, $shareLink)
    {
        $playlist = Playlist::where('share_link', $shareLink)->first();

        if (!$playlist) {
            return response()->json(['error' => 'Playlist no encontrada'], 404);
        }

        return response()->json([
            'success' => true,
            'playlist' => [
                'id' => $playlist->id,
                'nombre' => $playlist->nombre,
                'descripcion' => $playlist->descripcion,
                'cover_url' => $playlist->cover_url,
                'share_link' => $playlist->share_link,
                'user_id' => $playlist->user_id,
            ]
        ]);
    }

    public function generateShareLink($playlistId)
    {
        $playlist = Playlist::findOrFail($playlistId);
        
        if (!$playlist->share_link) {
            $playlist->share_link = $this->generateUniqueShareLink();
            $playlist->save();
        }

        return response()->json([
            'share_link' => url("/playlists/share/{$playlist->share_link}")
        ]);
    }

    private function generateUniqueShareLink()
    {
        do {
            $shareLink = bin2hex(random_bytes(16)); // Generate a random 32-character hex string
        } while (Playlist::where('share_link', $shareLink)->exists());

        return $shareLink;
    }
}
