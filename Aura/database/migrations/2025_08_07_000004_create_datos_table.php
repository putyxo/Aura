<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatosTable extends Migration
{
    public function up()
    {
        Schema::create('datos', function (Blueprint $table) {
            $table->id();
            $table->string('userName')->nullable();
            $table->string('userEmail', 191)->unique()->nullable();
            $table->string('userPassword')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('datos');
    }
}
