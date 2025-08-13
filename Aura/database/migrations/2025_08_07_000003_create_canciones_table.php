<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCancionesTable extends Migration
{
    public function up(): void {
        Schema::create('songs', function (Blueprint $t) {
          $t->id();
          $t->foreignId('user_id')->constrained()->cascadeOnDelete(); // artista dueño
          $t->foreignId('album_id')->nullable()->constrained()->nullOnDelete();
          $t->string('title');
          $t->string('genre')->nullable();
          $t->string('audio_path');          // ruta en S3
          $t->string('cover_path')->nullable(); // portada de single, opcional
          $t->integer('duration')->nullable();  // en segundos (si luego la calculas)
          $t->enum('status', ['draft','published'])->default('published');
          $t->timestamps();
        });
      }
      public function down(): void { Schema::dropIfExists('songs'); }
    }