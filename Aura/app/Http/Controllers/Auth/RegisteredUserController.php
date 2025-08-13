<?php

namespace App\Http\Controllers\Auth;

use App\Notifications\RegistroExitoso;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Debe tener al menos 12 años
        $mayorDe12 = now()->subYears(12)->toDateString();

        $request->validate([
            'nombre'            => ['required', 'string', 'max:25'],
            'email'             => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password'          => [
                'required',
                'confirmed',
                // 8+ con mayúscula, número y símbolo
                Rules\Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'fecha_nacimiento'  => ['required', 'date', 'after:1900-01-01', 'before_or_equal:'.$mayorDe12],
            'es_artista'        => ['sometimes', 'boolean'],
            'nombre_artistico'  => ['nullable', 'string', 'max:255', 'required_if:es_artista,1'],
            'biografia'         => ['nullable', 'string'],
            'generos'           => ['nullable', 'array'],
            'generos.*'         => ['string'],
        ]);

        $user = User::create([
            'nombre'            => $request->nombre,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'fecha_nacimiento'  => $request->fecha_nacimiento,
            'es_artista'        => $request->boolean('es_artista'),
            'nombre_artistico'  => $request->nombre_artistico,
            'biografia'         => $request->biografia,
            'generos'           => $request->generos ? json_encode($request->generos) : null,
        ]);

        // Notificar y disparar evento de registro
        $user->notify(new RegistroExitoso);
        event(new Registered($user));

        return redirect(route('login', absolute: false));
    }
}
