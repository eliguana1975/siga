<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReparacionArticulo;
use App\Models\ReparacionArticuloDetalle;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MobileReparacionArticuloController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'reparaciones-articulos.ver')) {
            return $forbidden;
        }

        $search = trim((string) $request->input('search', ''));
        $soloPendientes = $request->boolean('pendientes', true);
        $perPage = max(1, min(50, (int) $request->input('per_page', 20)));

        $query = ReparacionArticulo::query()
            ->with([
                'proveedor:id,nombre,telefono,email',
                'usuario:id,name,email',
                'detalles:id,reparacion_articulo_id,articulo_id,descripcion_articulo_manual,codigo_articulo_manual,cantidad_enviada,cantidad_devuelta,costo_unitario,estado,fecha_ultima_devolucion,observaciones',
                'detalles.articulo:id,nombre,codigo_producto',
            ])
            ->when($soloPendientes, fn ($query) => $query
                ->whereHas('detalles', fn ($detalles) => $detalles->whereRaw('cantidad_enviada > cantidad_devuelta')))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('numero_orden', 'like', "%{$search}%")
                        ->orWhere('estado', 'like', "%{$search}%")
                        ->orWhereHas('proveedor', fn ($proveedor) => $proveedor->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('detalles', fn ($detalles) => $detalles
                            ->where('descripcion_articulo_manual', 'like', "%{$search}%")
                            ->orWhere('codigo_articulo_manual', 'like', "%{$search}%"))
                        ->orWhereHas('detalles.articulo', fn ($articulo) => $articulo
                            ->where('nombre', 'like', "%{$search}%")
                            ->orWhere('codigo_producto', 'like', "%{$search}%"));
                });
            })
            ->latest('fecha_envio')
            ->latest('id');

        $reparaciones = $query->paginate($perPage)->withQueryString();
        $reparaciones->getCollection()->transform(fn (ReparacionArticulo $reparacion) => $this->payload($reparacion));

        return response()->json($reparaciones);
    }

    public function show(Request $request, ReparacionArticulo $reparacionArticulo): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'reparaciones-articulos.ver')) {
            return $forbidden;
        }

        return response()->json([
            'reparacion' => $this->payload($reparacionArticulo->load($this->relations())),
        ]);
    }

    public function devolver(Request $request, ReparacionArticulo $reparacionArticulo, string $detalle): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'reparaciones-articulos.editar')) {
            return $forbidden;
        }

        $detalle = ReparacionArticuloDetalle::query()
            ->where('reparacion_articulo_id', $reparacionArticulo->id)
            ->findOrFail($detalle);

        $fechaEnvio = $reparacionArticulo->fecha_envio
            ? date('Y-m-d', strtotime((string) $reparacionArticulo->fecha_envio))
            : now()->toDateString();

        $validator = Validator::make($request->all(), [
            'cantidad_devuelta' => ['required', 'integer', 'min:1'],
            'fecha_devolucion' => ['required', 'date', 'after_or_equal:' . $fechaEnvio],
            'costo_unitario' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request, $detalle) {
            if (! $request->filled('cantidad_devuelta')) {
                return;
            }

            if ((int) $request->input('cantidad_devuelta') > $detalle->cantidadPendiente()) {
                $validator->errors()->add('cantidad_devuelta', 'La cantidad devuelta supera lo pendiente de ese articulo.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos invalidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($reparacionArticulo, $detalle, $validated) {
            $detalle->update([
                'cantidad_devuelta' => (int) $detalle->cantidad_devuelta + (int) $validated['cantidad_devuelta'],
                'fecha_ultima_devolucion' => $validated['fecha_devolucion'],
                'costo_unitario' => $validated['costo_unitario'] ?? $detalle->costo_unitario,
                'observaciones' => $this->normalizeUpperNullable($validated['observaciones'] ?? $detalle->observaciones),
            ]);

            $reparacionArticulo->refreshEstado();
        });

        return response()->json([
            'message' => 'Devolucion registrada correctamente.',
            'reparacion' => $this->payload($reparacionArticulo->fresh($this->relations())),
        ]);
    }

    private function relations(): array
    {
        return [
            'proveedor:id,nombre,contacto,email,telefono',
            'provincia:id,nombre',
            'ciudad:id,nombre',
            'usuario:id,name,email',
            'detalles:id,reparacion_articulo_id,articulo_id,descripcion_articulo_manual,codigo_articulo_manual,cantidad_enviada,cantidad_devuelta,costo_unitario,estado,fecha_ultima_devolucion,observaciones,created_at',
            'detalles.articulo:id,nombre,codigo_producto',
        ];
    }

    private function payload(ReparacionArticulo $reparacion): array
    {
        $reparacion->loadMissing($this->relations());

        $payload = $reparacion->toArray();
        $payload['cantidad_pendiente_total'] = $reparacion->cantidadPendienteTotal();
        $payload['detalles'] = $reparacion->detalles
            ->map(function (ReparacionArticuloDetalle $detalle) {
                $data = $detalle->toArray();
                $data['cantidad_pendiente'] = $detalle->cantidadPendiente();
                $data['nombre_articulo'] = $detalle->nombreArticulo();
                $data['codigo_articulo'] = $detalle->codigoArticulo();

                return $data;
            })
            ->values()
            ->all();

        return $payload;
    }

    private function normalizeUpperNullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value, 'UTF-8');
    }

    private function authorizePermission(Request $request, string $permission): ?JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if ($user->isSuperUsuario() || $this->userHasPermission($user, $permission)) {
            return null;
        }

        return response()->json([
            'message' => 'No tiene permisos para esta accion.',
            'required_permission' => $permission,
        ], 403);
    }

    private function userHasPermission(User $user, string $permission): bool
    {
        $direct = DB::table('permissions')
            ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('permissions.name', $permission)
            ->where('model_has_permissions.model_type', User::class)
            ->where('model_has_permissions.model_id', $user->id)
            ->exists();

        if ($direct) {
            return true;
        }

        return DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('permissions.name', $permission)
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.model_id', $user->id)
            ->exists();
    }
}
