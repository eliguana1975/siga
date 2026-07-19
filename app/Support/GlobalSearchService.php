<?php

namespace App\Support;

use App\Models\Articulo;
use App\Models\Compra;
use App\Models\Empleado;
use App\Models\Entrada;
use App\Models\EntregaHerramienta;
use App\Models\EntregaRopaEpp;
use App\Models\Flota;
use App\Models\OrdenTrabajo;
use App\Models\PedidoArticulo;
use App\Models\ReparacionArticulo;
use App\Models\SolicitudRepuesto;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    public function search(User $user, string $term, int $limitPerModule = 8): Collection
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return collect()
            ->merge($this->flotas($user, $term, $limitPerModule))
            ->merge($this->articulos($user, $term, $limitPerModule))
            ->merge($this->ordenesTrabajo($user, $term, $limitPerModule))
            ->merge($this->solicitudesRepuestos($user, $term, $limitPerModule))
            ->merge($this->pedidosArticulos($user, $term, $limitPerModule))
            ->merge($this->ordenesCompra($user, $term, $limitPerModule))
            ->merge($this->entradas($user, $term, $limitPerModule))
            ->merge($this->reparacionesArticulos($user, $term, $limitPerModule))
            ->merge($this->entregasHerramientas($user, $term, $limitPerModule))
            ->merge($this->entregasRopaEpp($user, $term, $limitPerModule))
            ->merge($this->empleados($user, $term, $limitPerModule))
            ->values();
    }

    private function flotas(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'flota.ver')) {
            return collect();
        }

        return Flota::query()
            ->where(function (Builder $query) use ($term) {
                $query->where('nro_interno', 'like', "%{$term}%")
                    ->orWhere('dominio', 'like', "%{$term}%")
                    ->orWhere('nro_motor', 'like', "%{$term}%")
                    ->orWhere('nro_chasis', 'like', "%{$term}%");
            })
            ->limit($limit)
            ->get(['id', 'nro_interno', 'dominio', 'estado'])
            ->map(fn (Flota $flota) => $this->result(
                'Flota',
                "Unidad {$flota->nro_interno}",
                trim(($flota->dominio ? "Dominio {$flota->dominio}" : '') . ' ' . ($flota->estado ? "- {$flota->estado}" : '')),
                route('admin.flota.edit', $flota->id),
                'bi-truck'
            ));
    }

    private function articulos(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'articulos.ver')) {
            return collect();
        }

        return Articulo::query()
            ->where(function (Builder $query) use ($term) {
                $query->where('nombre', 'like', "%{$term}%")
                    ->orWhere('codigo_producto', 'like', "%{$term}%")
                    ->orWhere('observaciones', 'like', "%{$term}%");
            })
            ->limit($limit)
            ->get(['id', 'nombre', 'codigo_producto', 'estado_item'])
            ->map(fn (Articulo $articulo) => $this->result(
                'Articulos',
                $articulo->nombre,
                trim(($articulo->codigo_producto ?: 'Sin codigo') . ($articulo->estado_item ? " - {$articulo->estado_item}" : '')),
                route('admin.articulos.show', $articulo->id),
                'bi-box-seam'
            ));
    }

    private function ordenesTrabajo(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'ordenes-trabajo.ver')) {
            return collect();
        }

        return OrdenTrabajo::query()
            ->with('flota:id,nro_interno,dominio')
            ->where(function (Builder $query) use ($term) {
                $query->where('id', $this->numericOrZero($term))
                    ->orWhere('titulo', 'like', "%{$term}%")
                    ->orWhere('descripcion', 'like', "%{$term}%")
                    ->orWhere('estado', 'like', "%{$term}%")
                    ->orWhereHas('flota', fn (Builder $flota) => $flota
                        ->where('nro_interno', 'like', "%{$term}%")
                        ->orWhere('dominio', 'like', "%{$term}%"));
            })
            ->limit($limit)
            ->get(['id', 'flota_id', 'titulo', 'estado', 'fecha_orden'])
            ->map(fn (OrdenTrabajo $orden) => $this->result(
                'Ordenes de trabajo',
                'OT #' . $orden->id . ($orden->titulo ? " - {$orden->titulo}" : ''),
                trim(($orden->flota ? "Unidad {$orden->flota->nro_interno} {$orden->flota->dominio}" : '') . ($orden->estado ? " - {$orden->estado}" : '')),
                route('admin.ordenes-trabajo.articulos', $orden->id),
                'bi-clipboard-check'
            ));
    }

    private function solicitudesRepuestos(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'solicitudes-repuestos.ver')) {
            return collect();
        }

        return SolicitudRepuesto::query()
            ->with('flota:id,nro_interno,dominio')
            ->where(function (Builder $query) use ($term) {
                $query->where('id', $this->numericOrZero($term))
                    ->orWhere('descripcion_repuesto', 'like', "%{$term}%")
                    ->orWhere('codigo_repuesto', 'like', "%{$term}%")
                    ->orWhere('nro_chasis', 'like', "%{$term}%")
                    ->orWhere('estado', 'like', "%{$term}%")
                    ->orWhereHas('flota', fn (Builder $flota) => $flota
                        ->where('nro_interno', 'like', "%{$term}%")
                        ->orWhere('dominio', 'like', "%{$term}%"));
            })
            ->limit($limit)
            ->get(['id', 'flota_id', 'descripcion_repuesto', 'codigo_repuesto', 'estado'])
            ->map(fn (SolicitudRepuesto $solicitud) => $this->result(
                'Solicitudes de repuestos',
                'Solicitud #' . $solicitud->id . ' - ' . $solicitud->descripcion_repuesto,
                trim(($solicitud->codigo_repuesto ?: 'Sin codigo') . ($solicitud->flota ? " - Unidad {$solicitud->flota->nro_interno}" : '') . ($solicitud->estado ? " - {$solicitud->estado}" : '')),
                route('admin.solicitudes-repuestos.show', $solicitud->id),
                'bi-tools'
            ));
    }

    private function pedidosArticulos(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'pedidos-articulos.ver')) {
            return collect();
        }

        return PedidoArticulo::query()
            ->with('deposito:id,nombre')
            ->where(function (Builder $query) use ($term) {
                $query->where('id', $this->numericOrZero($term))
                    ->orWhere('estado', 'like', "%{$term}%")
                    ->orWhere('notas', 'like', "%{$term}%")
                    ->orWhereHas('deposito', fn (Builder $deposito) => $deposito->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('detalles.articulo', fn (Builder $articulo) => $articulo
                        ->where('nombre', 'like', "%{$term}%")
                        ->orWhere('codigo_producto', 'like', "%{$term}%"));
            })
            ->limit($limit)
            ->get(['id', 'deposito_id', 'estado', 'fecha_pedido'])
            ->map(fn (PedidoArticulo $pedido) => $this->result(
                'Pedidos de articulos',
                'Pedido #' . $pedido->id,
                trim(($pedido->deposito?->nombre ?: 'Sin deposito') . ($pedido->estado ? " - {$pedido->estado}" : '')),
                route('admin.pedidos-articulos.show', $pedido->id),
                'bi-cart'
            ));
    }

    private function ordenesCompra(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'ordenes-compra.ver')) {
            return collect();
        }

        return Compra::query()
            ->with('proveedor:id,nombre')
            ->where(function (Builder $query) use ($term) {
                $query->where('id', $this->numericOrZero($term))
                    ->orWhere('estado', 'like', "%{$term}%")
                    ->orWhere('comprobante', 'like', "%{$term}%")
                    ->orWhere('notas', 'like', "%{$term}%")
                    ->orWhereHas('proveedor', fn (Builder $proveedor) => $proveedor->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('detalles.articulo', fn (Builder $articulo) => $articulo
                        ->where('nombre', 'like', "%{$term}%")
                        ->orWhere('codigo_producto', 'like', "%{$term}%"));
            })
            ->limit($limit)
            ->get(['id', 'proveedor_id', 'estado', 'total_compra', 'fecha_compra'])
            ->map(fn (Compra $compra) => $this->result(
                'Ordenes de compra',
                'OC #' . $compra->id,
                trim(($compra->proveedor?->nombre ?: 'Sin proveedor') . ($compra->estado ? " - {$compra->estado}" : '')),
                route('admin.ordenes-compra.show', $compra->id),
                'bi-receipt'
            ));
    }

    private function entradas(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'entradas.ver')) {
            return collect();
        }

        return Entrada::query()
            ->with(['proveedor:id,nombre', 'deposito:id,nombre'])
            ->where(function (Builder $query) use ($term) {
                $query->where('id', $this->numericOrZero($term))
                    ->orWhere('nro_orden_compra', 'like', "%{$term}%")
                    ->orWhere('nro_comprobante_proveedor', 'like', "%{$term}%")
                    ->orWhere('observaciones', 'like', "%{$term}%")
                    ->orWhereHas('proveedor', fn (Builder $proveedor) => $proveedor->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('deposito', fn (Builder $deposito) => $deposito->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('detalles.articulo', fn (Builder $articulo) => $articulo
                        ->where('nombre', 'like', "%{$term}%")
                        ->orWhere('codigo_producto', 'like', "%{$term}%"));
            })
            ->limit($limit)
            ->get(['id', 'proveedor_id', 'deposito_id', 'nro_comprobante_proveedor', 'fecha_entrada'])
            ->map(fn (Entrada $entrada) => $this->result(
                'Ingresos',
                'Ingreso #' . $entrada->id,
                trim(($entrada->nro_comprobante_proveedor ?: 'Sin comprobante') . ' - ' . ($entrada->proveedor?->nombre ?: $entrada->deposito?->nombre ?: '')),
                route('admin.entradas.show', $entrada->id),
                'bi-box-arrow-in-down'
            ));
    }

    private function reparacionesArticulos(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'reparaciones-articulos.ver')) {
            return collect();
        }

        return ReparacionArticulo::query()
            ->with('proveedor:id,nombre')
            ->where(function (Builder $query) use ($term) {
                $query->where('id', $this->numericOrZero($term))
                    ->orWhere('numero_orden', 'like', "%{$term}%")
                    ->orWhere('estado', 'like', "%{$term}%")
                    ->orWhere('observaciones', 'like', "%{$term}%")
                    ->orWhereHas('proveedor', fn (Builder $proveedor) => $proveedor->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('detalles.articulo', fn (Builder $articulo) => $articulo
                        ->where('nombre', 'like', "%{$term}%")
                        ->orWhere('codigo_producto', 'like', "%{$term}%"));
            })
            ->limit($limit)
            ->get(['id', 'numero_orden', 'proveedor_id', 'estado'])
            ->map(fn (ReparacionArticulo $reparacion) => $this->result(
                'Reparaciones de articulos',
                $reparacion->numero_orden ?: 'Reparacion #' . $reparacion->id,
                trim(($reparacion->proveedor?->nombre ?: 'Sin proveedor') . ($reparacion->estado ? " - {$reparacion->estado}" : '')),
                route('admin.reparaciones-articulos.show', $reparacion->id),
                'bi-wrench'
            ));
    }

    private function entregasHerramientas(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'entregas-herramientas.ver')) {
            return collect();
        }

        return EntregaHerramienta::query()
            ->with('empleado:id,nombres,apellidos,numero_doc')
            ->where(function (Builder $query) use ($term) {
                $query->where('id', $this->numericOrZero($term))
                    ->orWhere('estado', 'like', "%{$term}%")
                    ->orWhere('observaciones', 'like', "%{$term}%")
                    ->orWhereHas('empleado', fn (Builder $empleado) => $empleado
                        ->where('nombres', 'like', "%{$term}%")
                        ->orWhere('apellidos', 'like', "%{$term}%")
                        ->orWhere('numero_doc', 'like', "%{$term}%"))
                    ->orWhereHas('detalles.articulo', fn (Builder $articulo) => $articulo
                        ->where('nombre', 'like', "%{$term}%")
                        ->orWhere('codigo_producto', 'like', "%{$term}%"));
            })
            ->limit($limit)
            ->get(['id', 'empleado_id', 'estado', 'fecha_entrega'])
            ->map(fn (EntregaHerramienta $entrega) => $this->result(
                'Entrega herramientas',
                'Entrega #' . $entrega->id,
                trim(($entrega->empleado ? "{$entrega->empleado->apellidos} {$entrega->empleado->nombres}" : 'Sin empleado') . ($entrega->estado ? " - {$entrega->estado}" : '')),
                route('admin.entregas-herramientas.show', $entrega->id),
                'bi-hammer'
            ));
    }

    private function entregasRopaEpp(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'entregas-ropa-epp.ver')) {
            return collect();
        }

        return EntregaRopaEpp::query()
            ->with('empleado:id,nombres,apellidos,numero_doc')
            ->where(function (Builder $query) use ($term) {
                $query->where('id', $this->numericOrZero($term))
                    ->orWhere('estado', 'like', "%{$term}%")
                    ->orWhere('observaciones', 'like', "%{$term}%")
                    ->orWhereHas('empleado', fn (Builder $empleado) => $empleado
                        ->where('nombres', 'like', "%{$term}%")
                        ->orWhere('apellidos', 'like', "%{$term}%")
                        ->orWhere('numero_doc', 'like', "%{$term}%"))
                    ->orWhereHas('detalles.articulo', fn (Builder $articulo) => $articulo
                        ->where('nombre', 'like', "%{$term}%")
                        ->orWhere('codigo_producto', 'like', "%{$term}%"));
            })
            ->limit($limit)
            ->get(['id', 'empleado_id', 'estado', 'fecha_entrega'])
            ->map(fn (EntregaRopaEpp $entrega) => $this->result(
                'Entrega ropa y EPP',
                'Entrega #' . $entrega->id,
                trim(($entrega->empleado ? "{$entrega->empleado->apellidos} {$entrega->empleado->nombres}" : 'Sin empleado') . ($entrega->estado ? " - {$entrega->estado}" : '')),
                route('admin.entregas-ropa-epp.show', $entrega->id),
                'bi-shield-check'
            ));
    }

    private function empleados(User $user, string $term, int $limit): Collection
    {
        if (! $this->can($user, 'empleados.ver')) {
            return collect();
        }

        return Empleado::query()
            ->where(function (Builder $query) use ($term) {
                $query->where('nombres', 'like', "%{$term}%")
                    ->orWhere('apellidos', 'like', "%{$term}%")
                    ->orWhere('numero_doc', 'like', "%{$term}%")
                    ->orWhere('telefono', 'like', "%{$term}%")
                    ->orWhere('tipo_empleado', 'like', "%{$term}%");
            })
            ->limit($limit)
            ->get(['id', 'nombres', 'apellidos', 'numero_doc', 'estado'])
            ->map(fn (Empleado $empleado) => $this->result(
                'Empleados',
                trim("{$empleado->apellidos} {$empleado->nombres}"),
                trim(($empleado->numero_doc ?: 'Sin documento') . ($empleado->estado ? " - {$empleado->estado}" : '')),
                route('admin.empleados.index', ['search' => $empleado->numero_doc ?: trim("{$empleado->apellidos} {$empleado->nombres}")]),
                'bi-person-badge'
            ));
    }

    private function can(User $user, string $permission): bool
    {
        return $user->isSuperUsuario() || $user->can($permission);
    }

    private function result(string $module, string $title, string $subtitle, string $url, string $icon): array
    {
        return compact('module', 'title', 'subtitle', 'url', 'icon');
    }

    private function numericOrZero(string $term): int
    {
        return ctype_digit($term) ? (int) $term : 0;
    }
}
