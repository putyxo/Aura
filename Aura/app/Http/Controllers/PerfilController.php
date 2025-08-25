<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cancion;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleDriveOAuthService;

class PerfilController extends Controller
{
    protected GoogleDriveOAuthService $drive;

    public function __construct(GoogleDriveOAuthService $drive)
    {
        $this->drive = $drive;
    }

    /**
     * Muestra el perfil de un usuario.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        // Obtener las canciones y álbumes del usuario
        $canciones = Cancion::where('user_id', $user->id)->get();
        $albumes   = Album::where('user_id', $user->id)->get();

        // Combinar álbumes y canciones para lanzamientos
        $lanzamientos = collect();
        foreach ($albumes as $album) {
            $lanzamientos->push([
                'tipo'       => 'album',
                'titulo'     => $album->titulo,
                'cover'      => $album->portada,
                'anio'       => $album->anio,
                'created_at' => $album->created_at,
            ]);
        }

        foreach ($canciones as $cancion) {
            $lanzamientos->push([
                'tipo'       => 'cancion',
                'titulo'     => $cancion->title,
                'cover'      => $cancion->cover_url,
                'anio'       => $cancion->created_at->format('Y'),
                'created_at' => $cancion->created_at,
            ]);
        }

        // Ordenar los lanzamientos
        $lanzamientos = $lanzamientos->sortByDesc('created_at');

        return view('ed_perfil', compact('user', 'canciones', 'albumes', 'lanzamientos'));
    }

    /**
     * Perfil del usuario autenticado
     */
    public function miPerfil()
    {
        return $this->show(Auth::id());
    }

    /**
     * Actualizar perfil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nuevo_nombre_artistico' => 'nullable|string|max:255',
            'bio'    => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:5120',
            'banner' => 'nullable|image|max:8192',
        ]);

        if ($request->filled('nuevo_nombre_artistico')) {
            $user->nombre_artistico = $request->nuevo_nombre_artistico;
        }
        if ($request->filled('bio')) {
            $user->biografia = $request->bio;
        }

        // Carpeta de destino en Drive
        $folderId = env('GOOGLE_DRIVE_UPLOAD_FOLDER_ID');

        // === AVATAR en Drive ===
        if ($request->hasFile('avatar')) {
            $file  = $request->file('avatar');
            $local = $file->getPathname();
            $name  = uniqid('avatar_') . '.' . $file->getClientOriginalExtension();
            $mime  = $file->getMimeType();

            $uploaded = $this->drive->uploadPublic($local, $name, $mime, $folderId);

            // Guardamos solo el ID del archivo
            $user->avatar = $uploaded['id'];
        }

        // === BANNER en Drive ===
        if ($request->hasFile('banner')) {
            $file  = $request->file('banner');
            $local = $file->getPathname();
            $name  = uniqid('banner_') . '.' . $file->getClientOriginalExtension();
            $mime  = $file->getMimeType();

            $uploaded = $this->drive->uploadPublic($local, $name, $mime, $folderId);

            // Guardamos solo el ID del archivo
            $user->banner = $uploaded['id'];
        }

        $user->save();

        return redirect()->route('perfil.show', $user->id)
            ->with('success', 'Perfil actualizado correctamente ✅');
    }

    /**
     * Seguir a un artista
     */
    public function follow($userId)
    {
        $user = Auth::user();
        if ($user->isFollowing($userId)) {
            return redirect()->back()->with('error', 'Ya sigues a este usuario.');
        }

        $user->followings()->attach($userId);
        return redirect()->back()->with('success', 'Ahora sigues a este artista.');
    }

    /**
     * Dejar de seguir a un artista
     */
    public function unfollow($userId)
    {
        $user = Auth::user();
        if (!$user->isFollowing($userId)) {
            return redirect()->back()->with('error', 'No sigues a este usuario.');
        }

        $user->followings()->detach($userId);
        return redirect()->back()->with('success', 'Has dejado de seguir a este artista.');
    }

    /**
     * Lista de artistas que sigue el usuario autenticado
     */
    public function followArtistList()
    {
        $user = Auth::user();
        $artistasSeguidos = $user->followings;
        return view('follow_artist', compact('artistasSeguidos'));
    }
}
