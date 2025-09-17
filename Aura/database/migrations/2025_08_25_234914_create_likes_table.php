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

            // Debe referenciar la tabla real de canciones: 'songs'
            $table->foreignId('song_id')
                ->constrained('songs')       // <- importante
                ->cascadeOnDelete();

            $table->timestamps();

            // Evita duplicados (un usuario no puede likear 2 veces la misma canción)
            $table->unique(['user_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
