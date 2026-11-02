<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesor_materia', function (Blueprint $table) {
            $table->foreignId('id_profesor')->constrained('profesor','id_profesor')->onDelete('cascade');
            $table->foreignId('id_materia')->constrained('materia','id_materia')->onDelete('cascade');
            $table->primary(['id_profesor','id_materia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesor_materia');
    }
};
