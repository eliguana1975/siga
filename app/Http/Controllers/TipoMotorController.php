<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesNombreCatalog;
use App\Models\TipoMotor;

class TipoMotorController extends Controller
{
    use ManagesNombreCatalog;

    protected string $modelClass = TipoMotor::class;
    protected string $routePrefix = 'admin.tipo-motor';
    protected string $permissionName = 'administrar-tipo-motor';
    protected string $title = 'Gestión de Tipo Motor';
    protected string $subtitle = 'Administra los tipos de motor disponibles para la flota del sistema.';
    protected string $entityName = 'tipo motor';
    protected string $entityNamePlural = 'tipos de motor';
}
