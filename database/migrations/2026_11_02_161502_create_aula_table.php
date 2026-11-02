<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aula', function (Blueprint $table) {
            $table->id('id_aula');
            $table->string('nombre_cubiculo')->unique();
            $table->string('capacidad');
            $table->string('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aula');
    }
};
