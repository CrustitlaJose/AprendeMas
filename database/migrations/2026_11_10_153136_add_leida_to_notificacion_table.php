<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificacion', function (Blueprint $table): void {
            $table->boolean('leida')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('notificacion', function (Blueprint $table): void {
            $table->dropColumn('leida');
        });
    }
};
