<?php

use App\Models\Articulo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('articulos')
            ->where(function ($query) {
                $query->whereNull('codigo_producto')
                    ->orWhere('codigo_producto', '');
            })
            ->orderBy('id')
            ->select('id')
            ->chunkById(200, function ($articulos) {
                foreach ($articulos as $articulo) {
                    DB::table('articulos')
                        ->where('id', $articulo->id)
                        ->update([
                            'codigo_producto' => Articulo::barcodeCodeForId((int) $articulo->id),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('articulos')
            ->where('codigo_producto', 'like', 'ART%')
            ->whereRaw('codigo_producto = CONCAT("ART", LPAD(id, 8, "0"))')
            ->update([
                'codigo_producto' => null,
                'updated_at' => now(),
            ]);
    }
};
