<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = null;

        // 1️⃣ Usuario autenticado → usar idioma de BD
        if (auth()->check() && auth()->user()->idioma) {
            $locale = auth()->user()->idioma;
        }
        // 2️⃣ Invitado con idioma en sesión
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
        }

        // 3️⃣ Si no hay nada, usar el idioma por defecto de config/app.php
        if (!$locale) {
            $locale = config('app.locale');
        }

        // 4️⃣ Sincronizar siempre con la sesión
        Session::put('locale', $locale);

        // 5️⃣ Aplicar el idioma en Laravel
        App::setLocale($locale);

        // 🔎 Log opcional (puedes quitarlo en producción)
        Log::info('🔥 SetLocale ejecutado', [
            'userLang'     => auth()->check() ? auth()->user()->idioma : null,
            'sessionLang'  => Session::get('locale'),
            'defaultLang'  => config('app.locale'),
            'finalApplied' => App::getLocale(),
        ]);

        return $next($request);
    }
}
