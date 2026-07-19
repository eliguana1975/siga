<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ajustes', function (Blueprint $table) {
            if (! Schema::hasColumn('ajustes', 'codigo_postal')) {
                $table->string('codigo_postal', 20)->nullable()->after('direccion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ajustes', function (Blueprint $table) {
            if (Schema::hasColumn('ajustes', 'codigo_postal')) {
                $table->dropColumn('codigo_postal');
            }
        });
    }
};
