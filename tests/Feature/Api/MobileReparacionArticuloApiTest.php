<?php

namespace Tests\Feature\Api;

use App\Models\ReparacionArticulo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MobileReparacionArticuloApiTest extends TestCase
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
        ReparacionArticulo::query()->where('numero_orden', 'like', 'TEST-MOB-REP-%')->delete();
        User::query()->where('email', 'like', 'mobile-reparacion-%@example.com')->delete();
    }

    public function test_index_requires_permission_and_returns_pending_repairs(): void
    {
        $user = $this->createMobileUser();
        $token = $user->createToken('android-test')->plainTextToken;
        $this->ensureReparacionForTest();

        $this->withToken($token)
            ->getJson(self::API_BASE . '/reparaciones-articulos')
            ->assertForbidden();

        Permission::findOrCreate('reparaciones-articulos.ver', 'web');
        $user->givePermissionTo('reparaciones-articulos.ver');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->withToken($token)
            ->getJson(self::API_BASE . '/reparaciones-articulos?per_page=5')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);
    }

    public function test_show_and_devolver_reparacion_detalle(): void
    {
        $user = $this->createMobileUser();
        Permission::findOrCreate('reparaciones-articulos.ver', 'web');
        Permission::findOrCreate('reparaciones-articulos.editar', 'web');
        $user->givePermissionTo(['reparaciones-articulos.ver', 'reparaciones-articulos.editar']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $token = $user->createToken('android-test')->plainTextToken;
        $ids = $this->ensureReparacionForTest();

        $this->withToken($token)
            ->getJson(self::API_BASE . '/reparaciones-articulos/' . $ids['reparacion_id'])
            ->assertOk()
            ->assertJsonPath('reparacion.id', $ids['reparacion_id'])
            ->assertJsonPath('reparacion.cantidad_pendiente_total', 3)
            ->assertJsonPath('reparacion.detalles.0.cantidad_pendiente', 3);

        $this->withToken($token)
            ->postJson(self::API_BASE . '/reparaciones-articulos/' . $ids['reparacion_id'] . '/detalles/' . $ids['detalle_id'] . '/devolver', [
                'cantidad_devuelta' => 2,
                'fecha_devolucion' => now()->toDateString(),
                'costo_unitario' => 1500.50,
                'observaciones' => 'devuelto desde app movil',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Devolucion registrada correctamente.')
            ->assertJsonPath('reparacion.estado', 'parcial')
            ->assertJsonPath('reparacion.cantidad_pendiente_total', 1)
            ->assertJsonPath('reparacion.detalles.0.cantidad_devuelta', 2)
            ->assertJsonPath('reparacion.detalles.0.cantidad_pendiente', 1);
    }

    public function test_devolver_rejects_quantity_over_pending(): void
    {
        $user = $this->createMobileUser();
        Permission::findOrCreate('reparaciones-articulos.editar', 'web');
        $user->givePermissionTo('reparaciones-articulos.editar');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $token = $user->createToken('android-test')->plainTextToken;
        $ids = $this->ensureReparacionForTest();

        $this->withToken($token)
            ->postJson(self::API_BASE . '/reparaciones-articulos/' . $ids['reparacion_id'] . '/detalles/' . $ids['detalle_id'] . '/devolver', [
                'cantidad_devuelta' => 5,
                'fecha_devolucion' => now()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cantidad_devuelta']);
    }

    private function createMobileUser(): User
    {
        return User::factory()->create([
            'email' => 'mobile-reparacion-' . Str::uuid() . '@example.com',
            'password' => bcrypt('12345678'),
        ]);
    }

    private function ensureReparacionForTest(): array
    {
        $proveedorId = $this->ensureProveedor();
        $articuloId = $this->ensureArticulo();

        $reparacionId = (int) DB::table('reparaciones_articulos')->insertGetId([
            'numero_orden' => 'TEST-MOB-REP-' . random_int(1000, 9999),
            'proveedor_id' => $proveedorId,
            'fecha_envio' => now()->toDateString(),
            'fecha_compromiso' => now()->addDays(7)->toDateString(),
            'estado' => 'enviada',
            'observaciones' => 'TEST MOBILE REPARACION',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detalleId = (int) DB::table('reparacion_articulo_detalles')->insertGetId([
            'reparacion_articulo_id' => $reparacionId,
            'articulo_id' => $articuloId,
            'cantidad_enviada' => 3,
            'cantidad_devuelta' => 0,
            'estado' => 'enviada',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'reparacion_id' => $reparacionId,
            'detalle_id' => $detalleId,
        ];
    }

    private function ensureProveedor(): int
    {
        $existing = DB::table('proveedores')->where('nombre', 'TEST-MOB-REP-PROVEEDOR')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('proveedores')->insertGetId([
            'nombre' => 'TEST-MOB-REP-PROVEEDOR',
            'telefono' => '111111',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureArticulo(): int
    {
        $existing = DB::table('articulos')->where('nombre', 'TEST-MOB-REP-ARTICULO')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('articulos')->insertGetId([
            'categoria_id' => $this->ensureSimpleCatalog('categorias', 'nombre', 'TEST-MOB-REP-CATEGORIA'),
            'unidad_medida_id' => $this->ensureSimpleCatalog('unidad_medidas', 'nombre', 'TEST-MOB-REP-UNIDAD'),
            'nombre' => 'TEST-MOB-REP-ARTICULO',
            'codigo_producto' => 'TMR-001',
            'estado_item' => 'activo',
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
