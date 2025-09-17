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
     * Página "Álbumes" (menu_album) del usuario autenticado.
     */
    public function albumsMenu(Request $request): View
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

    public function miPerfil(): View
    {
        return $this->show(Auth::id());
    }

    /**
     * Actualiza datos del perfil y sube avatar/banner LOCALMENTE.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'nuevo_nombre_artistico' => 'nullable|string|max:255',
            'bio'    => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:5120',  // 5MB
            'banner' => 'nullable|image|max:8192',  // 8MB
        ]);

        if ($request->filled('nuevo_nombre_artistico')) {
            $user->nombre_artistico = $request->nuevo_nombre_artistico;
        }
        if ($request->filled('bio')) {
            $user->biografia = $request->bio;
        }

        // Avatar
        if ($request->hasFile('avatar')) {
            $relative = $request->file('avatar')->store('avatars', 'public'); // storage/app/public/avatars/...
            // Limpia el anterior si era ruta relativa
            $this->deleteLocalIfRelative($user->avatar ?? null);
            $user->avatar = $relative; // guarda la ruta relativa
        }

        // Banner
        if ($request->hasFile('banner')) {
            $relative = $request->file('banner')->store('banners', 'public'); // storage/app/public/banners/...
            $this->deleteLocalIfRelative($user->banner ?? null);
            $user->banner = $relative;
        }

        $user->save();

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

    /**
     * Cambiar rol entre usuario/artista.
     * Si regresa a "usuario", elimina sus canciones/álbumes y limpia archivos locales.
     */
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

            // Elimina canciones con sus archivos
            Cancion::where('user_id', $user->id)->get()->each(function (Cancion $s) {
                $this->deleteLocalIfRelative($s->audio_path ?? $s->audio ?? null);
                $this->deleteLocalIfRelative($s->cover_path ?? $s->portada ?? null);
                $s->delete();
            });

            // Elimina álbumes y sus portadas
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
        // Solo 'es' o 'en'
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

    /* ================== Helpers privados ================== */

    /**
     * Elimina un archivo del disco 'public' si la ruta es relativa (no URL http/https).
     */
    private function deleteLocalIfRelative(?string $path): void
    {
        if (!$path) return;

        // Si es URL absoluta, no borrar
        if (preg_match('~^https?://~i', $path)) {
            return;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return;
            }
        } catch (\Throwable $e) { /* noop */ }

        // Fallback al disco por defecto si hiciera falta
        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable $e) { /* noop */ }
    }
}
