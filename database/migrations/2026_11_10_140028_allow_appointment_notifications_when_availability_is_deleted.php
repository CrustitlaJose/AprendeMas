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
        Schema::table('asesoria', function (Blueprint $table): void {
            $table->dropForeign(['id_disponibilidad']);
            $table->unsignedBigInteger('id_disponibilidad')->nullable()->change();
            $table->foreign('id_disponibilidad')
                ->references('id_disponibilidad')
                ->on('disponibilidad')
                ->nullOnDelete();
        });

        Schema::table('notificacion', function (Blueprint $table): void {
            $table->dropForeign(['id_asesoria']);
            $table->unsignedBigInteger('id_asesoria')->nullable()->change();
            $table->foreign('id_asesoria')
                ->references('id_asesoria')
                ->on('asesoria')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notificacion', function (Blueprint $table): void {
            $table->dropForeign(['id_asesoria']);
            $table->unsignedBigInteger('id_asesoria')->nullable(false)->change();
            $table->foreign('id_asesoria')
                ->references('id_asesoria')
                ->on('asesoria')
                ->cascadeOnDelete();
        });

        Schema::table('asesoria', function (Blueprint $table): void {
            $table->dropForeign(['id_disponibilidad']);
            $table->unsignedBigInteger('id_disponibilidad')->nullable(false)->change();
            $table->foreign('id_disponibilidad')
                ->references('id_disponibilidad')
                ->on('disponibilidad')
                ->cascadeOnDelete();
        });
    }
};
