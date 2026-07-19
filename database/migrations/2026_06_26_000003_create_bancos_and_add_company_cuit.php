<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bancos')) {
            Schema::create('bancos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 120)->unique();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('ajustes', function (Blueprint $table) {
            if (! Schema::hasColumn('ajustes', 'cuit')) {
                $table->string('cuit', 30)->nullable()->after('nombre');
            }
        });

        Schema::table('compra_pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('compra_pagos', 'banco_id')) {
                $table->foreignId('banco_id')->nullable()->after('tipo_cheque')->constrained('bancos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            if (Schema::hasColumn('compra_pagos', 'banco_id')) {
                $table->dropConstrainedForeignId('banco_id');
            }
        });

        Schema::table('ajustes', function (Blueprint $table) {
            if (Schema::hasColumn('ajustes', 'cuit')) {
                $table->dropColumn('cuit');
            }
        });

        Schema::dropIfExists('bancos');
    }
};
