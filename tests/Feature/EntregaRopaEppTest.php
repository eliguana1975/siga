<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EntregaRopaEppTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_rejects_non_ropa_epp_article(): void
    {
        $this->withoutMiddleware([Authenticate::class, CheckPermission::class, ValidateCsrfToken::class]);

        $depositoId = DB::table('depositos')->insertGetId([
            'nombre' => 'Deposito Central',
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empleadoId = DB::table('empleados')->insertGetId([
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
            'tipo_doc' => 'DNI',
            'numero_doc' => '10000001',
            'estado' => 'activo',
            'deposito_id' => $depositoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoriaId = DB::table('categorias')->insertGetId([
            'nombre' => 'Repuestos',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unidadId = DB::table('unidad_medidas')->insertGetId([
            'nombre' => 'UNIDAD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $articuloId = DB::table('articulos')->insertGetId([
            'categoria_id' => $categoriaId,
            'unidad_medida_id' => $unidadId,
            'nombre' => 'Filtro de aceite',
            'codigo_producto' => 'REP-001',
            'stock_minimo' => 0,
            'stock_maximo' => 100,
            'stock_pedido' => 0,
            'es_herramienta' => 0,
            'es_ropa_epp' => 0,
            'estado_item' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventarios')->insert([
            'deposito_id' => $depositoId,
            'articulo_id' => $articuloId,
            'precio_compra_unidad' => 10,
            'cantidad' => 50,
            'stock_minimo' => 0,
            'stock_maximo' => 100,
            'estado' => 'compra',
            'fecha_registro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['_token' => 'test-token'])->post(route('admin.entregas-ropa-epp.store', [], false), [
            '_token' => 'test-token',
            'empleado_id' => $empleadoId,
            'deposito_id' => $depositoId,
            'fecha_entrega' => now()->toDateString(),
            'detalles' => [
                [
                    'articulo_id' => $articuloId,
                    'cantidad' => 1,
                    'condicion_entrega' => 'nueva',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('entregas_ropa_epp', 0);
    }

    public function test_store_accepts_ropa_epp_article_and_decrements_stock(): void
    {
        $this->withoutMiddleware([Authenticate::class, CheckPermission::class, ValidateCsrfToken::class]);

        $depositoId = DB::table('depositos')->insertGetId([
            'nombre' => 'Deposito EPP',
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empleadoId = DB::table('empleados')->insertGetId([
            'nombres' => 'Ana',
            'apellidos' => 'Lopez',
            'tipo_doc' => 'DNI',
            'numero_doc' => '10000002',
            'estado' => 'activo',
            'deposito_id' => $depositoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoriaId = DB::table('categorias')->insertGetId([
            'nombre' => 'Ropa y EPP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unidadId = DB::table('unidad_medidas')->insertGetId([
            'nombre' => 'PAR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $articuloId = DB::table('articulos')->insertGetId([
            'categoria_id' => $categoriaId,
            'unidad_medida_id' => $unidadId,
            'nombre' => 'Guantes mecanico',
            'codigo_producto' => 'EPP-001',
            'stock_minimo' => 0,
            'stock_maximo' => 100,
            'stock_pedido' => 0,
            'es_herramienta' => 0,
            'es_ropa_epp' => 1,
            'estado_item' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventarios')->insert([
            'deposito_id' => $depositoId,
            'articulo_id' => $articuloId,
            'precio_compra_unidad' => 20,
            'cantidad' => 10,
            'stock_minimo' => 0,
            'stock_maximo' => 100,
            'estado' => 'compra',
            'fecha_registro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['_token' => 'test-token'])->post(route('admin.entregas-ropa-epp.store', [], false), [
            '_token' => 'test-token',
            'empleado_id' => $empleadoId,
            'deposito_id' => $depositoId,
            'fecha_entrega' => now()->toDateString(),
            'detalles' => [
                [
                    'articulo_id' => $articuloId,
                    'cantidad' => 2,
                    'condicion_entrega' => 'nueva',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('entregas_ropa_epp', 1);
        $this->assertDatabaseHas('inventarios', [
            'deposito_id' => $depositoId,
            'articulo_id' => $articuloId,
            'cantidad' => 8,
        ]);
    }
}
