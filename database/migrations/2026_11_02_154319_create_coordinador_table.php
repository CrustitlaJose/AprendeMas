<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coordinador', function (Blueprint $table) {
            $table->id('id_coordinador');
            $table->foreignId('id_usuario')->constrained('usuario', 'id_usuario')->onDelete('cascade');
            $table->string('nombre');
            $table->string('apellidos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coordinador');
    }
};
