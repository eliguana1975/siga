<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesNombreCatalog;
use App\Models\ServicioAsignado;

class ServicioAsignadoController extends Controller
{
    use ManagesNombreCatalog;

    protected string $modelClass = ServicioAsignado::class;
    protected string $routePrefix = 'admin.servicios-asignados';
    protected string $permissionName = 'administrar-servicios-asignados';
    protected string $title = 'Gestion de Servicio Asignado';
    protected string $subtitle = 'Administra los servicios disponibles para asignar en el Check List Vehicular.';
    protected string $entityName = 'servicio asignado';
    protected string $entityNamePlural = 'servicios asignados';
}
