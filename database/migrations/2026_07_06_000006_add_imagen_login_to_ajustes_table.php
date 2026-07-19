<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ajustes', function (Blueprint $table) {
            if (! Schema::hasColumn('ajustes', 'imagen_login')) {
                $table->string('imagen_login')->nullable()->after('logo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ajustes', function (Blueprint $table) {
            if (Schema::hasColumn('ajustes', 'imagen_login')) {
                $table->dropColumn('imagen_login');
            }
        });
    }
};
