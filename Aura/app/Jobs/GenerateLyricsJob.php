<?php

namespace App\Jobs;

use App\Models\Cancion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateLyricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Cancion $cancion) {}

    public function handle(): void
    {
        Log::info("🎵 Generando letra para canción ID={$this->cancion->id}, título=\"{$this->cancion->title}\"");

        try {
            $client = \OpenAI::client(env('OPENAI_API_KEY'));

            // 📥 Descargar audio desde la URL
            $response = Http::get($this->cancion->audio_path);
            if (!$response->successful()) {
                Log::error("❌ No se pudo descargar el audio desde {$this->cancion->audio_path}. ".
                           "HTTP status: ".$response->status());
                return;
            }

            // Guardar temporalmente con extensión .mp3
            $tmpFile = tempnam(sys_get_temp_dir(), 'mp3_') . '.mp3';
            file_put_contents($tmpFile, $response->body());

            $size = filesize($tmpFile);
            Log::info("📂 Archivo temporal creado en {$tmpFile}, tamaño: {$size} bytes");

            if ($size === 0) {
                Log::error("❌ El archivo descargado está vacío. Posible problema con el link.");
                unlink($tmpFile);
                return;
            }

            // 🎙️ 1. Transcripción con Whisper
            $trans = $client->audio()->transcribe([
                'model' => env('OPENAI_TRANSCRIBE_MODEL', 'gpt-4o-mini-transcribe'),
                'file'  => fopen($tmpFile, 'r'),
            ]);

            if (empty($trans->text)) {
                Log::error("❌ No se obtuvo texto para canción ID={$this->cancion->id}");
                unlink($tmpFile);
                return;
            }

            Log::info("📝 Texto transcrito (preview): ".substr($trans->text, 0, 80)."...");

            // 🎼 2. Conversión a karaoke LRC
            $lyrics = $client->chat()->create([
                'model' => 'gpt-4o-mini', // barato y suficiente para formatear
                'messages' => [
                    ['role'=>'system','content'=>'Convierte este texto a letras sincronizadas en formato LRC (timestamps tipo karaoke).'],
                    ['role'=>'user','content'=>$trans->text],
                ],
            ]);

            $lrcContent = $lyrics->choices[0]->message->content ?? null;

            if (!$lrcContent) {
                Log::error("❌ GPT no devolvió letra en LRC para canción ID={$this->cancion->id}");
                unlink($tmpFile);
                return;
            }

            // 📝 3. Guardar en la BD
            $this->cancion->lyric()->create([
                'content' => $lrcContent,
            ]);

            Log::info("✅ Letra guardada en BD para canción ID={$this->cancion->id}");

            // 🗑️ 4. Borrar archivo temporal
            unlink($tmpFile);

        } catch (\Throwable $e) {
            Log::error("💥 Error generando letra para canción ID={$this->cancion->id}: ".$e->getMessage(), [
                'exception' => $e,
                'song_id'   => $this->cancion->id,
                'title'     => $this->cancion->title,
            ]);
        }
    }
}
