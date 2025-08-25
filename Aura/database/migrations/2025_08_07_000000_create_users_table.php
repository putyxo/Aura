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

            // Básicos
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password');

            // Perfil / medios (TEXT por URLs largas; usa string si guardas solo IDs)
            $table->text('avatar')->nullable();
            $table->text('banner')->nullable();
            $table->text('imagen_portada')->nullable();

            // Datos adicionales de usuario
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero_favorito', 100)->nullable();  // <— está en tu $fillable
            $table->boolean('es_artista')->default(false);
            $table->string('nombre_artistico')->nullable();
            $table->text('biografia')->nullable();

            // Estados / verificación
            $table->boolean('verificado')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('ultima_sesion')->nullable();
            $table->boolean('is_active')->default(true);

            // Tokens y timestamps
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
