<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_equalizers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->decimal('preamp', 5, 2)->default(0);

            $table->decimal('band_60', 5, 2)->default(0);
            $table->decimal('band_170', 5, 2)->default(0);
            $table->decimal('band_310', 5, 2)->default(0);
            $table->decimal('band_600', 5, 2)->default(0);
            $table->decimal('band_1000', 5, 2)->default(0);
            $table->decimal('band_3000', 5, 2)->default(0);
            $table->decimal('band_6000', 5, 2)->default(0);
            $table->decimal('band_12000', 5, 2)->default(0);
            $table->decimal('band_14000', 5, 2)->default(0);
            $table->decimal('band_16000', 5, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_equalizers');
    }
};
