<?php

namespace Tests\Feature\Api;

use App\Models\SolicitudRepuesto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MobileSolicitudRepuestoApiTest extends TestCase
{
    private const API_BASE = 'http://localhost/api/v1';

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') === 'sqlite') {
            config([
                'database.connections.mysql.database' => env('SIGA_TEST_DB', 'sigas'),
                'database.default' => 'mysql',
            ]);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        DB::table('personal_access_tokens')->delete();
        SolicitudRepuesto::query()->where('descripcion_repuesto', 'like', 'TEST MOBILE REPUESTO%')->delete();
        User::query()->where('email', 'like', 'mobile-solicitud-%@example.com')->delete();
    }

    public function test_catalogs_and_index_require_permission(): void
    {
        $user = $this->createMobileUser();
        $token = $user->createToken('android-test')->plainTextToken;

        $this->withToken($token)
            ->getJson(self::API_BASE . '/solicitudes-repuestos/catalogos')
            ->assertForbidden();

        $this->withToken($token)
            ->getJson(self::API_BASE . '/solicitudes-repuestos')
            ->assertForbidden();

        Permission::findOrCreate('solicitudes-repuestos.ver', 'web');
        $user->givePermissionTo('solicitudes-repuestos.ver');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->withToken($token)
            ->getJson(self::API_BASE . '/solicitudes-repuestos/catalogos')
            ->assertOk()
            ->assertJsonStructure(['estados', 'prioridades', 'flotas', 'ordenes_trabajo']);

        $this->withToken($token)
            ->getJson(self::API_BASE . '/solicitudes-repuestos?per_page=5')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);
    }

    public function test_store_and_show_solicitud_repuesto(): void
    {
        $user = $this->createMobileUser();
        Permission::findOrCreate('solicitudes-repuestos.crear', 'web');
        Permission::findOrCreate('solicitudes-repuestos.ver', 'web');
        $user->givePermissionTo(['solicitudes-repuestos.crear', 'solicitudes-repuestos.ver']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $token = $user->createToken('android-test')->plainTextToken;
        $flotaId = $this->ensureFlotaForTest();

        $store = $this->withToken($token)
            ->postJson(self::API_BASE . '/solicitudes-repuestos', [
                'flota_id' => $flotaId,
                'prioridad' => 'urgente',
                'cantidad' => 2,
                'descripcion_repuesto' => 'test mobile repuesto filtro de aire',
                'codigo_repuesto' => 'fa-123',
                'motivo' => 'Pedido desde app movil',
                'observaciones_taller' => 'Unidad en base',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Solicitud de repuesto registrada correctamente.')
            ->assertJsonPath('solicitud.estado', 'pendiente')
            ->assertJsonPath('solicitud.prioridad', 'urgente')
            ->assertJsonPath('solicitud.descripcion_repuesto', 'TEST MOBILE REPUESTO FILTRO DE AIRE');

        $solicitudId = (int) $store->json('solicitud.id');

        $this->withToken($token)
            ->getJson(self::API_BASE . '/solicitudes-repuestos/' . $solicitudId)
            ->assertOk()
            ->assertJsonPath('solicitud.id', $solicitudId)
            ->assertJsonPath('solicitud.estado_label', 'Pendiente')
            ->assertJsonPath('solicitud.solicitante.id', $user->id);
    }

    public function test_store_requires_create_permission(): void
    {
        $user = $this->createMobileUser();
        $token = $user->createToken('android-test')->plainTextToken;

        $this->withToken($token)
            ->postJson(self::API_BASE . '/solicitudes-repuestos', [
                'prioridad' => 'normal',
                'cantidad' => 1,
                'descripcion_repuesto' => 'TEST MOBILE REPUESTO SIN PERMISO',
            ])
            ->assertForbidden()
            ->assertJsonPath('required_permission', 'solicitudes-repuestos.crear');
    }

    private function createMobileUser(): User
    {
        return User::factory()->create([
            'email' => 'mobile-solicitud-' . Str::uuid() . '@example.com',
            'password' => bcrypt('12345678'),
        ]);
    }

    private function ensureFlotaForTest(): int
    {
        $existing = DB::table('flota')->where('nro_interno', 'like', 'TEST-MOB-SOL-%')->orderByDesc('id')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        $tipoMotorId = $this->ensureSimpleCatalog('tipo_motor', 'nombre', 'TEST-MOB-SOL-TIPO-MOTOR');
        $modeloMotorId = $this->ensureSimpleCatalog('modelo_motor', 'nombre', 'TEST-MOB-SOL-MODELO-MOTOR');
        $marcaCarroceriaId = $this->ensureSimpleCatalog('marca_carroceria', 'nombre', 'TEST-MOB-SOL-MARCA-CARROCERIA');
        $titularId = $this->ensureTitular();
        $tipoVehiculoId = $this->ensureSimpleCatalog('tipo_vehiculo', 'nombre', 'TEST-MOB-SOL-TIPO-VEHICULO');
        $ciaSeguroId = $this->ensureSimpleCatalog('cia_seguro', 'nombre', 'TEST-MOB-SOL-CIA-SEGURO');
        $modeloCajaId = $this->ensureSimpleCatalog('modelo_caja', 'nombre', 'TEST-MOB-SOL-MODELO-CAJA');
        $tipoCajaId = $this->ensureSimpleCatalog('tipo_caja', 'nombre', 'TEST-MOB-SOL-TIPO-CAJA');
        $marcaVehiculoId = $this->ensureSimpleCatalog('marca_vehiculo', 'nombre', 'TEST-MOB-SOL-MARCA-VEHICULO');

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
            'nro_interno' => 'TEST-MOB-SOL-' . random_int(1000, 9999),
            'dominio' => 'TS' . random_int(100000, 999999),
            'estado' => 'activo',
            'nro_motor' => 'TSMOTOR' . random_int(100000, 999999),
            'nro_chasis' => 'TSCHASIS' . random_int(100000, 999999),
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
        $existing = DB::table('titular')->where('nombre', 'TEST-MOB-SOL-TITULAR')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('titular')->insertGetId([
            'nombre' => 'TEST-MOB-SOL-TITULAR',
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
}
