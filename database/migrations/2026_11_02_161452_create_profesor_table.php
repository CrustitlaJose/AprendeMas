<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesor', function (Blueprint $table) {
            $table->id('id_profesor');
            $table->foreignId('id_usuario')->constrained('usuario','id_usuario')->onDelete('cascade');
            $table->string('num_empleado')->unique();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('departamento');
            $table->string('especialidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesor');
    }
};
