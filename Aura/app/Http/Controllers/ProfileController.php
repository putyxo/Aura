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

        // Desconectar al usuario
        Auth::logout();

        // Eliminar al usuario de la base de datos
        $user->delete();

        // Invalidar la sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
