<?php

namespace Tests\Feature\Api;

use App\Models\ControlUnidad;
use App\Models\OrdenTrabajo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MobileChecklistApiTest extends TestCase
{
    private const API_BASE = 'http://localhost/api/v1';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);

        if (config('database.default') === 'sqlite') {
            config([
                'database.connections.mysql.database' => env('SIGA_TEST_DB', 'sigas'),
                'database.default' => 'mysql',
            ]);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        DB::table('personal_access_tokens')->delete();
        ControlUnidad::query()->where('interno', 'like', 'TEST-MOB-%')->delete();
        OrdenTrabajo::query()->where('titulo', 'like', 'Check List Vehicular #%TEST-MOB-%')->delete();
        User::query()->where('email', 'like', 'mobile-checklist-%@example.com')->delete();
    }

    public function test_catalogs_and_index_require_permission(): void
    {
        $user = $this->createMobileUser();
        $token = $user->createToken('android-test')->plainTextToken;

        $this->withToken($token)
            ->getJson(self::API_BASE . '/checklists/catalogos')
            ->assertForbidden();

        $this->withToken($token)
            ->getJson(self::API_BASE . '/checklists')
            ->assertForbidden();

        Permission::findOrCreate('controles-unidad.ver', 'web');
        $user->givePermissionTo('controles-unidad.ver');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->withToken($token)
            ->getJson(self::API_BASE . '/checklists/catalogos')
            ->assertOk()
            ->assertJsonStructure(['partes', 'control_unidad_items', 'flotas', 'servicios_asignados']);

        $this->withToken($token)
            ->getJson(self::API_BASE . '/checklists?per_page=5')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);
    }

    public function test_store_and_show_checklist(): void
    {
        $user = $this->createMobileUser();
        Permission::findOrCreate('controles-unidad.crear', 'web');
        Permission::findOrCreate('controles-unidad.ver', 'web');
        $user->givePermissionTo(['controles-unidad.crear', 'controles-unidad.ver']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $token = $user->createToken('android-test')->plainTextToken;
        $flotaId = $this->ensureFlotaForTest();
        $servicioId = $this->ensureServicioAsignadoForTest();

        $payload = [
            'flota_id' => $flotaId,
            'kilometraje_actual' => 12345,
            'servicio_asignado_id' => $servicioId,
            'observaciones_generales' => 'checklist mobile test',
            'partes' => $this->validPartesPayload('cumple'),
            'control_unidad' => $this->validControlPayload('hecho'),
        ];

        $store = $this->withToken($token)
            ->postJson(self::API_BASE . '/checklists', $payload)
            ->assertCreated()
            ->assertJsonPath('message', 'Check List Vehicular creado correctamente.');

        $controlId = (int) $store->json('control.id');

        $this->withToken($token)
            ->getJson(self::API_BASE . '/checklists/' . $controlId)
            ->assertOk()
            ->assertJsonPath('control.id', $controlId)
            ->assertJsonPath('control.kilometraje_actual', 12345);
    }

    public function test_store_checklist_with_no_cumple_creates_work_order(): void
    {
        $user = $this->createMobileUser();
        Permission::findOrCreate('controles-unidad.crear', 'web');
        Permission::findOrCreate('controles-unidad.ver', 'web');
        $user->givePermissionTo(['controles-unidad.crear', 'controles-unidad.ver']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $token = $user->createToken('android-test')->plainTextToken;
        $flotaId = $this->ensureFlotaForTest();
        $servicioId = $this->ensureServicioAsignadoForTest();
        $baseId = $this->ensureBaseForTest();
        $this->ensureEmpleadoForUser($user, $baseId);

        $partes = $this->validPartesPayload('cumple');
        $partes['mecanica']['frenos_delanteros'] = 'no_cumple';

        $store = $this->withToken($token)
            ->postJson(self::API_BASE . '/checklists', [
                'flota_id' => $flotaId,
                'kilometraje_actual' => 22345,
                'servicio_asignado_id' => $servicioId,
                'observaciones_generales' => 'checklist mobile no cumple test',
                'partes' => $partes,
                'control_unidad' => $this->validControlPayload('hecho'),
            ])
            ->assertCreated()
            ->assertJsonPath('control.orden_trabajo.estado', 'pendiente');

        $controlId = (int) $store->json('control.id');
        $ordenId = (int) $store->json('control.orden_trabajo.id');

        $this->assertGreaterThan(0, $ordenId);
        $this->assertDatabaseHas('controles_unidad', [
            'id' => $controlId,
            'orden_trabajo_id' => $ordenId,
        ]);
        $this->assertDatabaseHas('ordenes_trabajo', [
            'id' => $ordenId,
            'flota_id' => $flotaId,
            'estado' => 'pendiente',
            'tipo_trabajo' => 'inspeccion',
        ]);
    }

    private function createMobileUser(): User
    {
        return User::factory()->create([
            'email' => 'mobile-checklist-' . Str::uuid() . '@example.com',
            'password' => bcrypt('12345678'),
        ]);
    }

    private function validPartesPayload(string $estado): array
    {
        $payload = [];

        foreach (ControlUnidad::PARTES as $parteKey => $parte) {
            foreach ($parte['items'] as $itemKey => $_label) {
                $payload[$parteKey][$itemKey] = $estado;
            }
        }

        return $payload;
    }

    private function validControlPayload(string $estado): array
    {
        $payload = [];

        foreach (ControlUnidad::CONTROL_UNIDAD as $itemKey => $_label) {
            $payload[$itemKey] = $estado;
        }

        return $payload;
    }

    private function ensureServicioAsignadoForTest(): int
    {
        $existing = DB::table('servicios_asignados')->orderBy('id')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('servicios_asignados')->insertGetId([
            'nombre' => 'SERVICIO TEST MOVIL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureFlotaForTest(): int
    {
        $existing = DB::table('flota')->where('nro_interno', 'like', 'TEST-MOB-%')->orderByDesc('id')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        $tipoMotorId = $this->ensureSimpleCatalog('tipo_motor', 'nombre', 'TEST-MOB-TIPO-MOTOR');
        $modeloMotorId = $this->ensureSimpleCatalog('modelo_motor', 'nombre', 'TEST-MOB-MODELO-MOTOR');
        $marcaCarroceriaId = $this->ensureSimpleCatalog('marca_carroceria', 'nombre', 'TEST-MOB-MARCA-CARROCERIA');
        $titularId = $this->ensureTitular();
        $tipoVehiculoId = $this->ensureSimpleCatalog('tipo_vehiculo', 'nombre', 'TEST-MOB-TIPO-VEHICULO');
        $ciaSeguroId = $this->ensureSimpleCatalog('cia_seguro', 'nombre', 'TEST-MOB-CIA-SEGURO');
        $modeloCajaId = $this->ensureSimpleCatalog('modelo_caja', 'nombre', 'TEST-MOB-MODELO-CAJA');
        $tipoCajaId = $this->ensureSimpleCatalog('tipo_caja', 'nombre', 'TEST-MOB-TIPO-CAJA');
        $marcaVehiculoId = $this->ensureSimpleCatalog('marca_vehiculo', 'nombre', 'TEST-MOB-MARCA-VEHICULO');

        return (int) DB::table('flota')->insertGetId([
            'tipo_motor_id' => $tipoMotorId,
            'modelo_motor_id' => $modeloMotorId,
            'cod_marca_carroceria_id' => $marcaCarroceriaId,
            'cod_titular_id' => $titularId,
            'cod_tipo_vehiculo_id' => $tipoVehiculoId,
            'cod_cia_seguro_id' => $ciaSeguroId,
            'modelo_caja_id' => $modeloCajaId,
            'tipo_caja_id' => $tipoCajaId,
            'marca_vehiculo_id' => $marcaVehiculoId,
            'tipo_aceite_motor' => '15W40',
            'tipo_aceite_caja' => '80W90',
            'nro_interno' => 'TEST-MOB-' . random_int(1000, 9999),
            'dominio' => 'TM' . random_int(100000, 999999),
            'estado' => 'activo',
            'nro_motor' => 'TMOTOR' . random_int(100000, 999999),
            'nro_chasis' => 'TCHASIS' . random_int(100000, 999999),
            'cant_aceite_motor' => 10,
            'cant_aceite_caja' => 8,
            'med_cub_delanteras' => '295/80R22.5',
            'med_cub_traseras' => '295/80R22.5',
            'estado_seguro' => 'Activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureTitular(): int
    {
        $existing = DB::table('titular')->where('nombre', 'TEST-MOB-TITULAR')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('titular')->insertGetId([
            'nombre' => 'TEST-MOB-TITULAR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureSimpleCatalog(string $table, string $field, string $value): int
    {
        $existing = DB::table($table)->where($field, $value)->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table($table)->insertGetId([
            $field => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureBaseForTest(): int
    {
        $existing = DB::table('bases')->where('estado', 'activa')->orderBy('id')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('bases')->insertGetId([
            'deposito_id' => $this->ensureDeposito(),
            'provincia_id' => $this->ensureSimpleCatalog('provincias', 'nombre', 'TEST-MOB-PROVINCIA'),
            'ciudad_id' => $this->ensureSimpleCatalog('ciudades', 'nombre', 'TEST-MOB-CIUDAD'),
            'nombre' => 'TEST-MOB-BASE',
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureDeposito(): int
    {
        $existing = DB::table('depositos')->where('nombre', 'TEST-MOB-DEPOSITO')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('depositos')->insertGetId([
            'nombre' => 'TEST-MOB-DEPOSITO',
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureEmpleadoForUser(User $user, int $baseId): int
    {
        $existing = DB::table('empleados')->where('usuario_id', $user->id)->where('estado', 'activo')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('empleados')->insertGetId([
            'usuario_id' => $user->id,
            'base_id' => $baseId,
            'nombres' => 'Mobile',
            'apellidos' => 'Checklist',
            'tipo_doc' => 'DNI',
            'numero_doc' => 'MOB' . random_int(100000, 999999),
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
