<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Banco;
use App\Models\Provincia;
use App\Models\Ciudad;
use App\Support\CompanyTitularSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjusteController extends Controller
{
    public function index()
    {
        $ajuste = Ajuste::query()->first();
        $provincias = Provincia::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get();
        $ciudades = Ciudad::query()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')
            ->get();
        $bancos = Banco::query()
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->get();

        $tablasBackup = $this->getBackupTables();

        return view('admin.ajustes.index', compact('provincias', 'ciudades', 'ajuste', 'bancos', 'tablasBackup'));
    }

    public function create()
    {
        return redirect()->route('admin.ajustes.index');
    }

    public function store(Request $request)
    {
        $request->merge([
            'impuestos' => $this->normalizeImpuestoPercentages($request->input('impuestos', [])),
        ]);

        $request->validate([
            'provincia_id' => 'required|exists:provincias,id',
            'ciudad_id' => 'required|exists:ciudades,id',
            'nombre' => 'required|string',
            'cuit' => 'nullable|string|max:30',
            'descripcion' => 'nullable|string',
            'direccion' => 'nullable|string',
            'codigo_postal' => 'nullable|string|max:20',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'divisa' => 'required|string|max:10',
            'logo' => 'nullable|image|max:2048',
            'imagen_login' => 'nullable|image|max:4096',
            'web' => 'nullable|string',
            'pedidos_automaticos_activos' => 'nullable|boolean',
            'impuestos' => 'nullable|array',
            'impuestos.*.nombre' => 'nullable|string|max:120',
            'impuestos.*.porcentaje' => 'nullable|numeric|min:0|max:100',
            'impuestos.*.descripcion' => 'nullable|string|max:255',
            'impuestos.*.activo' => 'nullable|boolean',
        ]);
        // Si ya existe un ajuste, lo actualizamos; si no, creamos uno nuevo
        $ajuste = Ajuste::first() ?? new Ajuste();

        $ajuste->provincia_id = $request->input('provincia_id');
        $ajuste->ciudad_id = $request->input('ciudad_id');
        $ajuste->nombre = $request->input('nombre');
        $ajuste->cuit = $request->input('cuit');
        $ajuste->descripcion = $request->input('descripcion');
        $ajuste->direccion = $request->input('direccion');
        $ajuste->codigo_postal = $request->input('codigo_postal');
        $ajuste->telefono = $request->input('telefono');
        $ajuste->email = $request->input('email');
        $ajuste->divisa = $request->input('divisa');
        $ajuste->web = $request->input('web');
        $ajuste->pedidos_automaticos_activos = $request->boolean('pedidos_automaticos_activos');
        $ajuste->impuestos = $this->normalizeImpuestos($request->input('impuestos', []));

        // Manejar subida de logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('ajustes', 'public');
            $ajuste->logo = $path;
        }

        if ($request->hasFile('imagen_login')) {
            $file = $request->file('imagen_login');
            $path = $file->store('ajustes', 'public');
            $ajuste->imagen_login = $path;
        }

        $ajuste->save();
        app(CompanyTitularSync::class)->sync($ajuste);

        return redirect()->route('admin.ajustes.index')->with('success', 'Ajuste guardado correctamente.');
    }

    public function backup(Request $request)
    {
        $request->validate([
            'tablas' => 'required|array|min:1',
            'tablas.*' => 'required|string',
        ], [
            'tablas.required' => 'Debes seleccionar al menos una tabla para el backup.',
            'tablas.min' => 'Debes seleccionar al menos una tabla para el backup.',
        ]);

        $tablasDisponibles = $this->getBackupTables();
        $tablasSeleccionadas = array_values(array_intersect($request->input('tablas', []), $tablasDisponibles));

        if (empty($tablasSeleccionadas)) {
            return redirect()
                ->route('admin.ajustes.index')
                ->withErrors(['tablas' => 'Las tablas seleccionadas no son validas.'])
                ->withInput();
        }

        $sql = $this->buildSqlBackup($tablasSeleccionadas);
        $filename = 'backup-'.now()->format('Ymd-His').'.sql';

        return response()->streamDownload(function () use ($sql) {
            echo $sql;
        }, $filename, [
            'Content-Type' => 'application/sql; charset=UTF-8',
        ]);
    }

    private function getBackupTables(): array
    {
        $rows = DB::select('SHOW TABLES');

        return collect($rows)
            ->map(fn ($row) => (string) array_values((array) $row)[0])
            ->sort()
            ->values()
            ->all();
    }

    private function buildSqlBackup(array $tables): string
    {
        $pdo = DB::connection()->getPdo();
        $database = DB::connection()->getDatabaseName();
        $lines = [
            '-- SIGA backup',
            '-- Database: '.$database,
            '-- Generated at: '.now()->toDateTimeString(),
            '',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        foreach ($tables as $table) {
            $safeTable = str_replace('`', '``', $table);
            $createRows = DB::select('SHOW CREATE TABLE `'.$safeTable.'`');
            $createTableData = ! empty($createRows) ? array_values((array) $createRows[0]) : null;
            $createSql = $createTableData[1] ?? null;

            if (! $createSql) {
                continue;
            }

            $lines[] = '-- --------------------------------------------------------';
            $lines[] = '-- Table: '.$table;
            $lines[] = 'DROP TABLE IF EXISTS `'.$safeTable.'`;';
            $lines[] = $createSql.';';

            $hasRows = false;
            $columnsSql = '';

            foreach (DB::cursor('SELECT * FROM `'.$safeTable.'`') as $row) {
                $rowArray = (array) $row;

                if (! $hasRows) {
                    $columnsSql = collect(array_keys($rowArray))
                        ->map(fn ($column) => '`'.str_replace('`', '``', $column).'`')
                        ->implode(', ');
                    $hasRows = true;
                }

                $valuesSql = collect(array_values($rowArray))
                    ->map(fn ($value) => $this->toSqlValue($value, $pdo))
                    ->implode(', ');

                $lines[] = 'INSERT INTO `'.$safeTable.'` ('.$columnsSql.') VALUES ('.$valuesSql.');';
            }

            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function toSqlValue(mixed $value, \PDO $pdo): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }

    private function normalizeImpuestos(array $impuestos): array
    {
        return collect($impuestos)
            ->map(function (array $impuesto) {
                $nombre = trim((string) ($impuesto['nombre'] ?? ''));

                if ($nombre === '') {
                    return null;
                }

                return [
                    'nombre' => $nombre,
                    'porcentaje' => isset($impuesto['porcentaje']) && $impuesto['porcentaje'] !== ''
                        ? round((float) $impuesto['porcentaje'], 4)
                        : 0,
                    'descripcion' => trim((string) ($impuesto['descripcion'] ?? '')),
                    'activo' => (bool) ($impuesto['activo'] ?? false),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeImpuestoPercentages(mixed $impuestos): array
    {
        if (! is_array($impuestos)) {
            return [];
        }

        return collect($impuestos)
            ->map(function ($impuesto) {
                if (! is_array($impuesto)) {
                    return [];
                }

                if (isset($impuesto['porcentaje'])) {
                    $impuesto['porcentaje'] = str_replace(',', '.', (string) $impuesto['porcentaje']);
                }

                return $impuesto;
            })
            ->all();
    }
}
