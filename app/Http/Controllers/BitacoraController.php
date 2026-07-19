<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BitacoraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorizeAudit($request);

        $search = trim((string) $request->input('search', ''));
        $accion = trim((string) $request->input('accion', ''));
        $modulo = trim((string) $request->input('modulo', ''));
        $usuarioId = $request->input('user_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $query = $this->filteredQuery($request)
            ->with('usuario')
            ->latest()
            ->latest('id');

        $summaryQuery = $this->filteredQuery($request);
        $totales = [
            'registros' => (clone $summaryQuery)->count(),
            'usuarios' => (clone $summaryQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'modulos' => (clone $summaryQuery)->whereNotNull('modulo')->distinct('modulo')->count('modulo'),
        ];
        $porAccion = (clone $summaryQuery)
            ->selectRaw('accion, COUNT(*) as total')
            ->groupBy('accion')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'accion');
        $porModulo = (clone $summaryQuery)
            ->selectRaw("COALESCE(modulo, 'sin modulo') as modulo_nombre, COUNT(*) as total")
            ->groupBy('modulo_nombre')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'modulo_nombre');
        $porUsuario = (clone $summaryQuery)
            ->selectRaw("COALESCE(usuario_nombre, 'Sistema') as usuario_nombre, COUNT(*) as total")
            ->groupBy('usuario_nombre')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'usuario_nombre');

        $bitacoras = $query->paginate(20)->withQueryString();
        $acciones = Bitacora::query()->select('accion')->distinct()->orderBy('accion')->pluck('accion');
        $modulos = Bitacora::query()->whereNotNull('modulo')->select('modulo')->distinct()->orderBy('modulo')->pluck('modulo');
        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.bitacoras.index', compact(
            'bitacoras',
            'acciones',
            'modulos',
            'usuarios',
            'search',
            'accion',
            'modulo',
            'usuarioId',
            'fechaDesde',
            'fechaHasta',
            'totales',
            'porAccion',
            'porModulo',
            'porUsuario'
        ));
    }

    public function export(Request $request)
    {
        $this->authorizeAudit($request);

        $bitacoras = $this->filteredQuery($request)
            ->with('usuario:id,name')
            ->latest()
            ->latest('id')
            ->limit(5000)
            ->get();
        $filename = 'bitacora-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return Response::stream(function () use ($bitacoras) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'fecha',
                'usuario',
                'accion',
                'modulo',
                'descripcion',
                'entidad',
                'datos_anteriores',
                'datos_nuevos',
                'ip',
                'ruta',
            ], ';');

            foreach ($bitacoras as $bitacora) {
                fputcsv($output, [
                    $bitacora->created_at?->format('Y-m-d H:i:s'),
                    $bitacora->usuario?->name ?? $bitacora->usuario_nombre ?? 'Sistema',
                    $bitacora->accion,
                    $bitacora->modulo,
                    $bitacora->descripcion,
                    $bitacora->entidad_type ? class_basename($bitacora->entidad_type) . ' #' . $bitacora->entidad_id : '',
                    json_encode($bitacora->datos_anteriores, JSON_UNESCAPED_UNICODE),
                    json_encode($bitacora->datos_nuevos, JSON_UNESCAPED_UNICODE),
                    $bitacora->ip_address,
                    trim(($bitacora->method ? $bitacora->method . ' ' : '') . ($bitacora->route_name ?? $bitacora->url ?? '')),
                ], ';');
            }

            fclose($output);
        }, 200, $headers);
    }

    private function filteredQuery(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $accion = trim((string) $request->input('accion', ''));
        $modulo = trim((string) $request->input('modulo', ''));
        $usuarioId = $request->input('user_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $query = Bitacora::query();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('descripcion', 'like', '%' . $search . '%')
                    ->orWhere('modulo', 'like', '%' . $search . '%')
                    ->orWhere('accion', 'like', '%' . $search . '%')
                    ->orWhere('usuario_nombre', 'like', '%' . $search . '%')
                    ->orWhere('entidad_type', 'like', '%' . $search . '%')
                    ->orWhere('entidad_id', $search);
            });
        }

        if ($accion !== '') {
            $query->where('accion', $accion);
        }

        if ($modulo !== '') {
            $query->where('modulo', $modulo);
        }

        if ($usuarioId) {
            $query->where('user_id', $usuarioId);
        }

        if ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        return $query;
    }

    private function authorizeAudit(Request $request): void
    {
        $user = $request->user();

        abort_unless($user && ($user->isSuperUsuario() || $user->can('bitacoras.ver')), 403);
    }
}
