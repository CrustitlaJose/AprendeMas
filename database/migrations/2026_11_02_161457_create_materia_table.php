<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia', function (Blueprint $table) {
            $table->id('id_materia');
            $table->string('nombre_materia');
            $table->string('descripcion');
            $table->string('clave')->unique();
            $table->string('area');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia');
    }
};
