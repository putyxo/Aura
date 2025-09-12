<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cancion;
use App\Jobs\GenerateLyricsJob;

class RegenerateLyrics extends Command
{
    protected $signature = 'lyrics:regenerate {--limit=0 : Número máximo de canciones a procesar (0 = todas)}';
    protected $description = 'Regenera letras (LRC) para canciones que aún no tienen letra';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $query = Cancion::doesntHave('lyric');
        if ($limit > 0) $query->limit($limit);

        $songs = $query->get();
        if ($songs->isEmpty()) {
            $this->info("✅ No hay canciones pendientes de letra.");
            return self::SUCCESS;
        }

        foreach ($songs as $song) {
            $this->info("🎵 Encolando: {$song->title}");
            GenerateLyricsJob::dispatch($song);
        }

        $this->info("🚀 Se encolaron {$songs->count()} canciones para generar letra.");
        return self::SUCCESS;
    }
}
