<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    /**
     * Muestra todas las playlists del usuario autenticado.
     */
    public function index()
    {
        $playlists = Playlist::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('playlist', compact('playlists'));
    }

    /**
     * Muestra una playlist específica.
     */
    public function show(Playlist $playlist)
    {
        return view('playlist_card', compact('playlist'));
    }

    /**
     * Crea una nueva playlist.
     */
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
     * Elimina una playlist.
     */
public function destroy(Playlist $playlist)
{
    // Verificar que el usuario autenticado es el dueño de la playlist
    if ($playlist->user_id !== Auth::id()) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    // Eliminar la playlist
    try {
        $playlist->delete();
        return response()->json(['message' => 'Playlist eliminada correctamente']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error eliminando la playlist: ' . $e->getMessage()], 500);
    }
}

    /**
     * Actualiza una playlist existente.
     */
    public function update(Request $request, Playlist $playlist)
    {
        // Verificar que el usuario autenticado es el dueño de la playlist
        if ($playlist->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validar los datos de la solicitud
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'portada'     => ['nullable', 'image', 'max:5120'], // 5MB
        ]);

        // Manejar la imagen de portada si se sube una nueva
        $coverUrl = $playlist->cover_url; // Mantener la portada actual si no se sube una nueva
        if ($request->hasFile('portada')) {
            // Si se sube una nueva portada, guardarla y actualizar la URL
            $path = $request->file('portada')->store('portadas', 'public');
            $coverUrl = Storage::url($path);
        }

        // Actualizar la playlist con los nuevos datos
        $playlist->update([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'cover_url'   => $coverUrl,
        ]);

        return response()->json([
            'message' => 'Playlist actualizada correctamente',
            'playlist' => $playlist
        ]);
    }
}
