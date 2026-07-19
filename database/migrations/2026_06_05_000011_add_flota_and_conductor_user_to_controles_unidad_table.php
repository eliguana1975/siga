<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('controles_unidad', function (Blueprint $table) {
            $table->foreignId('flota_id')
                ->nullable()
                ->after('user_id')
                ->constrained('flota')
                ->nullOnDelete();

            $table->foreignId('conductor_user_id')
                ->nullable()
                ->after('flota_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('controles_unidad', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conductor_user_id');
            $table->dropConstrainedForeignId('flota_id');
        });
    }
};
