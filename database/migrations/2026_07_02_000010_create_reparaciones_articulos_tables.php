<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reparaciones_articulos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden', 40)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->foreignId('provincia_id')->nullable()->constrained('provincias')->nullOnDelete();
            $table->foreignId('ciudad_id')->nullable()->constrained('ciudades')->nullOnDelete();
            $table->string('domicilio', 255)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->date('fecha_envio');
            $table->date('fecha_compromiso')->nullable();
            $table->enum('estado', ['pendiente', 'enviada', 'parcial', 'completada', 'vencida', 'cancelada'])->default('enviada');
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['estado', 'fecha_envio']);
            $table->index(['proveedor_id', 'fecha_compromiso']);
        });

        Schema::create('reparacion_articulo_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reparacion_articulo_id')->constrained('reparaciones_articulos')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->restrictOnDelete();
            $table->integer('cantidad_enviada');
            $table->integer('cantidad_devuelta')->default(0);
            $table->decimal('costo_unitario', 12, 2)->nullable();
            $table->enum('estado', ['enviada', 'devuelta_parcial', 'devuelta_total', 'vencida'])->default('enviada');
            $table->date('fecha_ultima_devolucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['reparacion_articulo_id', 'articulo_id'], 'reparacion_articulo_detalle_unique');
            $table->index(['estado', 'articulo_id']);
        });

        Schema::create('reparacion_articulo_reclamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reparacion_articulo_id')->constrained('reparaciones_articulos')->cascadeOnDelete();
            $table->unsignedBigInteger('reparacion_articulo_detalle_id')->nullable();
            $table->foreign('reparacion_articulo_detalle_id', 'rep_art_reclamo_det_fk')
                ->references('id')
                ->on('reparacion_articulo_detalles')
                ->nullOnDelete();
            $table->date('fecha_reclamo');
            $table->enum('medio', ['telefono', 'email', 'whatsapp', 'presencial', 'otro'])->default('telefono');
            $table->string('numero_referencia', 120)->nullable();
            $table->text('observaciones')->nullable();
            $table->text('respuesta_proveedor')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reparacion_articulo_id', 'fecha_reclamo'], 'rep_art_reclamo_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reparacion_articulo_reclamos');
        Schema::dropIfExists('reparacion_articulo_detalles');
        Schema::dropIfExists('reparaciones_articulos');
    }
};
