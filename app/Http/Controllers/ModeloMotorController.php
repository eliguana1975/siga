<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesNombreCatalog;
use App\Models\ModeloMotor;

class ModeloMotorController extends Controller
{
    use ManagesNombreCatalog;

    protected string $modelClass = ModeloMotor::class;
    protected string $routePrefix = 'admin.modelo-motor';
    protected string $permissionName = 'administrar-modelo-motor';
    protected string $title = 'Gestión de Modelo Motor';
    protected string $subtitle = 'Administra los modelos de motor disponibles para la flota del sistema.';
    protected string $entityName = 'modelo motor';
    protected string $entityNamePlural = 'modelos de motor';
}
