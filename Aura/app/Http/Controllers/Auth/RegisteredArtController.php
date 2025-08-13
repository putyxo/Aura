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
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'fecha_nacimiento' => ['required', 'date'],
            'es_artista' => ['1', 'boolean'],
            'nombre_artistico' => ['required', 'string', 'max:255'],
            'biografia' => ['nullable', 'string'],
            'generos' => ['nullable', 'array'],
        ]);

      $user = User::create([
        'nombre' => $request->nombre,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'es_artista' => true,
        'nombre_artistico' => $request->nombre_artistico,
        'biografia' => $request->biografia,
        'generos' => $request->generos ? json_encode($request->generos) : null,

        
    ]);
        $user->notify(new RegistroExitoso);
        

        return redirect(route('login', absolute: false));
    }
}
