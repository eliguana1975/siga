<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferenciaDeposito extends Model
{
    protected $table = 'transferencias_deposito';

    protected $fillable = [
        'deposito_origen_id',
        'deposito_destino_id',
        'usuario_id',
        'fecha_transferencia',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_transferencia' => 'datetime',
    ];

    public function depositoOrigen(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'deposito_origen_id');
    }

    public function depositoDestino(): BelongsTo
    {
        return $this->belongsTo(Deposito::class, 'deposito_destino_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TransferenciaDepositoDetalle::class, 'transferencia_id');
    }
}
