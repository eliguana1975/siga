<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesNombreCatalog;
use App\Models\TipoVehiculo;

class TipoVehiculoController extends Controller
{
    use ManagesNombreCatalog;

    protected string $modelClass = TipoVehiculo::class;
    protected string $routePrefix = 'admin.tipo-vehiculo';
    protected string $permissionName = 'administrar-tipo-vehiculo';
    protected string $title = 'Gestión de Tipo Vehículo';
    protected string $subtitle = 'Administra los tipos de vehículo disponibles para la flota del sistema.';
    protected string $entityName = 'tipo de vehículo';
    protected string $entityNamePlural = 'tipos de vehículo';
}
