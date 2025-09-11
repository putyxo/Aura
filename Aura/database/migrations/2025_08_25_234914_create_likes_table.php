<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()              // -> references users(id)
                ->cascadeOnDelete();

            // DEBE referenciar la tabla real de canciones: 'songs'
            $table->foreignId('song_id')
                ->constrained('songs')       // <- AQUÍ está la corrección
                ->cascadeOnDelete();

            $table->timestamps();

            // evita duplicados (un usuario no puede likear la misma canción 2 veces)
            $table->unique(['user_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
