<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Auth::routes();

Route::get('/password/change', [App\Http\Controllers\PasswordChangeController::class, 'edit'])
    ->name('password.change')
    ->middleware('auth');
Route::put('/password/change', [App\Http\Controllers\PasswordChangeController::class, 'update'])
    ->name('password.change.update')
    ->middleware('auth');

Route::get('/session-user', function () {
    $user = Auth::user();

    $response = response()->json([
        'authenticated' => (bool) $user,
        'user_id' => $user?->id,
        'name' => $user?->name,
    ]);

    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

    return $response;
})->name('session.user');

Route::get('/mobile', App\Http\Controllers\MobileAppController::class)->name('mobile.app');
Route::post('/mobile/auth/login', [App\Http\Controllers\Api\MobileAuthController::class, 'webLogin'])->name('mobile.auth.login');
Route::post('/mobile/auth/logout', [App\Http\Controllers\Api\MobileAuthController::class, 'webLogout'])->name('mobile.auth.logout');
Route::get('/movile', fn () => redirect()->route('mobile.app', [], 301));
Route::get('/movil', fn () => redirect()->route('mobile.app', [], 301));
Route::get('/app-movil', fn () => redirect()->route('mobile.app', [], 301));
Route::get('/home', [App\Http\Controllers\Admin::class, 'index'])->name('home')->middleware('auth');
Route::get('/admin', [App\Http\Controllers\Admin::class, 'index'])->name('admin.index')->middleware('auth');
Route::post('/admin/dashboard/preferences', [App\Http\Controllers\Admin::class, 'updateDashboardPreferences'])->name('admin.dashboard.preferences')->middleware('auth');
Route::get('/admin/dashboards', [App\Http\Controllers\DashboardController::class, 'index'])->name('admin.dashboards.index')->middleware('auth');
Route::put('/admin/dashboards/{dashboard}', [App\Http\Controllers\DashboardController::class, 'update'])->name('admin.dashboards.update')->middleware('auth');
Route::get('/admin/manual-usuario', [App\Http\Controllers\ManualUsuarioController::class, 'index'])->name('admin.manual-usuario.index')->middleware('auth');
Route::get('/admin/bi', [App\Http\Controllers\BiController::class, 'index'])->name('admin.bi.index')->middleware('auth');
Route::get('/admin/bi/flota', [App\Http\Controllers\BiController::class, 'flotaEstadisticas'])->name('admin.bi.flota-estadisticas')->middleware('auth');
Route::get('/admin/bi/costeo-vehiculos.json', [App\Http\Controllers\BiController::class, 'costeoVehiculos'])->name('admin.bi.costeo-vehiculos')->middleware('auth');
Route::get('/admin/bi/stock-critico.json', [App\Http\Controllers\BiController::class, 'stockCritico'])->name('admin.bi.stock-critico')->middleware('auth');
Route::get('/admin/bi/solicitudes-repuestos.json', [App\Http\Controllers\BiController::class, 'solicitudesRepuestos'])->name('admin.bi.solicitudes-repuestos')->middleware('auth');
Route::get('/admin/bi/reparaciones-vencidas.json', [App\Http\Controllers\BiController::class, 'reparacionesVencidas'])->name('admin.bi.reparaciones-vencidas')->middleware('auth');
Route::get('/admin/bi/reposicion-sugerida.json', [App\Http\Controllers\BiController::class, 'reposicionSugerida'])->name('admin.bi.reposicion-sugerida')->middleware('auth');
Route::get('/admin/busqueda', [App\Http\Controllers\GlobalSearchController::class, 'index'])->name('admin.global-search.index')->middleware('auth');
Route::get('/admin/busqueda/sugerencias', [App\Http\Controllers\GlobalSearchController::class, 'suggest'])->name('admin.global-search.suggest')->middleware('auth');
Route::get('/admin/notificaciones-operativas', [App\Http\Controllers\OperationalNotificationController::class, 'index'])->name('admin.notificaciones-operativas.index')->middleware('auth');
Route::post('/admin/notificaciones-operativas/{notificacion}/leer', [App\Http\Controllers\OperationalNotificationController::class, 'read'])->name('admin.notificaciones-operativas.read')->middleware('auth');
Route::post('/admin/notificaciones-operativas/{notificacion}/resolver', [App\Http\Controllers\OperationalNotificationController::class, 'resolve'])->name('admin.notificaciones-operativas.resolve')->middleware('auth');
Route::get('/admin/auditoria-operativa', [App\Http\Controllers\OperationalAuditController::class, 'index'])->name('admin.auditoria-operativa.index')->middleware('auth');
Route::get('/admin/auditoria-operativa.json', [App\Http\Controllers\OperationalAuditController::class, 'json'])->name('admin.auditoria-operativa.json')->middleware('auth');
Route::post('/admin/saved-filters', [App\Http\Controllers\SavedFilterController::class, 'store'])->name('admin.saved-filters.store')->middleware('auth');
Route::delete('/admin/saved-filters/{key}/{id}', [App\Http\Controllers\SavedFilterController::class, 'destroy'])->name('admin.saved-filters.destroy')->middleware('auth');

// Rutas para bitacora
Route::get('/admin/bitacoras', [App\Http\Controllers\BitacoraController::class, 'index'])->name('admin.bitacoras.index')->middleware('auth');
Route::get('/admin/bitacoras/export', [App\Http\Controllers\BitacoraController::class, 'export'])->name('admin.bitacoras.export')->middleware('auth');

// Rutas para documentos adjuntos operativos
Route::post('/admin/documentos-operativos', [App\Http\Controllers\DocumentoOperativoController::class, 'store'])->name('admin.documentos-operativos.store')->middleware('auth');
Route::get('/admin/documentos-operativos/{documento}/download', [App\Http\Controllers\DocumentoOperativoController::class, 'download'])->name('admin.documentos-operativos.download')->middleware('auth');
Route::delete('/admin/documentos-operativos/{documento}', [App\Http\Controllers\DocumentoOperativoController::class, 'destroy'])->name('admin.documentos-operativos.destroy')->middleware('auth');

