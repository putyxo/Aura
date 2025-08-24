<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();

            // El usuario que sigue
            $table->unsignedBigInteger('follower_id');

            // El usuario seguido
            $table->unsignedBigInteger('followed_id');

            $table->timestamps();

            // Relaciones con users
            $table->foreign('follower_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('followed_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            // Evitar que se repita el mismo follow
            $table->unique(['follower_id', 'followed_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
