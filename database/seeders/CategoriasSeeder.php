<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        Categoria::where('nombre', 'LIBRERIA')->update(['nombre' => 'LIBRERÍA']);

        $categorias = [
            'ELECTRICIDAD',
            'BULONERIA/FERRETERIA',
            'MECANICA',
            'FRENOS',
            'LUBRICANTES',
            'CORREAS',
            'ELASTICOS',
            'FILTRO',
            'CHAPA/PINTURA',
            'GOMERIA',
            'CARROCERIA',
            'VALVULAS',
            'A/A /CALEFACCION',
            'INSUMOS LIMPIEZA',
            'HERRAMIENTAS',
            'ELEMENTOS DE SEGURIDAD',
            'LIBRERÍA',
            'ROPA/EPP',
        ];

        foreach ($categorias as $nombre) {
            Categoria::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
