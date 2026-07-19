<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orden_trabajo_articulos')) {
            return;
        }

        Schema::table('orden_trabajo_articulos', function (Blueprint $table) {
            if (! Schema::hasColumn('orden_trabajo_articulos', 'numero_movimiento')) {
                $table->string('numero_movimiento', 32)->nullable()->after('inventario_descontado')->index();
            }
        });

        if (Schema::hasColumn('orden_trabajo_articulos', 'numero_movimiento')) {
            DB::table('orden_trabajo_articulos')
                ->whereNull('numero_movimiento')
                ->orderBy('id')
                ->chunkById(200, function ($detalles) {
                    foreach ($detalles as $detalle) {
                        $year = $detalle->created_at
                            ? (string) date('Y', strtotime((string) $detalle->created_at))
                            : (string) date('Y');

                        DB::table('orden_trabajo_articulos')
                            ->where('id', $detalle->id)
                            ->update([
                                'numero_movimiento' => 'MOV-' . $year . '-' . str_pad((string) $detalle->id, 6, '0', STR_PAD_LEFT),
                            ]);
                    }
                });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('orden_trabajo_articulos')) {
            return;
        }

        Schema::table('orden_trabajo_articulos', function (Blueprint $table) {
            if (Schema::hasColumn('orden_trabajo_articulos', 'numero_movimiento')) {
                $table->dropColumn('numero_movimiento');
            }
        });
    }
};
