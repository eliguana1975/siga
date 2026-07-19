<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_repuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitante_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('procesado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('flota_id')->nullable()->constrained('flota')->nullOnDelete();
            $table->foreignId('orden_trabajo_id')->nullable()->constrained('ordenes_trabajo')->nullOnDelete();
            $table->foreignId('articulo_id')->nullable()->constrained('articulos')->nullOnDelete();
            $table->foreignId('pedido_articulo_id')->nullable()->constrained('pedidos_articulo')->nullOnDelete();
            $table->foreignId('deposito_id')->nullable()->constrained('depositos')->nullOnDelete();
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->enum('estado', [
                'pendiente',
                'en_revision',
                'aprobado',
                'rechazado',
                'catalogado',
                'pedido_generado',
                'comprado',
                'ingresado',
                'entregado_taller',
                'colocado',
                'cerrado',
            ])->default('pendiente');
            $table->enum('prioridad', ['normal', 'alta', 'urgente'])->default('normal');
            $table->unsignedInteger('cantidad')->default(1);
            $table->string('descripcion_repuesto', 255);
            $table->string('codigo_repuesto', 120)->nullable();
            $table->string('nro_chasis', 80)->nullable();
            $table->string('proveedor_sugerido', 160)->nullable();
            $table->text('motivo')->nullable();
            $table->text('observaciones_taller')->nullable();
            $table->text('observaciones_compras')->nullable();
            $table->string('foto_repuesto_path')->nullable();
            $table->string('foto_contexto_path')->nullable();
            $table->timestamps();

            $table->index(['estado', 'prioridad'], 'sol_rep_estado_prioridad_idx');
            $table->index(['flota_id', 'orden_trabajo_id'], 'sol_rep_flota_ot_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_repuestos');
    }
};
