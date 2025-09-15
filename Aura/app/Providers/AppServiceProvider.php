<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Carga helpers globales si existen
        $helpers = app_path('helpers.php');
        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Parche global: si alguien llama @vite() SIN argumentos,
         * no renderizamos nada (evita "Too few arguments..." del invocable Vite).
         */
        Blade::directive('vite', function ($expression) {
            $expr = trim((string) ($expression ?? ''));

            // Si quedó @vite() vacío, no generar salida
            if ($expr === '') {
                return '<?php /* @vite() vacío — ignorado para evitar error */ ?>';
            }

            // Comportamiento normal cuando hay argumentos
            return "<?php echo app('Illuminate\\\\Foundation\\\\Vite')($expression); ?>";
        });

        // Fijar locale según preferencia del usuario autenticado
        View::composer('*', function () {
            if (Auth::check() && !empty(Auth::user()->idioma)) {
                app()->setLocale(Auth::user()->idioma);
            }
        });
    }
}
