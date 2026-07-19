<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            if (! Schema::hasColumn('proveedores', 'condicion_pago_dias')) {
                $table->string('condicion_pago_dias', 80)->nullable()->after('forma_pago_preferida');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            if (Schema::hasColumn('proveedores', 'condicion_pago_dias')) {
                $table->dropColumn('condicion_pago_dias');
            }
        });
    }
};
