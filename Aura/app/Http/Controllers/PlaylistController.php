<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class PlaylistController extends Controller
{
    /**
<<<<<<< HEAD
     * Muestra todas las playlists del usuario autenticado.
     */
    public function index()
=======
     * Lista de playlists del usuario autenticado.
     */
    public function index(): View
>>>>>>> recup-ayer
    {
        $playlists = Playlist::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('playlist', compact('playlists'));
    }

    /**
<<<<<<< HEAD
     * Muestra una playlist específica.
     */
    public function show(Playlist $playlist)
=======
     * Mostrar una playlist (propietario).
     */
    public function show(Playlist $playlist): View
>>>>>>> recup-ayer
    {
        $this->authorizeOwner($playlist);
        return view('playlist_card', compact('playlist'));
    }

    /**
<<<<<<< HEAD
     * Crea una nueva playlist.
     */
    public function store(Request $request)
=======
     * Crear playlist con portada opcional (LOCAL).
     */
    public function store(Request $request): RedirectResponse
>>>>>>> recup-ayer
    {
        $data = $request->validate([
            'nombre'      => ['required','string','max:120'],
            'descripcion' => ['nullable','string','max:1000'],
            'portada'     => ['nullable','image','max:5120'], // 5MB
        ]);

        $coverUrl = null;
        if ($request->hasFile('portada')) {
            // Guarda en storage/app/public/portadas
            $path = $request->file('portada')->store('portadas', 'public');
            // URL pública: /storage/portadas/...
            $coverUrl = Storage::url($path);
        }

        Playlist::create([
            'user_id'     => Auth::id(),
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'cover_url'   => $coverUrl, // tu tabla usa cover_url
        ]);

        return redirect()->route('playlist')->with('ok', 'Playlist creada correctamente.');
    }

    /**
<<<<<<< HEAD
     * Elimina una playlist.
     */
public function destroy(Playlist $playlist)
{
    // Verificar que el usuario autenticado es el dueño de la playlist
    if ($playlist->user_id !== Auth::id()) {
        return response()->json(['error' => 'No autorizado'], 403);
=======
     * Actualizar nombre/descripcion y (opcional) cambiar portada.
     */
    public function update(Request $request, Playlist $playlist): RedirectResponse
    {
        $this->authorizeOwner($playlist);

        $data = $request->validate([
            'nombre'      => ['required','string','max:120'],
            'descripcion' => ['nullable','string','max:1000'],
            'portada'     => ['nullable','image','max:5120'],
        ]);

        // Si cambia portada, borra la anterior (si era local) y sube la nueva
        if ($request->hasFile('portada')) {
            $this->deleteCoverIfLocal($playlist->cover_url);

            $path = $request->file('portada')->store('portadas', 'public');
            $playlist->cover_url = Storage::url($path);
        }

        $playlist->nombre      = $data['nombre'];
        $playlist->descripcion = $data['descripcion'] ?? null;
        $playlist->save();

        return redirect()->back()->with('ok', 'Playlist actualizada.');
>>>>>>> recup-ayer
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
<<<<<<< HEAD
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
=======
     * Eliminar playlist (borra portada local y detacha canciones).
     */
    public function destroy(Playlist $playlist): RedirectResponse
    {
        $this->authorizeOwner($playlist);

        // Detach de canciones (si no hay ON DELETE CASCADE en pivot)
        if (method_exists($playlist, 'songs')) {
            $playlist->songs()->detach();
        }

        // Borrar portada si es local
        $this->deleteCoverIfLocal($playlist->cover_url);

        $playlist->delete();

        return redirect()->route('playlist')->with('ok', 'Playlist eliminada.');
    }

    /**
     * Devuelve las playlists del usuario autenticado (JSON) con portada y conteo.
     */
    public function myPlaylists(): JsonResponse
    {
        $playlists = Playlist::where('user_id', Auth::id())
            ->select(['id','nombre','cover_url','created_at'])
            ->withCount('songs')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pl) {
                $url = $pl->cover_url;
                // Normaliza a URL absoluta si está guardada como /storage/...
                if ($url && !preg_match('~^https?://~i', $url)) {
                    $url = url($url);
                }
                return [
                    'id'          => $pl->id,
                    'nombre'      => $pl->nombre,
                    'cover_url'   => $url,
                    'songs_count' => $pl->songs_count ?? 0,
                    'created_at'  => optional($pl->created_at)->toDateTimeString(),
                ];
            });

        return response()->json($playlists);
    }

    /**
     * Agregar canción a una playlist (sin duplicar).
     */
    public function addSong(Playlist $playlist, Cancion $cancion): JsonResponse
    {
        $this->authorizeOwner($playlist);

        $playlist->songs()->syncWithoutDetaching([$cancion->id]);

        return response()->json([
            'message' => 'Canción agregada a la playlist',
            'ok'      => true,
        ]);
    }

    /**
     * Quitar canción de una playlist.
     */
    public function removeSong(Playlist $playlist, Cancion $cancion): JsonResponse
    {
        $this->authorizeOwner($playlist);

        $playlist->songs()->detach($cancion->id);

        return response()->json(['message' => 'Canción removida de la playlist']);
    }

    /**
     * Crear playlist rápida (AJAX) sin portada.
     */
    public function quickStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:120'],
        ]);

        $playlist = Playlist::create([
            'user_id' => Auth::id(),
            'nombre'  => $data['nombre'],
        ]);

        return response()->json([
            'id'          => $playlist->id,
            'nombre'      => $playlist->nombre,
            'cover_url'   => $playlist->cover_url,
            'songs_count' => 0,
            'message'     => 'Playlist creada correctamente',
        ]);
    }

    /* ================== Helpers ================== */

    /**
     * Autoriza que la playlist pertenezca al usuario autenticado.
     */
    private function authorizeOwner(Playlist $playlist): void
    {
        if ((int) $playlist->user_id !== (int) Auth::id()) {
            abort(403, 'No autorizado');
        }
    }

    /**
     * Si cover_url apunta a /storage/... elimina el archivo en disco 'public'.
     */
    private function deleteCoverIfLocal(?string $coverUrl): void
    {
        if (!$coverUrl) return;

        // Si es URL absoluta externa, no borramos aquí.
        if (preg_match('~^https?://~i', $coverUrl)) {
            return;
        }

        // Convierte "/storage/portadas/archivo.jpg" en "portadas/archivo.jpg"
        $relative = ltrim(Str::replaceFirst('/storage/', '', $coverUrl), '/');

        try {
            if ($relative && Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
        } catch (\Throwable $e) {
            // noop
        }
    }
>>>>>>> recup-ayer
}
