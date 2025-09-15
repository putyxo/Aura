<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Album;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Mostrar el formulario de perfil del usuario.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Mostrar los álbumes del usuario (grid 2x2 paginado en memoria).
     */
    public function menuAlbum(Request $request): View
    {
        $user = $request->user();

        // Álbumes del usuario con canciones.
        // Usamos COALESCE para que "titulo" funcione aunque el campo sea title/titulo/nombre.
        $albumes = Album::where('user_id', $user->id)
            ->with(['songs' => function ($query) {
                $query
                    ->select('id', 'album_id', 'audio_path')
                    ->selectRaw('COALESCE(title, titulo, nombre) AS titulo');
            }])
            ->get();

        // Agrupar en páginas de 4 (2x2)
        $albumPages = $albumes->chunk(4);

        // Canciones que el usuario ha dado like, con datos del álbum.
        $likedSongs = $user->likes()
            ->with(['album' => function ($q) {
                $q->select('id')
                  ->selectRaw('COALESCE(title, titulo) AS titulo');
            }])
            ->select('id', 'album_id', 'audio_path')
            ->selectRaw('COALESCE(title, titulo, nombre) AS titulo')
            ->get();

        // Número de seguidores (usa relación followers() si existe)
        $followersCount = method_exists($user, 'followers')
            ? $user->followers()->count()
            : (int) ($user->seguidores ?? 0);

        return view('menu_album', [
            'user'           => $user,
            'albumPages'     => $albumPages,
            'likedSongs'     => $likedSongs,
            'followersCount' => $followersCount,
        ]);
    }

    /**
     * Actualizar la información del perfil del usuario.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Eliminar la cuenta del usuario.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
