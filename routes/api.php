<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileChatController;
use App\Http\Controllers\Api\MobileOrdenTrabajoController;
use App\Http\Controllers\Api\MobileReparacionArticuloController;
use App\Http\Controllers\Api\MobileSolicitudRepuestoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [MobileAuthController::class, 'login'])
        ->name('api.v1.auth.login');

    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
        Route::post('/auth/logout', [MobileAuthController::class, 'logout'])
            ->name('api.v1.auth.logout');
        Route::get('/me', [MobileAuthController::class, 'me'])
            ->name('api.v1.me');
        Route::get('/me/permisos', [MobileAuthController::class, 'permissions'])
            ->name('api.v1.me.permissions');

        Route::get('/chat', [MobileChatController::class, 'index'])
            ->name('api.v1.chat.index');
        Route::get('/chat/users/{user}', [MobileChatController::class, 'show'])
            ->name('api.v1.chat.show');
        Route::post('/chat/messages', [MobileChatController::class, 'store'])
            ->name('api.v1.chat.store');

        Route::get('/checklists/catalogos', [\App\Http\Controllers\Api\MobileChecklistController::class, 'catalogs'])
            ->name('api.v1.checklists.catalogs');
        Route::get('/checklists', [\App\Http\Controllers\Api\MobileChecklistController::class, 'index'])
            ->name('api.v1.checklists.index');
        Route::get('/checklists/{controlUnidad}', [\App\Http\Controllers\Api\MobileChecklistController::class, 'show'])
            ->name('api.v1.checklists.show');
        Route::post('/checklists', [\App\Http\Controllers\Api\MobileChecklistController::class, 'store'])
            ->name('api.v1.checklists.store');

        Route::get('/ordenes-trabajo', [MobileOrdenTrabajoController::class, 'index'])
            ->name('api.v1.ordenes-trabajo.index');
        Route::get('/ordenes-trabajo/{ordenTrabajo}', [MobileOrdenTrabajoController::class, 'show'])
            ->name('api.v1.ordenes-trabajo.show');
        Route::patch('/ordenes-trabajo/{ordenTrabajo}', [MobileOrdenTrabajoController::class, 'update'])
            ->name('api.v1.ordenes-trabajo.update');

        Route::get('/solicitudes-repuestos/catalogos', [MobileSolicitudRepuestoController::class, 'catalogs'])
            ->name('api.v1.solicitudes-repuestos.catalogs');
        Route::get('/solicitudes-repuestos', [MobileSolicitudRepuestoController::class, 'index'])
            ->name('api.v1.solicitudes-repuestos.index');
        Route::get('/solicitudes-repuestos/{solicitudRepuesto}', [MobileSolicitudRepuestoController::class, 'show'])
            ->name('api.v1.solicitudes-repuestos.show');
        Route::post('/solicitudes-repuestos', [MobileSolicitudRepuestoController::class, 'store'])
            ->name('api.v1.solicitudes-repuestos.store');

        Route::get('/reparaciones-articulos', [MobileReparacionArticuloController::class, 'index'])
            ->name('api.v1.reparaciones-articulos.index');
        Route::get('/reparaciones-articulos/{reparacionArticulo}', [MobileReparacionArticuloController::class, 'show'])
            ->name('api.v1.reparaciones-articulos.show');
        Route::post('/reparaciones-articulos/{reparacionArticulo}/detalles/{detalle}/devolver', [MobileReparacionArticuloController::class, 'devolver'])
            ->name('api.v1.reparaciones-articulos.devolver');
    });
});
