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
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Mostrar los álbumes del usuario.
     */
    public function menuAlbum(Request $request): View
    {
        // Obtener el usuario autenticado
        $user = $request->user();

        // Obtener los álbumes asociados al usuario con sus canciones
        $albumes = Album::where('user_id', $user->id)->with(['songs' => function($query) {
            $query->select('id', 'album_id', 'titulo', 'audio_path');
        }])->get();

        // Agrupar álbumes en páginas de 4 (para grid 2x2)
        $albumPages = $albumes->chunk(4);

        // Obtener las canciones que el usuario ha dado like, con información del álbum
        $likedSongs = $user->likes()->with(['album' => function($query) {
            $query->select('id', 'titulo');
        }])->select('id', 'titulo', 'audio_path', 'album_id')->get();

        // Calcular el número de seguidores
        $followersCount = method_exists($user, 'followers') ? $user->followers()->count() : (int)($user->seguidores ?? 0);

        // Pasar datos a la vista
        return view('menu_album', [
            'user' => $user,
            'albumPages' => $albumPages,
            'likedSongs' => $likedSongs,
            'followersCount' => $followersCount,
        ]);
    }

    /**
     * Update the user's profile information.
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
     * Delete the user's account.
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
