<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrdenTrabajoArticulosTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_articulo_rejects_ropa_epp_by_category_even_without_flag(): void
    {
        $this->withoutMiddleware([Authenticate::class, CheckPermission::class, ValidateCsrfToken::class]);

        $setup = $this->createOrdenTrabajoContext();

        $categoriaEppId = DB::table('categorias')->insertGetId([
            'nombre' => 'EPP General',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unidadId = DB::table('unidad_medidas')->insertGetId([
            'nombre' => 'UNIDAD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $articuloId = DB::table('articulos')->insertGetId([
            'categoria_id' => $categoriaEppId,
            'unidad_medida_id' => $unidadId,
            'nombre' => 'Guantes reforzados',
            'codigo_producto' => 'MIX-001',
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
            'deposito_id' => $setup['deposito_id'],
            'articulo_id' => $articuloId,
            'precio_compra_unidad' => 12,
            'cantidad' => 30,
            'stock_minimo' => 0,
            'stock_maximo' => 100,
            'estado' => 'compra',
            'fecha_registro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['_token' => 'test-token'])->post(route('admin.ordenes-trabajo.articulos.store', $setup['orden_id'], false), [
            '_token' => 'test-token',
            'articulo_id' => $articuloId,
            'cantidad' => 1,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('orden_trabajo_articulos', 0);
    }

    private function createOrdenTrabajoContext(): array
    {
        $provinciaId = DB::table('provincias')->insertGetId([
            'nombre' => 'Buenos Aires',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ciudadId = DB::table('ciudades')->insertGetId([
            'nombre' => 'La Plata',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $depositoId = DB::table('depositos')->insertGetId([
            'nombre' => 'Deposito Taller',
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $baseId = DB::table('bases')->insertGetId([
            'deposito_id' => $depositoId,
            'provincia_id' => $provinciaId,
            'ciudad_id' => $ciudadId,
            'nombre' => 'Base Central',
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tipoMotorId = DB::table('tipo_motor')->insertGetId(['nombre' => 'Diesel', 'created_at' => now(), 'updated_at' => now()]);
        $modeloMotorId = DB::table('modelo_motor')->insertGetId(['nombre' => 'M1', 'created_at' => now(), 'updated_at' => now()]);
        $marcaCarroceriaId = DB::table('marca_carroceria')->insertGetId(['nombre' => 'Carroceria X', 'created_at' => now(), 'updated_at' => now()]);
        $titularId = DB::table('titular')->insertGetId(['nombre' => 'Municipio', 'created_at' => now(), 'updated_at' => now()]);
        $tipoVehiculoId = DB::table('tipo_vehiculo')->insertGetId(['nombre' => 'Camion', 'created_at' => now(), 'updated_at' => now()]);
        $ciaSeguroId = DB::table('cia_seguro')->insertGetId(['nombre' => 'Seguro SA', 'created_at' => now(), 'updated_at' => now()]);
        $tipoCajaId = DB::table('tipo_caja')->insertGetId(['nombre' => 'Manual', 'created_at' => now(), 'updated_at' => now()]);
        $modeloCajaId = DB::table('modelo_caja')->insertGetId(['nombre' => 'Caja 1', 'created_at' => now(), 'updated_at' => now()]);
        $marcaVehiculoId = DB::table('marca_vehiculo')->insertGetId(['nombre' => 'Marca X', 'created_at' => now(), 'updated_at' => now()]);

        $flotaId = DB::table('flota')->insertGetId([
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
            'nro_interno' => 'INT-001',
            'dominio' => 'AAA111',
            'estado' => 'activo',
            'tipo_medidor_servicio' => 'km',
            'horometro_actual' => 0,
            'nro_motor' => 'MOTOR-001',
            'nro_chasis' => 'CHASIS-001',
            'cant_aceite_motor' => 10,
            'cant_aceite_caja' => 8,
            'med_cub_delanteras' => '275/80R22.5',
            'med_cub_traseras' => '275/80R22.5',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empleadoId = DB::table('empleados')->insertGetId([
            'nombres' => 'Carlos',
            'apellidos' => 'Gomez',
            'tipo_doc' => 'DNI',
            'numero_doc' => '20000001',
            'estado' => 'activo',
            'deposito_id' => $depositoId,
            'base_id' => $baseId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ordenId = DB::table('ordenes_trabajo')->insertGetId([
            'empleado_id' => $empleadoId,
            'actualizado_por_user_id' => null,
            'reparador_empleado_id' => null,
            'flota_id' => $flotaId,
            'base_id' => $baseId,
            'kilometraje' => 1000,
            'fecha_orden' => now(),
            'tipo_trabajo' => 'correctivo',
            'prioridad' => 'media',
            'estado' => 'pendiente',
            'vehiculo_parado' => 0,
            'titulo' => 'Prueba OT',
            'descripcion' => 'Desc',
            'observaciones' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'orden_id' => $ordenId,
            'deposito_id' => $depositoId,
        ];
    }
}
