<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
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
     * Lista de playlists del usuario autenticado.
     */
    public function index(): View
    {
        $playlists = Playlist::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('playlist', compact('playlists'));
    }

    /**
     * Mostrar una playlist (propietario).
     */
    public function show(Playlist $playlist): View
    {
        $this->authorizeOwner($playlist);
        return view('playlist_card', compact('playlist'));
    }

    /**
     * Crear playlist con portada opcional (LOCAL).
     */
    public function store(Request $request): RedirectResponse
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
    }

    /**
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
     * Devuelve las playlists del usuario autenticado en JSON (id, nombre).
     */
    public function myPlaylists(): JsonResponse
    {
        return response()->json(
            Playlist::where('user_id', Auth::id())
                ->get(['id', 'nombre'])
        );
    }

    /**
     * Agregar canción a una playlist (sin duplicar).
     */
    public function addSong(Playlist $playlist, Cancion $cancion): JsonResponse
    {
        $this->authorizeOwner($playlist);

        $playlist->songs()->syncWithoutDetaching([$cancion->id]);

        return response()->json(['message' => 'Canción agregada a la playlist']);
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
            'id'      => $playlist->id,
            'nombre'  => $playlist->nombre,
            'message' => 'Playlist creada correctamente',
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
}
