<?php

namespace App\Http\Controllers\Auth;

use App\Notifications\RegistroExitoso;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredArtController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register-artista');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // fecha límite para que tenga al menos 12 años
        $mayorDe12 = now()->subYears(12)->toDateString();

        $request->validate([
            'nombre'            => ['required', 'string', 'max:25'],
            'email'             => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password'          => [
                'required',
                'confirmed',
                // Mínimo 8 + 1 mayúscula + 1 número + 1 símbolo
                Rules\Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'fecha_nacimiento'  => ['required', 'date', 'after:1900-01-01', 'before_or_equal:'.$mayorDe12],
            'es_artista'        => ['sometimes', 'boolean'],
            'nombre_artistico'  => ['required', 'string', 'max:25'],
            'biografia'         => ['nullable', 'string'],
            'generos'           => ['nullable', 'array'],
            'generos.*'         => ['string'],
        ]);

        $user = User::create([
            'nombre'            => $request->nombre,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'fecha_nacimiento'  => $request->fecha_nacimiento,
            'es_artista'        => true, // según tu flujo, lo forzamos a artista
            'nombre_artistico'  => $request->nombre_artistico,
            'biografia'         => $request->biografia,
            'generos'           => $request->generos ? json_encode($request->generos) : null,
        ]);

        $user->notify(new RegistroExitoso);

        return redirect(route('login', absolute: false));
    }
}
