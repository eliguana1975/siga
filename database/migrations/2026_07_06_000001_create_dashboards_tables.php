<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->default('bi bi-speedometer2');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('dashboard_role', function (Blueprint $table) {
            $table->foreignId('dashboard_id')->constrained('dashboards')->cascadeOnDelete();
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->primary(['dashboard_id', 'role_id']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        $now = now();
        $dashboards = [
            ['key' => 'general', 'name' => 'General', 'description' => 'Resumen completo de flota, taller, alertas y vencimientos.', 'icon' => 'bi bi-speedometer2', 'sort_order' => 10],
            ['key' => 'taller', 'name' => 'Taller', 'description' => 'Ordenes de trabajo, vehiculos parados y servicios pendientes.', 'icon' => 'bi bi-tools', 'sort_order' => 20],
            ['key' => 'compras', 'name' => 'Compras', 'description' => 'Seguimiento de solicitudes, stock critico y reparaciones vencidas.', 'icon' => 'bi bi-cart-check', 'sort_order' => 30],
            ['key' => 'inventario', 'name' => 'Inventario', 'description' => 'Alertas de stock y movimientos que impactan deposito.', 'icon' => 'bi bi-box-seam', 'sort_order' => 40],
            ['key' => 'flota', 'name' => 'Flota', 'description' => 'Estado de vehiculos, tipos, servicios y vencimientos tecnicos.', 'icon' => 'bi bi-car-front-fill', 'sort_order' => 50],
            ['key' => 'operativo', 'name' => 'Operativo', 'description' => 'Alertas, vencimientos y controles para seguimiento diario.', 'icon' => 'bi bi-clipboard2-pulse', 'sort_order' => 60],
        ];

        DB::table('dashboards')->insert(array_map(fn (array $dashboard) => $dashboard + [
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $dashboards));

        $adminRoleIds = DB::table('roles')
            ->whereIn(DB::raw('UPPER(name)'), ['ADMIN', 'ADMINISTRADOR', 'SUPERUSUARIO', 'SUPER USUARIO', 'SUPERUSER', 'SUPER USER'])
            ->pluck('id');

        if ($adminRoleIds->isNotEmpty()) {
            $dashboardIds = DB::table('dashboards')->pluck('id');
            $rows = [];

            foreach ($dashboardIds as $dashboardId) {
                foreach ($adminRoleIds as $roleId) {
                    $rows[] = [
                        'dashboard_id' => $dashboardId,
                        'role_id' => $roleId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::table('dashboard_role')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_role');
        Schema::dropIfExists('dashboards');
    }
};
