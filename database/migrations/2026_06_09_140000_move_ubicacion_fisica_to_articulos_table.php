<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articulos')) {
            Schema::table('articulos', function (Blueprint $table) {
                if (! Schema::hasColumn('articulos', 'pasillo')) {
                    $table->string('pasillo', 50)->nullable()->after('stock_pedido');
                }

                if (! Schema::hasColumn('articulos', 'estanteria')) {
                    $table->string('estanteria', 50)->nullable()->after('pasillo');
                }

                if (! Schema::hasColumn('articulos', 'casillero')) {
                    $table->string('casillero', 50)->nullable()->after('estanteria');
                }
            });
        }

        if (
            Schema::hasTable('articulos')
            && Schema::hasTable('inventarios')
            && Schema::hasTable('ubicacion_fisicas')
            && Schema::hasColumn('inventarios', 'ubicacion_fisica_id')
        ) {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("
                    UPDATE articulos
                    INNER JOIN inventarios ON inventarios.articulo_id = articulos.id
                    INNER JOIN ubicacion_fisicas ON ubicacion_fisicas.id = inventarios.ubicacion_fisica_id
                    SET
                        articulos.pasillo = COALESCE(articulos.pasillo, ubicacion_fisicas.ubicacion_pasillo),
                        articulos.estanteria = COALESCE(articulos.estanteria, ubicacion_fisicas.ubicacion_estaneria),
                        articulos.casillero = COALESCE(articulos.casillero, ubicacion_fisicas.ubicacion_nivel)
                    WHERE
                        articulos.pasillo IS NULL
                        OR articulos.estanteria IS NULL
                        OR articulos.casillero IS NULL
                ");
            } else {
                DB::table('articulos')
                    ->join('inventarios', 'inventarios.articulo_id', '=', 'articulos.id')
                    ->join('ubicacion_fisicas', 'ubicacion_fisicas.id', '=', 'inventarios.ubicacion_fisica_id')
                    ->where(function ($query) {
                        $query->whereNull('articulos.pasillo')
                            ->orWhereNull('articulos.estanteria')
                            ->orWhereNull('articulos.casillero');
                    })
                    ->select([
                        'articulos.id',
                        'articulos.pasillo',
                        'articulos.estanteria',
                        'articulos.casillero',
                        'ubicacion_fisicas.ubicacion_pasillo',
                        'ubicacion_fisicas.ubicacion_estaneria',
                        'ubicacion_fisicas.ubicacion_nivel',
                    ])
                    ->orderBy('articulos.id')
                    ->chunkById(100, function ($articulos) {
                        foreach ($articulos as $articulo) {
                            DB::table('articulos')
                                ->where('id', $articulo->id)
                                ->update([
                                    'pasillo' => $articulo->pasillo ?? $articulo->ubicacion_pasillo,
                                    'estanteria' => $articulo->estanteria ?? $articulo->ubicacion_estaneria,
                                    'casillero' => $articulo->casillero ?? $articulo->ubicacion_nivel,
                                ]);
                        }
                    }, 'articulos.id', 'id');
            }
        }

        if (Schema::hasTable('inventarios') && Schema::hasColumn('inventarios', 'ubicacion_fisica_id')) {
            Schema::table('inventarios', function (Blueprint $table) {
                $table->dropConstrainedForeignId('ubicacion_fisica_id');
            });
        }

        Schema::dropIfExists('ubicacion_fisicas');
    }

    public function down(): void
    {
        if (! Schema::hasTable('ubicacion_fisicas')) {
            Schema::create('ubicacion_fisicas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
                $table->foreignId('base_id')->nullable()->constrained('bases')->nullOnDelete();
                $table->string('ubicacion_pasillo', 50)->nullable();
                $table->string('ubicacion_estaneria', 50)->nullable();
                $table->string('ubicacion_nivel', 50)->nullable();
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('inventarios') && ! Schema::hasColumn('inventarios', 'ubicacion_fisica_id')) {
            Schema::table('inventarios', function (Blueprint $table) {
                $table->foreignId('ubicacion_fisica_id')->nullable()->after('articulo_id')->constrained('ubicacion_fisicas')->nullOnDelete();
            });
        }

        if (Schema::hasTable('articulos')) {
            Schema::table('articulos', function (Blueprint $table) {
                foreach (['casillero', 'estanteria', 'pasillo'] as $column) {
                    if (Schema::hasColumn('articulos', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
