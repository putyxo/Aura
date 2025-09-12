<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cancion;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleDriveOAuthService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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

        // Obtener TODO (puedes limitar/paginar en DB si hace falta)
        $canciones = Cancion::where('user_id', $user->id)->latest()->get();
        $albumes   = Album::where('user_id', $user->id)->latest()->get();

        // Combinar álbumes y canciones para lanzamientos (normalizados)
        $lanzamientos = collect();

        foreach ($albumes as $album) {
            $lanzamientos->push([
                'tipo'       => 'album',
                'titulo'     => $album->titulo ?: ($album->title ?? 'Sin título'),
                'cover'      => $album->portada ?? $album->cover_path ?? null,
                'anio'       => $album->anio ?? optional($album->created_at)->format('Y'),
                'created_at' => $album->created_at,
            ]);
        }

        foreach ($canciones as $cancion) {
            $lanzamientos->push([
                'tipo'       => 'cancion',
                'titulo'     => $cancion->title ?: ($cancion->nombre ?? 'Sin título'),
                'cover'      => $cancion->cover_url ?? $cancion->portada ?? $cancion->cover_path ?? null,
                'anio'       => optional($cancion->created_at)->format('Y'),
                'created_at' => $cancion->created_at,
            ]);
        }

        // Ordena por fecha y evita elementos totalmente vacíos
        $lanzamientos = $lanzamientos
            ->filter(fn($x) => !empty($x['titulo']))
            ->sortByDesc('created_at')
            ->values();

        return view('ed_perfil', compact('user', 'canciones', 'albumes', 'lanzamientos'));
    }

    /**
     * Página "Ver todo" de lanzamientos con paginación.
     */
    public function releasesAll(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $albums = Album::where('user_id', $user->id)->latest()->get()->map(function($a){
            return [
                'id'         => $a->id,
                'tipo'       => 'album',
                'titulo'     => $a->titulo ?: ($a->title ?? 'Sin título'),
                'cover'      => $a->portada ?? $a->cover_path ?? null,
                'anio'       => $a->anio ?? optional($a->created_at)->format('Y'),
                'created_at' => $a->created_at,
            ];
        });

        $songs = Cancion::where('user_id', $user->id)->latest()->get()->map(function($c){
            return [
                'id'         => $c->id,
                'tipo'       => 'cancion',
                'titulo'     => $c->title ?: ($c->nombre ?? 'Sin título'),
                'cover'      => $c->cover_url ?? $c->portada ?? $c->cover_path ?? null,
                'anio'       => optional($c->created_at)->format('Y'),
                'created_at' => $c->created_at,
            ];
        });

        /** @var Collection $all */
        $all = $albums->merge($songs)
            ->filter(fn($x) => !empty($x['titulo']))
            ->sortByDesc('created_at')
            ->values();

        // Paginación manual de una Collection
        $perPage   = 24;
        $page      = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $items     = $all->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new LengthAwarePaginator($items, $all->count(), $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        return view('perfil.releases_all', [
            'user'         => $user,
            'lanzamientos' => $items,
            'pagination'   => $paginator,
        ]);
    }

    /**
     * Página "Álbumes" (menu_album) del usuario autenticado.
     * Envía $user, $albumes y $followersCount a la vista para evitar errores.
     */
    public function albumsMenu(Request $request)
    {
        $user = $request->user(); // autenticado
        $albumes = $user
            ? Album::where('user_id', $user->id)->latest()->get()
            : collect();

        $followersCount = ($user && method_exists($user, 'followers'))
            ? $user->followers()->count()
            : 0;

        return view('menu_album', [
            'user' => $user,
            'albumes' => $albumes,
            'followersCount' => $followersCount,
        ]);
    }

    public function miPerfil()
    {
        return $this->show(Auth::id());
    }

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

        // Carpeta en Drive
        $folderId = env('GOOGLE_DRIVE_UPLOAD_FOLDER_ID');

        // Avatar
        if ($request->hasFile('avatar')) {
            $file  = $request->file('avatar');
            $local = $file->getPathname();
            $name  = uniqid('avatar_') . '.' . $file->getClientOriginalExtension();
            $mime  = $file->getMimeType();
            $uploaded = $this->drive->uploadPublic($local, $name, $mime, $folderId);
            $user->avatar = $uploaded['id']; // ID del archivo
        }

        // Banner
        if ($request->hasFile('banner')) {
            $file  = $request->file('banner');
            $local = $file->getPathname();
            $name  = uniqid('banner_') . '.' . $file->getClientOriginalExtension();
            $mime  = $file->getMimeType();
            $uploaded = $this->drive->uploadPublic($local, $name, $mime, $folderId);
            $user->banner = $uploaded['id']; // ID del archivo
        }

        $user->save();

        return redirect()->route('perfil.show', $user->id)
            ->with('success', 'Perfil actualizado correctamente ✅');
    }

    public function follow($userId)
    {
        $user = Auth::user();
        if ($user->isFollowing($userId)) {
            return redirect()->back()->with('error', 'Ya sigues a este usuario.');
        }
        $user->followings()->attach($userId);
        return redirect()->back()->with('success', 'Ahora sigues a este artista.');
    }

    public function unfollow($userId)
    {
        $user = Auth::user();
        if (!$user->isFollowing($userId)) {
            return redirect()->back()->with('error', 'No sigues a este usuario.');
        }
        $user->followings()->detach($userId);
        return redirect()->back()->with('success', 'Has dejado de seguir a este artista.');
    }

    public function followArtistList()
    {
        $user = Auth::user();
        $artistasSeguidos = $user->followings;
        return view('follow_artist', compact('artistasSeguidos'));
    }
}
