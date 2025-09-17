<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cancion;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PerfilController extends Controller
{
<<<<<<< HEAD
    /**
     * Mostrar el formulario del perfil del usuario.
     */
    public function edit(Request $request): View
    {
        // Obtener el usuario autenticado
        $user = $request->user();

        // Obtener los álbumes asociados al usuario
        $albumes = Album::where('user_id', $user->id)->get();

        // Normalizar los álbumes para la vista
        $albumsNormalized = collect($albumes)->map(function($a) {
            return (object)[
                'id'      => $a->id,
                'titulo'  => $a->title ?? $a->titulo ?? 'Sin título',
                'portada' => $a->cover_path ?? $a->portada ?? null,
            ];
        });

        // Paginación de los álbumes (4 álbumes por página)
        $albumPages = $albumsNormalized->chunk(4);

        // Pasar datos a la vista
        return view('profile.edit', [
            'user' => $user,
            'albumPages' => $albumPages,
        ]);
    }

    /**
     * Actualizar la información del perfil del usuario.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        // Si el email ha cambiado, eliminar la verificación de email
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
=======
    public function show($id): View
    {
        $user = User::findOrFail($id);

        $canciones = Cancion::where('user_id', $user->id)->latest()->get();
        $albumes   = Album::where('user_id', $user->id)->latest()->get();

        $lanzamientos = collect();

        foreach ($albumes as $album) {
            $lanzamientos->push([
                'tipo'       => 'album',
                'titulo'     => $album->titulo ?: ($album->title ?? 'Sin título'),
                'cover'      => $album->portada ?? $album->cover_path ?? null,
                'anio'       => $album->anio ?? optional($album->created_at)->format('Y'),
                'created_at' => $album->created_at,
            ]);
>>>>>>> recup-ayer
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

        $lanzamientos = $lanzamientos
            ->filter(fn($x) => !empty($x['titulo']))
            ->sortByDesc('created_at')
            ->values();

        return view('ed_perfil', compact('user', 'canciones', 'albumes', 'lanzamientos'));
    }

    public function releasesAll(Request $request, $userId): View
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

        $all = $albums->merge($songs)
            ->filter(fn($x) => !empty($x['titulo']))
            ->sortByDesc('created_at')
            ->values();

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
<<<<<<< HEAD
     * Eliminar la cuenta del usuario.
=======
     * Vista "Mis álbumes" / detalle de álbum con edición condicional.
     * - Si viene ?album=ID y NO eres dueño => muestra solo ese álbum (modo lectura).
     * - Si eres dueño => lista tus álbumes y el seleccionado editable.
>>>>>>> recup-ayer
     */
    public function albumsMenu(Request $request): View
    {
        $auth = $request->user();
        $albumId = (int) $request->query('album');

        if ($albumId) {
            $album = Album::with(['user', 'songs.user'])->findOrFail($albumId);
            $owner = $auth && $album->user_id === $auth->id;

            if ($owner) {
                $albumes = Album::with('songs')->where('user_id', $auth->id)->latest()->get();
                $user = $auth;
            } else {
                $albumes = collect([$album]); // solo ese álbum
                $user = $album->user;
            }

            return view('menu_album', [
                'user'    => $user,
                'albumes' => $albumes,
                'album'   => $album,
            ]);
        }

        // Sin ?album, lista del dueño
        $user = $auth;
        $albumes = Album::with('songs')->where('user_id', $user->id)->latest()->get();

        return view('menu_album', compact('user', 'albumes'));
    }

    public function miPerfil(): View
    {
        return $this->show(Auth::id());
    }

    public function update(Request $request): RedirectResponse
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

<<<<<<< HEAD
        // Desconectar al usuario
        Auth::logout();

        // Eliminar al usuario de la base de datos
        $user->delete();

        // Invalidar la sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();
=======
        if ($request->hasFile('avatar')) {
            $relative = $request->file('avatar')->store('avatars', 'public');
            $this->deleteLocalIfRelative($user->avatar ?? null);
            $user->avatar = $relative;
        }

        if ($request->hasFile('banner')) {
            $relative = $request->file('banner')->store('banners', 'public');
            $this->deleteLocalIfRelative($user->banner ?? null);
            $user->banner = $relative;
        }

        $user->save();
>>>>>>> recup-ayer

        return redirect()
            ->route('perfil.show', $user->id)
            ->with('success', __('account.profile_updated'));
    }

    public function follow($userId): RedirectResponse
    {
        $user = Auth::user();
        if ($user->isFollowing($userId)) {
            return redirect()->back()->with('error', __('account.already_following'));
        }
        $user->followings()->attach($userId);
        return redirect()->back()->with('success', __('account.now_following'));
    }

    public function unfollow($userId): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isFollowing($userId)) {
            return redirect()->back()->with('error', __('account.not_following'));
        }
        $user->followings()->detach($userId);
        return redirect()->back()->with('success', __('account.unfollowed'));
    }

    public function followArtistList(): View
    {
        $user = Auth::user();
        $artistasSeguidos = $user->followings;
        return view('follow_artist', compact('artistasSeguidos'));
    }

    public function toggleRole(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($request->modo === 'artista' && !$user->es_artista) {
            $request->validate([
                'nombre_artistico' => 'required|string|max:255'
            ]);

            $user->es_artista = 1;
            $user->nombre_artistico = $request->nombre_artistico;
            $user->save();

            return back()->with('status', __('account.role_artist'));
        }

        if ($request->modo === 'usuario' && $user->es_artista) {
            if (!$request->has('confirmar')) {
                return back()->with('warning', __('account.role_user_confirm'));
            }

            Cancion::where('user_id', $user->id)->get()->each(function (Cancion $s) {
                $this->deleteLocalIfRelative($s->audio_path ?? $s->audio ?? null);
                $this->deleteLocalIfRelative($s->cover_path ?? $s->portada ?? null);
                $s->delete();
            });

            Album::where('user_id', $user->id)->get()->each(function (Album $a) {
                $this->deleteLocalIfRelative($a->cover_path ?? $a->portada ?? null);
                $a->delete();
            });

            $user->es_artista = 0;
            $user->nombre_artistico = null;
            $user->save();

            return back()->with('status', __('account.role_user_done'));
        }

        return back()->with('info', __('account.no_changes'));
    }

    public function setLanguage(Request $request): RedirectResponse
    {
        $lang = $request->input('lang');
        if (!in_array($lang, ['es', 'en'])) {
            $lang = 'en';
        }

        if (auth()->check()) {
            $user = auth()->user();
            $user->idioma = $lang;
            $user->save();
        } else {
            session(['locale' => $lang]);
        }

        App::setLocale($lang);

        return back()->with('status', __('account.language_changed'));
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
