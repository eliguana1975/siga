<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('proveedores')) {
            Schema::table('proveedores', function (Blueprint $table) {
                if (! Schema::hasColumn('proveedores', 'forma_pago_preferida')) {
                    $table->string('forma_pago_preferida', 40)->nullable()->after('contacto');
                }

                if (! Schema::hasColumn('proveedores', 'datos_pago')) {
                    $table->text('datos_pago')->nullable()->after('forma_pago_preferida');
                }
            });
        }

        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table) {
                if (! Schema::hasColumn('compras', 'forma_pago')) {
                    $table->string('forma_pago', 40)->nullable()->after('comprobante');
                }

                if (! Schema::hasColumn('compras', 'datos_pago')) {
                    $table->text('datos_pago')->nullable()->after('forma_pago');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table) {
                if (Schema::hasColumn('compras', 'datos_pago')) {
                    $table->dropColumn('datos_pago');
                }

                if (Schema::hasColumn('compras', 'forma_pago')) {
                    $table->dropColumn('forma_pago');
                }
            });
        }

        if (Schema::hasTable('proveedores')) {
            Schema::table('proveedores', function (Blueprint $table) {
                if (Schema::hasColumn('proveedores', 'datos_pago')) {
                    $table->dropColumn('datos_pago');
                }

                if (Schema::hasColumn('proveedores', 'forma_pago_preferida')) {
                    $table->dropColumn('forma_pago_preferida');
                }
            });
        }
    }
};
