<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->json('genero_favorito')->nullable();
            $table->boolean('es_artista')->default(false);
            $table->string('nombre_artistico')->nullable();
            $table->text('biografia')->nullable();
            $table->string('imagen_portada')->nullable();
            $table->boolean('verificado')->default(false);
            $table->json('generos')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('ultima_sesion')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
