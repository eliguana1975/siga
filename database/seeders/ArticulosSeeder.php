<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SplFileObject;

class ArticulosSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/articulos.csv');

        if (! is_file($path)) {
            throw new RuntimeException('No se encontro el archivo de articulos: ' . $path);
        }

        $unidadMedida = UnidadMedida::firstOrCreate(['nombre' => 'UNIDAD']);

        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(';');

        $insertados = 0;
        $omitidos = 0;
        $reasignados = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::table('articulos')->delete();
            DB::statement('ALTER TABLE articulos AUTO_INCREMENT = 1');

            $usedIds = [];
            $nextId = 1;

            foreach ($file as $index => $row) {
                if ($index === 0 || ! is_array($row) || $this->isEmptyRow($row)) {
                    continue;
                }

                if (count($row) < 10) {
                    $omitidos++;
                    continue;
                }

                $categoriaId = $this->parseCategoriaId((string) $row[1]);

                if ($categoriaId <= 0) {
                    $omitidos++;
                    continue;
                }

                $this->ensureCategoriaExists($categoriaId);

                $nombre = mb_strtoupper(trim((string) $row[2]), 'UTF-8');

                if ($nombre === '') {
                    $omitidos++;
                    continue;
                }

                $codigoArticulo = $this->parseCodigoArticulo((string) $row[0]);

                if ($codigoArticulo <= 0) {
                    $omitidos++;
                    continue;
                }

                $assignedId = $codigoArticulo;

                if (isset($usedIds[$assignedId])) {
                    while (isset($usedIds[$nextId])) {
                        $nextId++;
                    }

                    $assignedId = $nextId;
                    $reasignados++;
                }

                $usedIds[$assignedId] = true;
                $nextId = max($nextId, $assignedId + 1);

                DB::table('articulos')->insert([
                    'id' => $assignedId,
                    'categoria_id' => $categoriaId,
                    'unidad_medida_id' => $unidadMedida->id,
                    'nombre' => $nombre,
                    'codigo_producto' => (string) $assignedId,
                    'stock_minimo' => $this->parseInteger($row[3]),
                    'stock_maximo' => $this->parseInteger($row[4]),
                    'stock_pedido' => $this->parseInteger($row[5]),
                    'pasillo' => $this->nullableString($row[6]),
                    'estanteria' => $this->nullableString($row[7]),
                    'casillero' => $this->nullableString($row[8]),
                    'estado_item' => $this->parseEstado($row[9]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $insertados++;
            }

            $nextId = ((int) DB::table('articulos')->max('id')) + 1;
            DB::statement('ALTER TABLE articulos AUTO_INCREMENT = ' . max(1, $nextId));
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->command?->info("Articulos sembrados. Insertados: {$insertados}. Reasignados por duplicado: {$reasignados}. Omitidos: {$omitidos}.");
    }

    private function parseCodigoArticulo(string $value): int
    {
        $value = trim($value);

        if (preg_match('/\d+/', $value, $matches) !== 1) {
            return 0;
        }

        return (int) ltrim($matches[0], '0') ?: 0;
    }

    private function parseCategoriaId(string $value): int
    {
        $value = trim($value);

        if (preg_match('/\d+/', $value, $matches) !== 1) {
            return 0;
        }

        return (int) ltrim($matches[0], '0') ?: 0;
    }

    private function ensureCategoriaExists(int $categoriaId): void
    {
        if ($categoriaId <= 0) {
            return;
        }

        if (Categoria::query()->where('id', $categoriaId)->exists()) {
            return;
        }

        $nombre = 'CATEGORIA IMPORTADA ' . $categoriaId;
        $base = $nombre;
        $suffix = 1;

        while (Categoria::query()->where('nombre', $nombre)->exists()) {
            $suffix++;
            $nombre = $base . ' (' . $suffix . ')';
        }

        DB::table('categorias')->insert([
            'id' => $categoriaId,
            'nombre' => $nombre,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function parseInteger(mixed $value): int
    {
        $value = trim((string) $value);

        return is_numeric($value) ? (int) $value : 0;
    }

    private function parseEstado(mixed $value): string
    {
        return mb_strtolower(trim((string) $value), 'UTF-8') === 'inactivo'
            ? 'inactivo'
            : 'activo';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isEmptyRow(array $row): bool
    {
        return trim(implode('', array_map(static fn ($value) => (string) $value, $row))) === '';
    }
}
