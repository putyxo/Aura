<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cancion;
use App\Jobs\GenerateLyricsJob;

class GenerateLyricsCommand extends Command
{
    /**
     * Nombre y firma del comando.
     *
     * php artisan lyrics:generate {songId}
     */
    protected $signature = 'lyrics:generate {songId}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Genera o regenera la letra de una canción usando IA';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        $songId = $this->argument('songId');
        $cancion = Cancion::find($songId);

        if (!$cancion) {
            $this->error("Canción con ID {$songId} no encontrada.");
            return 1;
        }

        // Disparar job de generación de letras
        GenerateLyricsJob::dispatch($cancion);

        $this->info("✅ Se ha lanzado el Job para generar la letra de: {$cancion->title}");
        $this->info("Revisa tu tabla lyrics después de correr: php artisan queue:work");

        return 0;
    }
}
