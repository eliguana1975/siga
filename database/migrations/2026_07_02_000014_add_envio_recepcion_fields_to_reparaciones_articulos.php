<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones_articulos', function (Blueprint $table) {
            if (! Schema::hasColumn('reparaciones_articulos', 'quien_envia_nombre')) {
                $table->string('quien_envia_nombre', 150)->nullable()->after('ciudad_id');
            }

            if (! Schema::hasColumn('reparaciones_articulos', 'quien_envia_documento')) {
                $table->string('quien_envia_documento', 50)->nullable()->after('quien_envia_nombre');
            }

            if (! Schema::hasColumn('reparaciones_articulos', 'quien_recibe_nombre')) {
                $table->string('quien_recibe_nombre', 150)->nullable()->after('quien_envia_documento');
            }

            if (! Schema::hasColumn('reparaciones_articulos', 'quien_recibe_documento')) {
                $table->string('quien_recibe_documento', 50)->nullable()->after('quien_recibe_nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones_articulos', function (Blueprint $table) {
            $columns = [
                'quien_envia_documento',
                'quien_envia_nombre',
                'quien_recibe_documento',
                'quien_recibe_nombre',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('reparaciones_articulos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
