<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flota;
use App\Models\OrdenTrabajo;
use App\Models\SolicitudRepuesto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MobileSolicitudRepuestoController extends Controller
{
    public function catalogs(Request $request): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'solicitudes-repuestos.ver')) {
            return $forbidden;
        }

        return response()->json([
            'estados' => SolicitudRepuesto::ESTADOS,
            'prioridades' => SolicitudRepuesto::PRIORIDADES,
            'flotas' => Flota::query()
                ->select('id', 'nro_interno', 'dominio', 'nro_chasis', 'estado')
                ->orderBy('nro_interno')
                ->limit(500)
                ->get(),
            'ordenes_trabajo' => OrdenTrabajo::query()
                ->select('id', 'titulo', 'flota_id', 'estado', 'fecha_orden')
                ->whereNotIn('estado', ['completada', 'cancelada'])
                ->latest('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'solicitudes-repuestos.ver')) {
            return $forbidden;
        }

        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $perPage = max(1, min(50, (int) $request->input('per_page', 20)));

        $query = SolicitudRepuesto::query()
            ->with([
                'solicitante:id,name,email',
                'procesadoPor:id,name,email',
                'flota:id,nro_interno,dominio,nro_chasis,estado',
                'ordenTrabajo:id,titulo,estado',
                'articulo:id,nombre,codigo_producto',
                'pedidoArticulo:id,estado',
            ])
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('descripcion_repuesto', 'like', "%{$search}%")
                        ->orWhere('codigo_repuesto', 'like', "%{$search}%")
                        ->orWhere('nro_chasis', 'like', "%{$search}%")
                        ->orWhere('proveedor_sugerido', 'like', "%{$search}%")
                        ->orWhereHas('flota', fn ($flota) => $flota->where('nro_interno', 'like', "%{$search}%")
                            ->orWhere('dominio', 'like', "%{$search}%")
                            ->orWhere('nro_chasis', 'like', "%{$search}%"))
                        ->orWhereHas('solicitante', fn ($usuario) => $usuario->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('fecha_solicitud')
            ->latest('id');

        return response()->json($query->paginate($perPage)->withQueryString());
    }

    public function show(Request $request, SolicitudRepuesto $solicitudRepuesto): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'solicitudes-repuestos.ver')) {
            return $forbidden;
        }

        return response()->json([
            'solicitud' => $this->payload($solicitudRepuesto->load([
                'solicitante:id,name,email',
                'procesadoPor:id,name,email',
                'flota:id,nro_interno,dominio,nro_chasis,estado',
                'ordenTrabajo:id,titulo,estado,fecha_orden',
                'articulo:id,nombre,codigo_producto',
                'pedidoArticulo:id,estado',
                'deposito:id,nombre',
            ])),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($forbidden = $this->authorizePermission($request, 'solicitudes-repuestos.crear')) {
            return $forbidden;
        }

        /** @var User $user */
        $user = $request->user();
        $validator = Validator::make($request->all(), $this->rules());
        $this->validateFlotaAndOrden($validator, $request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos invalidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        foreach (['descripcion_repuesto', 'codigo_repuesto', 'nro_chasis'] as $field) {
            if (! empty($validated[$field])) {
                $validated[$field] = mb_strtoupper(trim((string) $validated[$field]), 'UTF-8');
            }
        }

        foreach (['foto_repuesto_path' => 'foto_repuesto', 'foto_contexto_path' => 'foto_contexto'] as $target => $field) {
            if ($request->hasFile($field)) {
                $validated[$target] = Storage::disk('public')->putFile('solicitudes-repuestos', $request->file($field));
            }
        }

        unset($validated['foto_repuesto'], $validated['foto_contexto']);

        $solicitud = SolicitudRepuesto::query()->create($validated + [
            'solicitante_user_id' => $user->id,
            'fecha_solicitud' => now(),
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'message' => 'Solicitud de repuesto registrada correctamente.',
            'solicitud' => $this->payload($solicitud->load([
                'solicitante:id,name,email',
                'flota:id,nro_interno,dominio,nro_chasis,estado',
                'ordenTrabajo:id,titulo,estado,fecha_orden',
            ])),
        ], 201);
    }

    private function rules(): array
    {
        return [
            'flota_id' => ['nullable', 'integer', 'exists:flota,id'],
            'orden_trabajo_id' => ['nullable', 'integer', 'exists:ordenes_trabajo,id'],
            'prioridad' => ['required', Rule::in(array_keys(SolicitudRepuesto::PRIORIDADES))],
            'cantidad' => ['required', 'integer', 'min:1'],
            'descripcion_repuesto' => ['required', 'string', 'max:255'],
            'codigo_repuesto' => ['nullable', 'string', 'max:120'],
            'nro_chasis' => ['nullable', 'string', 'max:80'],
            'proveedor_sugerido' => ['nullable', 'string', 'max:160'],
            'motivo' => ['nullable', 'string'],
            'observaciones_taller' => ['nullable', 'string'],
            'foto_repuesto' => ['nullable', 'image', 'max:4096'],
            'foto_contexto' => ['nullable', 'image', 'max:4096'],
        ];
    }

    private function validateFlotaAndOrden($validator, Request $request): void
    {
        $validator->after(function ($validator) use ($request) {
            if (! $request->filled('flota_id') || ! $request->filled('orden_trabajo_id')) {
                return;
            }

            $ordenFlotaId = OrdenTrabajo::query()
                ->whereKey((int) $request->input('orden_trabajo_id'))
                ->value('flota_id');

            if ($ordenFlotaId && (int) $ordenFlotaId !== (int) $request->input('flota_id')) {
                $validator->errors()->add('orden_trabajo_id', 'La orden de trabajo no pertenece al vehiculo indicado.');
            }
        });
    }

    private function payload(SolicitudRepuesto $solicitud): array
    {
        return $solicitud->toArray() + [
            'estado_label' => $solicitud->estadoLabel(),
            'prioridad_label' => $solicitud->prioridadLabel(),
            'foto_repuesto_url' => $solicitud->foto_repuesto_path ? Storage::disk('public')->url($solicitud->foto_repuesto_path) : null,
            'foto_contexto_url' => $solicitud->foto_contexto_path ? Storage::disk('public')->url($solicitud->foto_contexto_path) : null,
        ];
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