// Rutas para chat interno
Route::get('/admin/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('admin.chat.index')->middleware('auth');
Route::post('/admin/chat', [App\Http\Controllers\ChatController::class, 'store'])->name('admin.chat.store')->middleware('auth');
Route::get('/admin/chat/unread', [App\Http\Controllers\ChatController::class, 'unread'])->name('admin.chat.unread')->middleware('auth');

// Rutas para ajustes
Route::get('/admin/ajustes', [App\Http\Controllers\AjusteController::class, 'index'])->name('admin.ajustes.index')->middleware('auth');
Route::post('/admin/ajustes/create', [App\Http\Controllers\AjusteController::class, 'store'])->name('admin.ajustes.store')->middleware('auth');
Route::post('/admin/ajustes/backup', [App\Http\Controllers\AjusteController::class, 'backup'])->name('admin.ajustes.backup')->middleware('auth');
Route::post('/admin/ajustes/bancos', [App\Http\Controllers\BancoController::class, 'store'])->name('admin.bancos.store')->middleware('auth');
Route::put('/admin/ajustes/bancos/{id}', [App\Http\Controllers\BancoController::class, 'update'])->name('admin.bancos.update')->middleware('auth');
Route::delete('/admin/ajustes/bancos/{id}', [App\Http\Controllers\BancoController::class, 'destroy'])->name('admin.bancos.destroy')->middleware('auth');
Route::get('/admin/configuracion-intervalos-servicio', [App\Http\Controllers\ConfiguracionIntervaloServicioController::class, 'index'])->name('admin.configuracion-intervalos-servicio.index')->middleware('auth');
Route::post('/admin/configuracion-intervalos-servicio/create', [App\Http\Controllers\ConfiguracionIntervaloServicioController::class, 'store'])->name('admin.configuracion-intervalos-servicio.store')->middleware('auth');
Route::put('/admin/configuracion-intervalos-servicio/{id}', [App\Http\Controllers\ConfiguracionIntervaloServicioController::class, 'update'])->name('admin.configuracion-intervalos-servicio.update')->middleware('auth');
Route::delete('/admin/configuracion-intervalos-servicio/{id}', [App\Http\Controllers\ConfiguracionIntervaloServicioController::class, 'destroy'])->name('admin.configuracion-intervalos-servicio.destroy')->middleware('auth');
Route::get('/admin/configuracion-vencimientos-verificacion', [App\Http\Controllers\ConfiguracionVencimientoVerificacionController::class, 'index'])->name('admin.configuracion-vencimientos-verificacion.index')->middleware('auth');
Route::post('/admin/configuracion-vencimientos-verificacion/create', [App\Http\Controllers\ConfiguracionVencimientoVerificacionController::class, 'store'])->name('admin.configuracion-vencimientos-verificacion.store')->middleware('auth');
Route::put('/admin/configuracion-vencimientos-verificacion/{id}', [App\Http\Controllers\ConfiguracionVencimientoVerificacionController::class, 'update'])->name('admin.configuracion-vencimientos-verificacion.update')->middleware('auth');
Route::delete('/admin/configuracion-vencimientos-verificacion/{id}', [App\Http\Controllers\ConfiguracionVencimientoVerificacionController::class, 'destroy'])->name('admin.configuracion-vencimientos-verificacion.destroy')->middleware('auth');

// Rutas para servicios asignados
Route::get('/admin/servicios-asignados', [App\Http\Controllers\ServicioAsignadoController::class, 'index'])->name('admin.servicios-asignados.index')->middleware('auth');
Route::post('/admin/servicios-asignados/create', [App\Http\Controllers\ServicioAsignadoController::class, 'store'])->name('admin.servicios-asignados.store')->middleware('auth');
Route::put('/admin/servicios-asignados/{id}', [App\Http\Controllers\ServicioAsignadoController::class, 'update'])->name('admin.servicios-asignados.update')->middleware('auth');
Route::delete('/admin/servicios-asignados/{id}', [App\Http\Controllers\ServicioAsignadoController::class, 'destroy'])->name('admin.servicios-asignados.destroy')->middleware('auth');

// Rutas para bases
Route::get('/admin/bases', [App\Http\Controllers\BaseController::class, 'index'])->name('admin.bases.index')->middleware('auth');
Route::post('/admin/bases/create', [App\Http\Controllers\BaseController::class, 'store'])->name('admin.bases.store')->middleware('auth');
Route::put('/admin/bases/{id}', [App\Http\Controllers\BaseController::class, 'update'])->name('admin.bases.update')->middleware('auth');
Route::delete('/admin/bases/{id}', [App\Http\Controllers\BaseController::class, 'destroy'])->name('admin.bases.destroy')->middleware('auth');

// Rutas para roles
Route::get('/admin/roles', [App\Http\Controllers\RoleController::class, 'index'])->name('admin.roles.index')->middleware('auth');
Route::post('/admin/roles/create', [App\Http\Controllers\RoleController::class, 'store'])->name('admin.roles.store')->middleware('auth');
Route::put('/admin/roles/{id}', [App\Http\Controllers\RoleController::class, 'update'])->name('admin.roles.update')->middleware('auth');
Route::delete('/admin/roles/{id}', [App\Http\Controllers\RoleController::class, 'destroy'])->name('admin.roles.destroy')->middleware('auth');

// Rutas para usuarios
Route::get('/admin/users', [App\Http\Controllers\UserController::class, 'index'])->name('admin.users.index')->middleware('auth');
Route::post('/admin/users/create', [App\Http\Controllers\UserController::class, 'store'])->name('admin.users.store')->middleware('auth');
Route::put('/admin/users/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update')->middleware('auth');
Route::delete('/admin/users/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('admin.users.destroy')->middleware('auth');

// Rutas para empleados
Route::get('/admin/empleados', [App\Http\Controllers\EmpleadoController::class, 'index'])->name('admin.empleados.index')->middleware('auth');
Route::post('/admin/empleados/create', [App\Http\Controllers\EmpleadoController::class, 'store'])->name('admin.empleados.store')->middleware('auth');
Route::put('/admin/empleados/{id}', [App\Http\Controllers\EmpleadoController::class, 'update'])->name('admin.empleados.update')->middleware('auth');
Route::delete('/admin/empleados/{id}', [App\Http\Controllers\EmpleadoController::class, 'destroy'])->name('admin.empleados.destroy')->middleware('auth');

// Rutas para cronogramas laborales
Route::get('/admin/cronogramas-laborales', [App\Http\Controllers\CronogramaLaboralController::class, 'index'])->name('admin.cronogramas-laborales.index')->middleware('auth');
Route::get('/admin/cronogramas-laborales/imprimir', [App\Http\Controllers\CronogramaLaboralController::class, 'imprimir'])->name('admin.cronogramas-laborales.imprimir')->middleware('auth');
Route::post('/admin/cronogramas-laborales/patrones', [App\Http\Controllers\CronogramaLaboralController::class, 'storePatron'])->name('admin.cronogramas-laborales.patrones.store')->middleware('auth');
Route::put('/admin/cronogramas-laborales/patrones/{id}', [App\Http\Controllers\CronogramaLaboralController::class, 'updatePatron'])->name('admin.cronogramas-laborales.patrones.update')->middleware('auth');
Route::delete('/admin/cronogramas-laborales/patrones/{id}', [App\Http\Controllers\CronogramaLaboralController::class, 'destroyPatron'])->name('admin.cronogramas-laborales.patrones.destroy')->middleware('auth');
Route::post('/admin/cronogramas-laborales/asignaciones', [App\Http\Controllers\CronogramaLaboralController::class, 'storeAsignacion'])->name('admin.cronogramas-laborales.asignaciones.store')->middleware('auth');
Route::put('/admin/cronogramas-laborales/asignaciones/{id}', [App\Http\Controllers\CronogramaLaboralController::class, 'updateAsignacion'])->name('admin.cronogramas-laborales.asignaciones.update')->middleware('auth');
Route::delete('/admin/cronogramas-laborales/asignaciones/{id}', [App\Http\Controllers\CronogramaLaboralController::class, 'destroyAsignacion'])->name('admin.cronogramas-laborales.asignaciones.destroy')->middleware('auth');
Route::post('/admin/cronogramas-laborales/novedades', [App\Http\Controllers\CronogramaLaboralController::class, 'storeNovedad'])->name('admin.cronogramas-laborales.novedades.store')->middleware('auth');
Route::delete('/admin/cronogramas-laborales/novedades/{id}', [App\Http\Controllers\CronogramaLaboralController::class, 'destroyNovedad'])->name('admin.cronogramas-laborales.novedades.destroy')->middleware('auth');

// Rutas para depositos
Route::get('/admin/depositos', [App\Http\Controllers\DepositoController::class, 'index'])->name('admin.depositos.index')->middleware('auth');
Route::post('/admin/depositos/create', [App\Http\Controllers\DepositoController::class, 'store'])->name('admin.depositos.store')->middleware('auth');
Route::put('/admin/depositos/{id}', [App\Http\Controllers\DepositoController::class, 'update'])->name('admin.depositos.update')->middleware('auth');
Route::delete('/admin/depositos/{id}', [App\Http\Controllers\DepositoController::class, 'destroy'])->name('admin.depositos.destroy')->middleware('auth');

// Rutas para categorias de productos
Route::get('/admin/categorias', [App\Http\Controllers\CategoriaController::class, 'index'])->name('admin.categorias.index')->middleware('auth');
Route::post('/admin/categorias/create', [App\Http\Controllers\CategoriaController::class, 'store'])->name('admin.categorias.store')->middleware('auth');
Route::put('/admin/categorias/{id}', [App\Http\Controllers\CategoriaController::class, 'update'])->name('admin.categorias.update')->middleware('auth');
Route::delete('/admin/categorias/{id}', [App\Http\Controllers\CategoriaController::class, 'destroy'])->name('admin.categorias.destroy')->middleware('auth');

// Rutas para provincias y ciudades
Route::get('/admin/provincias', [App\Http\Controllers\ProvinciaController::class, 'index'])->name('admin.provincias.index')->middleware('auth');
Route::post('/admin/provincias/create', [App\Http\Controllers\ProvinciaController::class, 'store'])->name('admin.provincias.store')->middleware('auth');
Route::put('/admin/provincias/{id}', [App\Http\Controllers\ProvinciaController::class, 'update'])->name('admin.provincias.update')->middleware('auth');
Route::delete('/admin/provincias/{id}', [App\Http\Controllers\ProvinciaController::class, 'destroy'])->name('admin.provincias.destroy')->middleware('auth');

Route::get('/admin/ciudades', [App\Http\Controllers\CiudadController::class, 'index'])->name('admin.ciudades.index')->middleware('auth');
Route::post('/admin/ciudades/create', [App\Http\Controllers\CiudadController::class, 'store'])->name('admin.ciudades.store')->middleware('auth');
Route::put('/admin/ciudades/{id}', [App\Http\Controllers\CiudadController::class, 'update'])->name('admin.ciudades.update')->middleware('auth');
Route::delete('/admin/ciudades/{id}', [App\Http\Controllers\CiudadController::class, 'destroy'])->name('admin.ciudades.destroy')->middleware('auth');

// Rutas para catálogos de flota
// Rutas para proveedores
Route::get('/admin/proveedores', [App\Http\Controllers\ProveedorController::class, 'index'])->name('admin.proveedores.index')->middleware('auth');
Route::post('/admin/proveedores/create', [App\Http\Controllers\ProveedorController::class, 'store'])->name('admin.proveedores.store')->middleware('auth');
Route::put('/admin/proveedores/{id}', [App\Http\Controllers\ProveedorController::class, 'update'])->name('admin.proveedores.update')->middleware('auth');
Route::delete('/admin/proveedores/{id}', [App\Http\Controllers\ProveedorController::class, 'destroy'])->name('admin.proveedores.destroy')->middleware('auth');

Route::get('/admin/tipo-motor', [App\Http\Controllers\TipoMotorController::class, 'index'])->name('admin.tipo-motor.index')->middleware('auth');
Route::post('/admin/tipo-motor/create', [App\Http\Controllers\TipoMotorController::class, 'store'])->name('admin.tipo-motor.store')->middleware('auth');
Route::put('/admin/tipo-motor/{id}', [App\Http\Controllers\TipoMotorController::class, 'update'])->name('admin.tipo-motor.update')->middleware('auth');
Route::delete('/admin/tipo-motor/{id}', [App\Http\Controllers\TipoMotorController::class, 'destroy'])->name('admin.tipo-motor.destroy')->middleware('auth');

Route::get('/admin/modelo-motor', [App\Http\Controllers\ModeloMotorController::class, 'index'])->name('admin.modelo-motor.index')->middleware('auth');
Route::post('/admin/modelo-motor/create', [App\Http\Controllers\ModeloMotorController::class, 'store'])->name('admin.modelo-motor.store')->middleware('auth');
Route::put('/admin/modelo-motor/{id}', [App\Http\Controllers\ModeloMotorController::class, 'update'])->name('admin.modelo-motor.update')->middleware('auth');
Route::delete('/admin/modelo-motor/{id}', [App\Http\Controllers\ModeloMotorController::class, 'destroy'])->name('admin.modelo-motor.destroy')->middleware('auth');
Route::get('/admin/tipo-vehiculo', [App\Http\Controllers\TipoVehiculoController::class, 'index'])->name('admin.tipo-vehiculo.index')->middleware('auth');
Route::post('/admin/tipo-vehiculo/create', [App\Http\Controllers\TipoVehiculoController::class, 'store'])->name('admin.tipo-vehiculo.store')->middleware('auth');
Route::put('/admin/tipo-vehiculo/{id}', [App\Http\Controllers\TipoVehiculoController::class, 'update'])->name('admin.tipo-vehiculo.update')->middleware('auth');
Route::delete('/admin/tipo-vehiculo/{id}', [App\Http\Controllers\TipoVehiculoController::class, 'destroy'])->name('admin.tipo-vehiculo.destroy')->middleware('auth');
Route::get('/admin/titulares', [App\Http\Controllers\TitularController::class, 'index'])->name('admin.titulares.index')->middleware('auth');
Route::post('/admin/titulares/create', [App\Http\Controllers\TitularController::class, 'store'])->name('admin.titulares.store')->middleware('auth');
Route::put('/admin/titulares/{id}', [App\Http\Controllers\TitularController::class, 'update'])->name('admin.titulares.update')->middleware('auth');
Route::delete('/admin/titulares/{id}', [App\Http\Controllers\TitularController::class, 'destroy'])->name('admin.titulares.destroy')->middleware('auth');

Route::get('/admin/tipo-caja', [App\Http\Controllers\TipoCajaController::class, 'index'])->name('admin.tipo-caja.index')->middleware('auth');
Route::post('/admin/tipo-caja/create', [App\Http\Controllers\TipoCajaController::class, 'store'])->name('admin.tipo-caja.store')->middleware('auth');
Route::put('/admin/tipo-caja/{id}', [App\Http\Controllers\TipoCajaController::class, 'update'])->name('admin.tipo-caja.update')->middleware('auth');
Route::delete('/admin/tipo-caja/{id}', [App\Http\Controllers\TipoCajaController::class, 'destroy'])->name('admin.tipo-caja.destroy')->middleware('auth');

Route::get('/admin/modelo-caja', [App\Http\Controllers\ModeloCajaController::class, 'index'])->name('admin.modelo-caja.index')->middleware('auth');
Route::post('/admin/modelo-caja/create', [App\Http\Controllers\ModeloCajaController::class, 'store'])->name('admin.modelo-caja.store')->middleware('auth');
Route::put('/admin/modelo-caja/{id}', [App\Http\Controllers\ModeloCajaController::class, 'update'])->name('admin.modelo-caja.update')->middleware('auth');
Route::delete('/admin/modelo-caja/{id}', [App\Http\Controllers\ModeloCajaController::class, 'destroy'])->name('admin.modelo-caja.destroy')->middleware('auth');

Route::get('/admin/cia-seguro', [App\Http\Controllers\CiaSeguroController::class, 'index'])->name('admin.cia-seguro.index')->middleware('auth');
Route::post('/admin/cia-seguro/create', [App\Http\Controllers\CiaSeguroController::class, 'store'])->name('admin.cia-seguro.store')->middleware('auth');
Route::put('/admin/cia-seguro/{id}', [App\Http\Controllers\CiaSeguroController::class, 'update'])->name('admin.cia-seguro.update')->middleware('auth');
Route::delete('/admin/cia-seguro/{id}', [App\Http\Controllers\CiaSeguroController::class, 'destroy'])->name('admin.cia-seguro.destroy')->middleware('auth');

Route::get('/admin/marca-carroceria', [App\Http\Controllers\MarcaCarroceriaController::class, 'index'])->name('admin.marca-carroceria.index')->middleware('auth');
Route::post('/admin/marca-carroceria/create', [App\Http\Controllers\MarcaCarroceriaController::class, 'store'])->name('admin.marca-carroceria.store')->middleware('auth');
Route::put('/admin/marca-carroceria/{id}', [App\Http\Controllers\MarcaCarroceriaController::class, 'update'])->name('admin.marca-carroceria.update')->middleware('auth');
Route::delete('/admin/marca-carroceria/{id}', [App\Http\Controllers\MarcaCarroceriaController::class, 'destroy'])->name('admin.marca-carroceria.destroy')->middleware('auth');

Route::get('/admin/marca-vehiculo', [App\Http\Controllers\MarcaVehiculoController::class, 'index'])->name('admin.marca-vehiculo.index')->middleware('auth');
Route::post('/admin/marca-vehiculo/create', [App\Http\Controllers\MarcaVehiculoController::class, 'store'])->name('admin.marca-vehiculo.store')->middleware('auth');
Route::put('/admin/marca-vehiculo/{id}', [App\Http\Controllers\MarcaVehiculoController::class, 'update'])->name('admin.marca-vehiculo.update')->middleware('auth');
Route::delete('/admin/marca-vehiculo/{id}', [App\Http\Controllers\MarcaVehiculoController::class, 'destroy'])->name('admin.marca-vehiculo.destroy')->middleware('auth');


// Rutas para flota
Route::get('/admin/flota', [App\Http\Controllers\FlotaController::class, 'index'])->name('admin.flota.index')->middleware('auth');
Route::get('/admin/flota/servicios-kilometraje', [App\Http\Controllers\ServicioKilometrajeController::class, 'index'])->name('admin.servicios-kilometraje.index')->middleware('auth');
Route::post('/admin/flota/servicios-kilometraje/registrar', [App\Http\Controllers\ServicioKilometrajeController::class, 'registrarServicio'])->name('admin.servicios-kilometraje.registrar')->middleware('auth');
Route::get('/admin/flota/verificaciones-tecnicas', [App\Http\Controllers\VerificacionTecnicaController::class, 'index'])->name('admin.verificaciones-tecnicas.index')->middleware('auth');
Route::post('/admin/flota/verificaciones-tecnicas/registrar', [App\Http\Controllers\VerificacionTecnicaController::class, 'registrar'])->name('admin.verificaciones-tecnicas.registrar')->middleware('auth');
Route::get('/admin/flota/historial-articulos', [App\Http\Controllers\HistorialArticulosVehiculoController::class, 'index'])->name('admin.historial-articulos-vehiculo.index')->middleware('auth');
Route::get('/admin/flota/costeo-vehiculos', [App\Http\Controllers\VehicleCostController::class, 'index'])->name('admin.costeo-vehiculos.index')->middleware('auth');
Route::get('/admin/flota/gestion-cubiertas', [App\Http\Controllers\GestionCubiertaController::class, 'index'])->name('admin.gestion-cubiertas.index')->middleware('auth');
Route::get('/admin/flota/movimiento-cubiertas', [App\Http\Controllers\MovimientoCubiertaController::class, 'create'])->name('admin.movimiento-cubiertas.index')->middleware('auth');
Route::post('/admin/flota/movimiento-cubiertas', [App\Http\Controllers\MovimientoCubiertaController::class, 'store'])->name('admin.movimiento-cubiertas.store')->middleware('auth');
Route::put('/admin/flota/movimiento-cubiertas/{id}', [App\Http\Controllers\MovimientoCubiertaController::class, 'update'])->name('admin.movimiento-cubiertas.update')->middleware('auth');
Route::get('/admin/flota/create', [App\Http\Controllers\FlotaController::class, 'create'])->name('admin.flota.create')->middleware('auth');
Route::post('/admin/flota/create', [App\Http\Controllers\FlotaController::class, 'store'])->name('admin.flota.store')->middleware('auth');
Route::get('/admin/flota/{flota}/repuestos', [App\Http\Controllers\FlotaRepuestoController::class, 'index'])->name('admin.flota.repuestos.index')->middleware('auth');
Route::post('/admin/flota/{flota}/repuestos', [App\Http\Controllers\FlotaRepuestoController::class, 'store'])->name('admin.flota.repuestos.store')->middleware('auth');
Route::put('/admin/flota/{flota}/repuestos/{repuesto}', [App\Http\Controllers\FlotaRepuestoController::class, 'update'])->name('admin.flota.repuestos.update')->middleware('auth');
Route::delete('/admin/flota/{flota}/repuestos/{repuesto}', [App\Http\Controllers\FlotaRepuestoController::class, 'destroy'])->name('admin.flota.repuestos.destroy')->middleware('auth');
Route::get('/admin/flota/{id}/edit', [App\Http\Controllers\FlotaController::class, 'edit'])->name('admin.flota.edit')->middleware('auth');
Route::get('/admin/flota/{id}/servicio-asignado', [App\Http\Controllers\FlotaController::class, 'editServicioAsignado'])->name('admin.flota.servicio-asignado.edit')->middleware('auth');
Route::put('/admin/flota/{id}/servicio-asignado', [App\Http\Controllers\FlotaController::class, 'updateServicioAsignado'])->name('admin.flota.servicio-asignado.update')->middleware('auth');
Route::put('/admin/flota/{id}', [App\Http\Controllers\FlotaController::class, 'update'])->name('admin.flota.update')->middleware('auth');
Route::delete('/admin/flota/{id}', [App\Http\Controllers\FlotaController::class, 'destroy'])->name('admin.flota.destroy')->middleware('auth');

// Rutas para Check List Vehicular
Route::get('/admin/controles-unidad', [App\Http\Controllers\ControlUnidadController::class, 'index'])->name('admin.controles-unidad.index')->middleware('auth');
Route::get('/admin/controles-unidad/create', [App\Http\Controllers\ControlUnidadController::class, 'create'])->name('admin.controles-unidad.create')->middleware('auth');
Route::post('/admin/controles-unidad/create', [App\Http\Controllers\ControlUnidadController::class, 'store'])->name('admin.controles-unidad.store')->middleware('auth');
Route::get('/admin/controles-unidad/{controlUnidad}', [App\Http\Controllers\ControlUnidadController::class, 'show'])->name('admin.controles-unidad.show')->middleware('auth');
Route::post('/admin/controles-unidad/{controlUnidad}/orden-trabajo', [App\Http\Controllers\ControlUnidadController::class, 'crearOrdenTrabajo'])->name('admin.controles-unidad.orden-trabajo')->middleware('auth');
Route::delete('/admin/controles-unidad/{controlUnidad}', [App\Http\Controllers\ControlUnidadController::class, 'destroy'])->name('admin.controles-unidad.destroy')->middleware('auth');

// Rutas para ordenes de trabajo
Route::get('/admin/ordenes-trabajo', [App\Http\Controllers\OrdenTrabajoController::class, 'index'])->name('admin.ordenes-trabajo.index')->middleware('auth');
Route::post('/admin/ordenes-trabajo/create', [App\Http\Controllers\OrdenTrabajoController::class, 'store'])->name('admin.ordenes-trabajo.store')->middleware('auth');
Route::get('/admin/ordenes-trabajo/{id}/edit', [App\Http\Controllers\OrdenTrabajoController::class, 'edit'])->name('admin.ordenes-trabajo.edit')->middleware('auth');
Route::get('/admin/ordenes-trabajo/{id}/articulos', [App\Http\Controllers\OrdenTrabajoController::class, 'articulos'])->name('admin.ordenes-trabajo.articulos')->middleware('auth');
Route::post('/admin/ordenes-trabajo/{id}/registrar-servicio-kilometraje', [App\Http\Controllers\OrdenTrabajoController::class, 'registrarServicioKilometraje'])->name('admin.ordenes-trabajo.registrar-servicio-kilometraje')->middleware('auth');
Route::put('/admin/ordenes-trabajo/{id}', [App\Http\Controllers\OrdenTrabajoController::class, 'update'])->name('admin.ordenes-trabajo.update')->middleware('auth');
Route::delete('/admin/ordenes-trabajo/{id}', [App\Http\Controllers\OrdenTrabajoController::class, 'destroy'])->name('admin.ordenes-trabajo.destroy')->middleware('auth');
Route::post('/admin/ordenes-trabajo/{id}/articulos', [App\Http\Controllers\OrdenTrabajoController::class, 'storeArticulo'])->name('admin.ordenes-trabajo.articulos.store')->middleware('auth');
Route::post('/admin/ordenes-trabajo/{id}/articulos/kit-servicio', [App\Http\Controllers\OrdenTrabajoController::class, 'cargarKitServicio'])->name('admin.ordenes-trabajo.articulos.kit-servicio')->middleware('auth');
Route::delete('/admin/ordenes-trabajo/{id}/articulos/{detalleId}', [App\Http\Controllers\OrdenTrabajoController::class, 'destroyArticulo'])->name('admin.ordenes-trabajo.articulos.destroy')->middleware('auth');
Route::resource('/admin/ordenes-trabajo-motivos', App\Http\Controllers\OrdenTrabajoMotivoController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->names('admin.ordenes-trabajo-motivos')
    ->middleware('auth');

// Rutas para articulos
Route::get('/admin/articulos', [App\Http\Controllers\ArticuloController::class, 'index'])->name('admin.articulos.index')->middleware('auth');
Route::get('/admin/articulos/listado', [App\Http\Controllers\ArticuloController::class, 'listado'])->name('admin.articulos.listado')->middleware('auth');
Route::get('/admin/articulos/create', [App\Http\Controllers\ArticuloController::class, 'create'])->name('admin.articulos.create')->middleware('auth');
Route::post('/admin/articulos/create', [App\Http\Controllers\ArticuloController::class, 'store'])->name('admin.articulos.store')->middleware('auth');
Route::get('/admin/articulos/{id}', [App\Http\Controllers\ArticuloController::class, 'show'])->name('admin.articulos.show')->middleware('auth');    
Route::get('/admin/articulos/{id}/edit', [App\Http\Controllers\ArticuloController::class, 'edit'])->name('admin.articulos.edit')->middleware('auth');
Route::put('/admin/articulos/{id}', [App\Http\Controllers\ArticuloController::class, 'update'])->name('admin.articulos.update')->middleware('auth');
Route::delete('/admin/articulos/{id}', [App\Http\Controllers\ArticuloController::class, 'destroy'])->name('admin.articulos.destroy')->middleware('auth');

// Rutas para unidad de medidas
Route::get('/admin/unidad-medidas', [App\Http\Controllers\UnidadMedidaController::class, 'index'])->name('admin.unidad-medidas.index')->middleware('auth');
Route::post('/admin/unidad-medidas/create', [App\Http\Controllers\UnidadMedidaController::class, 'store'])->name('admin.unidad-medidas.store')->middleware('auth');
Route::put('/admin/unidad-medidas/{id}', [App\Http\Controllers\UnidadMedidaController::class, 'update'])->name('admin.unidad-medidas.update')->middleware('auth');
Route::delete('/admin/unidad-medidas/{id}', [App\Http\Controllers\UnidadMedidaController::class, 'destroy'])->name('admin.unidad-medidas.destroy')->middleware('auth');

// Rutas para inventarios
Route::get('/admin/inventarios', [App\Http\Controllers\InventarioController::class, 'index'])->name('admin.inventarios.index')->middleware('auth');
Route::get('/admin/inventarios/bajo-stock', [App\Http\Controllers\InventarioController::class, 'bajoStock'])->name('admin.inventarios.bajo-stock')->middleware('auth');
Route::get('/admin/inventarios/sin-stock', [App\Http\Controllers\InventarioController::class, 'sinStock'])->name('admin.inventarios.sin-stock')->middleware('auth');
Route::get('/admin/inventarios/{id}/etiqueta', [App\Http\Controllers\InventarioController::class, 'etiqueta'])->name('admin.inventarios.etiqueta')->middleware('auth');
Route::get('/admin/inventarios/transferencias', [App\Http\Controllers\TransferenciaDepositoController::class, 'index'])->name('admin.inventarios.transferencias.index')->middleware('auth');
Route::get('/admin/inventarios/transferencias/create', [App\Http\Controllers\TransferenciaDepositoController::class, 'create'])->name('admin.inventarios.transferencias.create')->middleware('auth');
Route::post('/admin/inventarios/transferencias', [App\Http\Controllers\TransferenciaDepositoController::class, 'store'])->name('admin.inventarios.transferencias.store')->middleware('auth');
Route::get('/admin/inventarios/transferencias/{id}', [App\Http\Controllers\TransferenciaDepositoController::class, 'show'])->name('admin.inventarios.transferencias.show')->middleware('auth');
Route::post('/admin/inventarios/create', [App\Http\Controllers\InventarioController::class, 'store'])->name('admin.inventarios.store')->middleware('auth');
Route::put('/admin/inventarios/{id}', [App\Http\Controllers\InventarioController::class, 'update'])->name('admin.inventarios.update')->middleware('auth');
Route::delete('/admin/inventarios/{id}', [App\Http\Controllers\InventarioController::class, 'destroy'])->name('admin.inventarios.destroy')->middleware('auth');

// Rutas para entrega de herramientas
Route::get('/admin/entregas-herramientas', [App\Http\Controllers\EntregaHerramientaController::class, 'index'])->name('admin.entregas-herramientas.index')->middleware('auth');
Route::get('/admin/entregas-herramientas/create', [App\Http\Controllers\EntregaHerramientaController::class, 'create'])->name('admin.entregas-herramientas.create')->middleware('auth');
Route::post('/admin/entregas-herramientas', [App\Http\Controllers\EntregaHerramientaController::class, 'store'])->name('admin.entregas-herramientas.store')->middleware('auth');
Route::get('/admin/entregas-herramientas/{id}/edit', [App\Http\Controllers\EntregaHerramientaController::class, 'edit'])->name('admin.entregas-herramientas.edit')->middleware('auth');
Route::put('/admin/entregas-herramientas/{id}', [App\Http\Controllers\EntregaHerramientaController::class, 'update'])->name('admin.entregas-herramientas.update')->middleware('auth');
Route::get('/admin/entregas-herramientas/{id}', [App\Http\Controllers\EntregaHerramientaController::class, 'show'])->name('admin.entregas-herramientas.show')->middleware('auth');
Route::get('/admin/entregas-herramientas/{id}/planilla', [App\Http\Controllers\EntregaHerramientaController::class, 'planilla'])->name('admin.entregas-herramientas.planilla')->middleware('auth');
Route::post('/admin/entregas-herramientas/{id}/detalles/{detalleId}/devolver', [App\Http\Controllers\EntregaHerramientaController::class, 'devolver'])->name('admin.entregas-herramientas.devolver')->middleware('auth');

// Rutas para entrega de ropa y EPP
Route::get('/admin/entregas-ropa-epp', [App\Http\Controllers\EntregaRopaEppController::class, 'index'])->name('admin.entregas-ropa-epp.index')->middleware('auth');
Route::get('/admin/entregas-ropa-epp/create', [App\Http\Controllers\EntregaRopaEppController::class, 'create'])->name('admin.entregas-ropa-epp.create')->middleware('auth');
Route::post('/admin/entregas-ropa-epp', [App\Http\Controllers\EntregaRopaEppController::class, 'store'])->name('admin.entregas-ropa-epp.store')->middleware('auth');
Route::get('/admin/entregas-ropa-epp/{id}/edit', [App\Http\Controllers\EntregaRopaEppController::class, 'edit'])->name('admin.entregas-ropa-epp.edit')->middleware('auth');
Route::put('/admin/entregas-ropa-epp/{id}', [App\Http\Controllers\EntregaRopaEppController::class, 'update'])->name('admin.entregas-ropa-epp.update')->middleware('auth');
Route::get('/admin/entregas-ropa-epp/{id}', [App\Http\Controllers\EntregaRopaEppController::class, 'show'])->name('admin.entregas-ropa-epp.show')->middleware('auth');
Route::get('/admin/entregas-ropa-epp/{id}/planilla', [App\Http\Controllers\EntregaRopaEppController::class, 'planilla'])->name('admin.entregas-ropa-epp.planilla')->middleware('auth');
Route::post('/admin/entregas-ropa-epp/{id}/detalles/{detalleId}/devolver', [App\Http\Controllers\EntregaRopaEppController::class, 'devolver'])->name('admin.entregas-ropa-epp.devolver')->middleware('auth');

// Rutas para reparaciones de articulos
Route::get('/admin/reparaciones-articulos', [App\Http\Controllers\ReparacionArticuloController::class, 'index'])->name('admin.reparaciones-articulos.index')->middleware('auth');
Route::get('/admin/reparaciones-articulos/pendientes', [App\Http\Controllers\ReparacionArticuloController::class, 'pendientes'])->name('admin.reparaciones-articulos.pendientes')->middleware('auth');
Route::get('/admin/reparaciones-articulos/create', [App\Http\Controllers\ReparacionArticuloController::class, 'create'])->name('admin.reparaciones-articulos.create')->middleware('auth');
Route::get('/admin/reparaciones-articulos/proveedor/{id}', [App\Http\Controllers\ReparacionArticuloController::class, 'proveedorData'])->name('admin.reparaciones-articulos.proveedor.data')->middleware('auth');
Route::post('/admin/reparaciones-articulos', [App\Http\Controllers\ReparacionArticuloController::class, 'store'])->name('admin.reparaciones-articulos.store')->middleware('auth');
Route::post('/admin/reparaciones-articulos/bulk', [App\Http\Controllers\ReparacionArticuloController::class, 'bulk'])->name('admin.reparaciones-articulos.bulk')->middleware('auth');
Route::get('/admin/reparaciones-articulos/{id}/edit', [App\Http\Controllers\ReparacionArticuloController::class, 'edit'])->name('admin.reparaciones-articulos.edit')->middleware('auth');
Route::put('/admin/reparaciones-articulos/{id}', [App\Http\Controllers\ReparacionArticuloController::class, 'update'])->name('admin.reparaciones-articulos.update')->middleware('auth');
Route::delete('/admin/reparaciones-articulos/{id}', [App\Http\Controllers\ReparacionArticuloController::class, 'destroy'])->name('admin.reparaciones-articulos.destroy')->middleware('auth');
Route::get('/admin/reparaciones-articulos/{id}', [App\Http\Controllers\ReparacionArticuloController::class, 'show'])->name('admin.reparaciones-articulos.show')->middleware('auth');
Route::get('/admin/reparaciones-articulos/{id}/planilla', [App\Http\Controllers\ReparacionArticuloController::class, 'planilla'])->name('admin.reparaciones-articulos.planilla')->middleware('auth');
Route::post('/admin/reparaciones-articulos/{id}/detalles/{detalleId}/devolver', [App\Http\Controllers\ReparacionArticuloController::class, 'devolver'])->name('admin.reparaciones-articulos.devolver')->middleware('auth');
Route::post('/admin/reparaciones-articulos/{id}/reclamos', [App\Http\Controllers\ReparacionArticuloController::class, 'storeReclamo'])->name('admin.reparaciones-articulos.reclamos.store')->middleware('auth');

// Rutas para orden de compra

// Rutas para solicitudes de repuestos no catalogados
Route::get('/admin/solicitudes-repuestos', [App\Http\Controllers\SolicitudRepuestoController::class, 'index'])->name('admin.solicitudes-repuestos.index')->middleware('auth');
Route::get('/admin/solicitudes-repuestos/create', [App\Http\Controllers\SolicitudRepuestoController::class, 'create'])->name('admin.solicitudes-repuestos.create')->middleware('auth');
Route::post('/admin/solicitudes-repuestos', [App\Http\Controllers\SolicitudRepuestoController::class, 'store'])->name('admin.solicitudes-repuestos.store')->middleware('auth');
Route::post('/admin/solicitudes-repuestos/bulk', [App\Http\Controllers\SolicitudRepuestoController::class, 'bulk'])->name('admin.solicitudes-repuestos.bulk')->middleware('auth');
Route::get('/admin/solicitudes-repuestos/{id}', [App\Http\Controllers\SolicitudRepuestoController::class, 'show'])->name('admin.solicitudes-repuestos.show')->middleware('auth');
Route::get('/admin/solicitudes-repuestos/{id}/edit', [App\Http\Controllers\SolicitudRepuestoController::class, 'edit'])->name('admin.solicitudes-repuestos.edit')->middleware('auth');
Route::put('/admin/solicitudes-repuestos/{id}', [App\Http\Controllers\SolicitudRepuestoController::class, 'update'])->name('admin.solicitudes-repuestos.update')->middleware('auth');
Route::post('/admin/solicitudes-repuestos/{id}/aprobar', [App\Http\Controllers\SolicitudRepuestoController::class, 'aprobar'])->name('admin.solicitudes-repuestos.aprobar')->middleware('auth');
Route::post('/admin/solicitudes-repuestos/{id}/rechazar', [App\Http\Controllers\SolicitudRepuestoController::class, 'rechazar'])->name('admin.solicitudes-repuestos.rechazar')->middleware('auth');
Route::post('/admin/solicitudes-repuestos/{id}/asociar-articulo', [App\Http\Controllers\SolicitudRepuestoController::class, 'asociarArticulo'])->name('admin.solicitudes-repuestos.asociar-articulo')->middleware('auth');
Route::post('/admin/solicitudes-repuestos/{id}/crear-articulo', [App\Http\Controllers\SolicitudRepuestoController::class, 'crearArticulo'])->name('admin.solicitudes-repuestos.crear-articulo')->middleware('auth');
Route::post('/admin/solicitudes-repuestos/{id}/generar-pedido', [App\Http\Controllers\SolicitudRepuestoController::class, 'generarPedido'])->name('admin.solicitudes-repuestos.generar-pedido')->middleware('auth');
Route::post('/admin/solicitudes-repuestos/{id}/estado', [App\Http\Controllers\SolicitudRepuestoController::class, 'actualizarEstado'])->name('admin.solicitudes-repuestos.estado')->middleware('auth');

Route::get('/admin/ordenes-compra', [App\Http\Controllers\CompraTmpController::class, 'index'])->name('admin.ordenes-compra.index')->middleware('auth');
Route::get('/admin/ordenes-compra/create', [App\Http\Controllers\CompraTmpController::class, 'create'])->name('admin.ordenes-compra.create')->middleware('auth');
Route::post('/admin/ordenes-compra/items/add', [App\Http\Controllers\CompraTmpController::class, 'additem'])->name('admin.ordenes-compra.additem')->middleware('auth');
Route::put('/admin/ordenes-compra/items/{itemid}', [App\Http\Controllers\CompraTmpController::class, 'updateitem'])->name('admin.ordenes-compra.updateitem')->middleware('auth');
Route::delete('/admin/ordenes-compra/items/{itemid}', [App\Http\Controllers\CompraTmpController::class, 'removeitem'])->name('admin.ordenes-compra.removeitem')->middleware('auth');
Route::delete('/admin/ordenes-compra/items', [App\Http\Controllers\CompraTmpController::class, 'clearitem'])->name('admin.ordenes-compra.clearitem')->middleware('auth');
Route::post('/admin/ordenes-compra/create', [App\Http\Controllers\CompraTmpController::class, 'store'])->name('admin.ordenes-compra.store')->middleware('auth');
Route::post('/admin/ordenes-compra/{id}/detalles', [App\Http\Controllers\CompraTmpController::class, 'storeDetalle'])->name('admin.ordenes-compra.detalles.store')->middleware('auth');
Route::put('/admin/ordenes-compra/{id}/detalles/{detalleId}', [App\Http\Controllers\CompraTmpController::class, 'updateDetalle'])->name('admin.ordenes-compra.detalles.update')->middleware('auth');
Route::delete('/admin/ordenes-compra/{id}/detalles/{detalleId}', [App\Http\Controllers\CompraTmpController::class, 'destroyDetalle'])->name('admin.ordenes-compra.detalles.destroy')->middleware('auth');
Route::post('/admin/ordenes-compra/{id}/mail', [App\Http\Controllers\CompraTmpController::class, 'enviarMail'])->name('admin.ordenes-compra.mail')->middleware('auth');
Route::get('/admin/ordenes-compra/{id}/pagos', [App\Http\Controllers\CompraPagoController::class, 'create'])->name('admin.ordenes-compra.pagos.create')->middleware('auth');
Route::post('/admin/ordenes-compra/{id}/pagos', [App\Http\Controllers\CompraPagoController::class, 'store'])->name('admin.ordenes-compra.pagos.store')->middleware('auth');
Route::get('/admin/ordenes-compra/{id}/pagos/{pagoId}/comprobante', [App\Http\Controllers\CompraPagoController::class, 'comprobante'])->name('admin.ordenes-compra.pagos.comprobante')->middleware('auth');
Route::put('/admin/ordenes-compra/{id}/pagos/{pagoId}/comprobante', [App\Http\Controllers\CompraPagoController::class, 'updateComprobante'])->name('admin.ordenes-compra.pagos.comprobante.update')->middleware('auth');
Route::delete('/admin/ordenes-compra/{id}/pagos/{pagoId}', [App\Http\Controllers\CompraPagoController::class, 'destroy'])->name('admin.ordenes-compra.pagos.destroy')->middleware('auth');
Route::get('/admin/ordenes-compra/{id}', [App\Http\Controllers\CompraTmpController::class, 'show'])->name('admin.ordenes-compra.show')->middleware('auth');
Route::get('/admin/ordenes-compra/{id}/edit', [App\Http\Controllers\CompraTmpController::class, 'edit'])->name('admin.ordenes-compra.edit')->middleware('auth');
Route::put('/admin/ordenes-compra/{id}', [App\Http\Controllers\CompraTmpController::class, 'update'])->name('admin.ordenes-compra.update')->middleware('auth');
Route::delete('/admin/ordenes-compra/{id}', [App\Http\Controllers\CompraTmpController::class, 'destroy'])->name('admin.ordenes-compra.destroy')->middleware('auth');

// Rutas para pedidos de articulos
Route::get('/admin/pedidos-articulos', [App\Http\Controllers\PedidoArticuloController::class, 'index'])->name('admin.pedidos-articulos.index')->middleware('auth');
Route::get('/admin/pedidos-articulos/create', [App\Http\Controllers\PedidoArticuloController::class, 'create'])->name('admin.pedidos-articulos.create')->middleware('auth');
Route::post('/admin/pedidos-articulos/items/add', [App\Http\Controllers\PedidoArticuloController::class, 'addItem'])->name('admin.pedidos-articulos.additem')->middleware('auth');
Route::put('/admin/pedidos-articulos/items/{itemId}', [App\Http\Controllers\PedidoArticuloController::class, 'updateItem'])->name('admin.pedidos-articulos.updateitem')->middleware('auth');
Route::delete('/admin/pedidos-articulos/items/{itemId}', [App\Http\Controllers\PedidoArticuloController::class, 'removeItem'])->name('admin.pedidos-articulos.removeitem')->middleware('auth');
Route::delete('/admin/pedidos-articulos/items', [App\Http\Controllers\PedidoArticuloController::class, 'clearItems'])->name('admin.pedidos-articulos.clearitems')->middleware('auth');
Route::post('/admin/pedidos-articulos/create', [App\Http\Controllers\PedidoArticuloController::class, 'store'])->name('admin.pedidos-articulos.store')->middleware('auth');
Route::post('/admin/pedidos-articulos/generar-sugeridos', [App\Http\Controllers\PedidoArticuloController::class, 'generarSugeridos'])->name('admin.pedidos-articulos.generar-sugeridos')->middleware('auth');
Route::post('/admin/pedidos-articulos/{id}/detalles', [App\Http\Controllers\PedidoArticuloController::class, 'storeDetalle'])->name('admin.pedidos-articulos.detalles.store')->middleware('auth');
Route::put('/admin/pedidos-articulos/{id}/detalles/{detalleId}', [App\Http\Controllers\PedidoArticuloController::class, 'updateDetalle'])->name('admin.pedidos-articulos.detalles.update')->middleware('auth');
Route::delete('/admin/pedidos-articulos/{id}/detalles/{detalleId}', [App\Http\Controllers\PedidoArticuloController::class, 'destroyDetalle'])->name('admin.pedidos-articulos.detalles.destroy')->middleware('auth');
Route::get('/admin/pedidos-articulos/{id}', [App\Http\Controllers\PedidoArticuloController::class, 'show'])->name('admin.pedidos-articulos.show')->middleware('auth');
Route::get('/admin/pedidos-articulos/{id}/edit', [App\Http\Controllers\PedidoArticuloController::class, 'edit'])->name('admin.pedidos-articulos.edit')->middleware('auth');
Route::put('/admin/pedidos-articulos/{id}', [App\Http\Controllers\PedidoArticuloController::class, 'update'])->name('admin.pedidos-articulos.update')->middleware('auth');
Route::delete('/admin/pedidos-articulos/{id}', [App\Http\Controllers\PedidoArticuloController::class, 'destroy'])->name('admin.pedidos-articulos.destroy')->middleware('auth');



//Rutas para compras

Route::get('/admin/compras', [App\Http\Controllers\CompraController::class, 'index'])->name('admin.compras.index')->middleware('auth');
Route::get('/admin/compras/{id}', [App\Http\Controllers\CompraController::class, 'show'])->name('admin.compras.show')->middleware('auth');
Route::delete('/admin/compras/{compra}', [App\Http\Controllers\CompraController::class, 'destroy'])->name('admin.compras.destroy')->middleware('auth');

// Rutas para ingresos de articulos
Route::get('/admin/entradas', [App\Http\Controllers\EntradaController::class, 'index'])->name('admin.entradas.index')->middleware('auth');
Route::get('/admin/entradas/pendientes-ordenes', [App\Http\Controllers\EntradaController::class, 'pendientes'])->name('admin.entradas.pendientes')->middleware('auth');
Route::post('/admin/entradas/pendientes-ordenes', [App\Http\Controllers\EntradaController::class, 'storePendiente'])->name('admin.entradas.pendientes.store')->middleware('auth');
Route::get('/admin/entradas/create', [App\Http\Controllers\EntradaController::class, 'create'])->name('admin.entradas.create')->middleware('auth');
Route::post('/admin/entradas/create', [App\Http\Controllers\EntradaController::class, 'store'])->name('admin.entradas.store')->middleware('auth');
Route::get('/admin/entradas/{id}', [App\Http\Controllers\EntradaController::class, 'show'])->name('admin.entradas.show')->middleware('auth');
Route::get('/admin/entradas/{id}/edit', [App\Http\Controllers\EntradaController::class, 'edit'])->name('admin.entradas.edit')->middleware('auth');
Route::put('/admin/entradas/{id}', [App\Http\Controllers\EntradaController::class, 'update'])->name('admin.entradas.update')->middleware('auth');
Route::delete('/admin/entradas/{id}', [App\Http\Controllers\EntradaController::class, 'destroy'])->name('admin.entradas.destroy')->middleware('auth');
