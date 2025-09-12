<?php

namespace App\Http\Middleware;

use Closure;

class TestMiddleware
{
    public function handle($request, Closure $next)
    {
        dd('🔥 TestMiddleware ejecutado correctamente');
        return $next($request);
    }
}
