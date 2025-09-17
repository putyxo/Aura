<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('lyrics', function (Blueprint $table) {
            // Cambiar content a longText (si no lo es ya)
            $table->longText('content')->change();

            // Agregar json_segments si no existe
            if (!Schema::hasColumn('lyrics', 'json_segments')) {
                $table->longText('json_segments')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('lyrics', function (Blueprint $table) {
            // Revertir content a string(255) solo si era así antes
            $table->string('content', 255)->change();

            // Quitar json_segments
            $table->dropColumn('json_segments');
        });
    }
};
