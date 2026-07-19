<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesNombreCatalog;
use App\Models\ModeloCaja;

class ModeloCajaController extends Controller
{
    use ManagesNombreCatalog;

    protected string $modelClass = ModeloCaja::class;
    protected string $routePrefix = 'admin.modelo-caja';
    protected string $permissionName = 'administrar-modelo-caja';
    protected string $title = 'Gestión de Modelo Caja';
    protected string $subtitle = 'Administra los modelos de caja disponibles para la flota del sistema.';
    protected string $entityName = 'modelo caja';
    protected string $entityNamePlural = 'modelos de caja';
}
