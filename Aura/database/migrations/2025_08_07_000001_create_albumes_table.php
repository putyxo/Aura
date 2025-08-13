<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlbumesTable extends Migration
{
    public function up(): void {
        Schema::create('albums', function (Blueprint $t) {
          $t->id();
          $t->foreignId('user_id')->constrained()->cascadeOnDelete(); // artista dueño
          $t->string('title');
          $t->string('genre')->nullable();
          $t->string('cover_path')->nullable(); // ruta en S3
          $t->date('release_date')->nullable();
          $t->timestamps();
        });
      }
      public function down(): void { Schema::dropIfExists('albums'); }
    }
