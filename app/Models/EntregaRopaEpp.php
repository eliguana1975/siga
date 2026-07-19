<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntregaRopaEpp extends Model
{
    protected $table = 'entregas_ropa_epp';

    protected $fillable = [
        'empleado_id',
        'deposito_id',
        'usuario_id',
        'fecha_entrega',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(EntregaRopaEppDetalle::class, 'entrega_ropa_epp_id');
    }

    public function refreshEstado(): void
    {
        $this->loadMissing('detalles');

        if ($this->detalles->isEmpty()) {
            $this->forceFill(['estado' => 'cancelada'])->save();
            return;
        }

        $todosDevueltos = $this->detalles->every(fn ($detalle) => $detalle->cantidadPendiente() <= 0);
        $algunaDevolucion = $this->detalles->contains(fn ($detalle) => (int) $detalle->cantidad_devuelta > 0);

        $this->forceFill([
            'estado' => $todosDevueltos ? 'devuelta' : ($algunaDevolucion ? 'parcial' : 'entregada'),
        ])->save();
    }
}

