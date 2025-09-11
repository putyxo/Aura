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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // IMPORTANTE: si tu tabla se llama 'canciones', usa 'canciones'
            $table->foreignId('song_id')
                  ->constrained(table: 'canciones') // <-- antes tenías 'songs'
                  ->cascadeOnDelete();

            $table->timestamps();
            $table->unique(['user_id', 'song_id']); // evita duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
