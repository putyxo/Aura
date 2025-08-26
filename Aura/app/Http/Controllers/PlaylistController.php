<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    public function canciones()
{
    return $this->belongsToMany(Cancion::class, 'playlist_cancion', 'playlist_id', 'cancion_id')
                ->withTimestamps();
}
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

        Playlist::create([
            'user_id'     => Auth::id(),
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'cover_url'   => $coverUrl,
        ]);

        return redirect()->route('playlist')->with('ok', 'Playlist creada correctamente.');
    }

    /**
     * Devuelve las playlists del usuario autenticado en JSON
     */
    public function myPlaylists()
    {
        return response()->json(
            Playlist::where('user_id', Auth::id())
                ->get(['id', 'nombre'])
        );
    }

    /**
     * Agregar canción a una playlist
     */
    public function addSong(Playlist $playlist, Cancion $cancion)
    {
        if ($playlist->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $playlist->songs()->syncWithoutDetaching([$cancion->id]);

        return response()->json(['message' => 'Canción agregada a la playlist']);
    }

    public function quickStore(Request $request)
{
    $data = $request->validate([
        'nombre' => ['required','string','max:120'],
    ]);

    $playlist = Playlist::create([
        'user_id' => Auth::id(),
        'nombre'  => $data['nombre'],
    ]);

    return response()->json([
        'id'     => $playlist->id,
        'nombre' => $playlist->nombre,
        'message'=> 'Playlist creada correctamente'
    ]);
}
}
