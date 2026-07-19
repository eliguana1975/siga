<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesNombreCatalog;
use App\Models\MarcaVehiculo;

class MarcaVehiculoController extends Controller
{
    use ManagesNombreCatalog;

    protected string $modelClass = MarcaVehiculo::class;
    protected string $routePrefix = 'admin.marca-vehiculo';
    protected string $permissionName = 'administrar-marca-vehiculo';
    protected string $title = 'Gestión de Marca Vehículo';
    protected string $subtitle = 'Administra las marcas de vehículo disponibles para la flota del sistema.';
    protected string $entityName = 'marca de vehículo';
    protected string $entityNamePlural = 'marcas de vehículo';
}
