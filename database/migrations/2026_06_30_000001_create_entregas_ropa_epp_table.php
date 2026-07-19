<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            if (! Schema::hasColumn('articulos', 'es_ropa_epp')) {
                $table->boolean('es_ropa_epp')->default(false)->after('es_herramienta');
            }
        });

        Schema::create('entregas_ropa_epp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('deposito_id')->constrained('depositos')->restrictOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_entrega');
            $table->enum('estado', ['entregada', 'parcial', 'devuelta', 'cancelada'])->default('entregada');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['empleado_id', 'estado']);
            $table->index(['deposito_id', 'fecha_entrega']);
        });

        Schema::create('entrega_ropa_epp_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrega_ropa_epp_id')->constrained('entregas_ropa_epp')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->restrictOnDelete();
            $table->integer('cantidad_entregada');
            $table->integer('cantidad_devuelta')->default(0);
            $table->enum('estado', ['entregada', 'parcial', 'devuelta', 'rota', 'perdida'])->default('entregada');
            $table->string('condicion_entrega', 120)->nullable();
            $table->string('condicion_devolucion', 120)->nullable();
            $table->date('fecha_devolucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['articulo_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_ropa_epp_detalles');
        Schema::dropIfExists('entregas_ropa_epp');

        Schema::table('articulos', function (Blueprint $table) {
            if (Schema::hasColumn('articulos', 'es_ropa_epp')) {
                $table->dropColumn('es_ropa_epp');
            }
        });
    }
};
