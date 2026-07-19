<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('compra_pagos', 'nros_cheques')) {
                $table->json('nros_cheques')->nullable()->after('nro_cheque');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compra_pagos', function (Blueprint $table) {
            if (Schema::hasColumn('compra_pagos', 'nros_cheques')) {
                $table->dropColumn('nros_cheques');
            }
        });
    }
};
