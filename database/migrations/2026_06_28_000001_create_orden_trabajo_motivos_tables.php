<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orden_trabajo_motivos')) {
            Schema::create('orden_trabajo_motivos', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 120);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('orden_trabajo_motivo')) {
            Schema::create('orden_trabajo_motivo', function (Blueprint $table) {
                $table->id();
                $table->foreignId('orden_trabajo_id')->constrained('ordenes_trabajo')->cascadeOnDelete();
                $table->foreignId('orden_trabajo_motivo_id')->constrained('orden_trabajo_motivos')->restrictOnDelete();
                $table->timestamps();

                $table->unique(['orden_trabajo_id', 'orden_trabajo_motivo_id'], 'ot_motivo_unique');
            });
        }

        $now = now();
        $motivos = [
            ['codigo' => 'documentacion', 'nombre' => 'Documentacion'],
            ['codigo' => 'mecanica', 'nombre' => 'Mecanica'],
            ['codigo' => 'electricidad', 'nombre' => 'Electricidad'],
            ['codigo' => 'cubiertas', 'nombre' => 'Cubiertas'],
            ['codigo' => 'carroceria', 'nombre' => 'Carroceria'],
            ['codigo' => 'accesorios', 'nombre' => 'Accesorios'],
            ['codigo' => 'service', 'nombre' => 'Service'],
            ['codigo' => 'otro', 'nombre' => 'Otro'],
        ];

        foreach ($motivos as $motivo) {
            $exists = DB::table('orden_trabajo_motivos')
                ->where('codigo', $motivo['codigo'])
                ->exists();

            if ($exists) {
                DB::table('orden_trabajo_motivos')
                    ->where('codigo', $motivo['codigo'])
                    ->update([
                        'nombre' => $motivo['nombre'],
                        'activo' => true,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('orden_trabajo_motivos')->insert([
                'codigo' => $motivo['codigo'],
                'nombre' => $motivo['nombre'],
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $cubiertasMotivoId = DB::table('orden_trabajo_motivos')
            ->where('codigo', 'cubiertas')
            ->value('id');

        if ($cubiertasMotivoId) {
            DB::table('ordenes_trabajo')
                ->where(function ($query) {
                    $query->where('titulo', 'like', '%cubierta%')
                        ->orWhere('descripcion', 'like', '%cubierta%')
                        ->orWhere('observaciones', 'like', '%cubierta%')
                        ->orWhere('titulo', 'like', '%neumatic%')
                        ->orWhere('descripcion', 'like', '%neumatic%')
                        ->orWhere('observaciones', 'like', '%neumatic%');
                })
                ->orderBy('id')
                ->select('id')
                ->chunkById(100, function ($ordenes) use ($cubiertasMotivoId, $now) {
                    $rows = $ordenes->map(fn ($orden) => [
                        'orden_trabajo_id' => $orden->id,
                        'orden_trabajo_motivo_id' => $cubiertasMotivoId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('orden_trabajo_motivo')->insertOrIgnore($rows);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_trabajo_motivo');
        Schema::dropIfExists('orden_trabajo_motivos');
    }
};
