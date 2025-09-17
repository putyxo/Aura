<?php

namespace App\Jobs;

use App\Models\Cancion;
use App\Models\Lyric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI;

class GenerateLyricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cancion;

    public function __construct(Cancion $cancion)
    {
        $this->cancion = $cancion;
    }

    public function handle(): void
    {
        try {
            Log::info("🎵 Generando letra para canción ID={$this->cancion->id}, título=\"{$this->cancion->title}\"");

            $client = OpenAI::client(env('OPENAI_API_KEY'));
            $audioPath = storage_path('app/public/'.$this->cancion->audio_path);

            if (!file_exists($audioPath)) {
                Log::error("❌ Archivo de audio no encontrado: {$audioPath}");
                return;
            }

            Log::info("📂 Enviando a transcripción con Whisper: {$audioPath}");

            // 🔹 Intento con verbose_json
            $response = $client->audio()->transcribe([
                'model' => env('OPENAI_TRANSCRIBE_MODEL', 'whisper-1'),
                'file'  => fopen($audioPath, 'r'),
                'response_format' => 'verbose_json',
            ]);

            $data     = $response->toArray();
            Log::debug("📡 Respuesta completa de Whisper:", $data);

            $texto    = $data['text'] ?? null;
            $segments = $data['segments'] ?? [];

            Log::debug("✍️ Texto detectado:", [$texto]);
            Log::debug("🔖 Segments detectados:", $segments);

            if ($texto) {
                $json = json_encode($segments ?? [], JSON_UNESCAPED_UNICODE);

                Lyric::updateOrCreate(
                    ['song_id' => $this->cancion->id],
                    [
                        'content'       => $texto,
                        'json_segments' => $json,
                    ]
                );

                Log::info("📝 Letra guardada correctamente para canción ID={$this->cancion->id}");
                Log::debug("📥 Guardado en DB:", [
                    'song_id' => $this->cancion->id,
                    'content' => mb_substr($texto, 0, 100) . '...',
                    'json_segments' => $json ? mb_substr($json, 0, 200).'...' : '[]'
                ]);
            } else {
                Log::warning("⚠️ No se recibió texto de transcripción para canción ID={$this->cancion->id}");
            }

        } catch (\Throwable $e) {
            // 🚨 Log específico si es problema de verbose_json
            if (str_contains($e->getMessage(), 'response_format')) {
                Log::error("❌ El modelo no soporta verbose_json. Usa 'json' o 'text'. Error: ".$e->getMessage());
            } else {
                Log::error("💥 Error en GenerateLyricsJob para canción ID={$this->cancion->id}: ".$e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }
}
