<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesNombreCatalog;
use App\Models\MarcaCarroceria;

class MarcaCarroceriaController extends Controller
{
    use ManagesNombreCatalog;

    protected string $modelClass = MarcaCarroceria::class;
    protected string $routePrefix = 'admin.marca-carroceria';
    protected string $permissionName = 'administrar-marca-carroceria';
    protected string $title = 'Gestión de Marca Carrocería';
    protected string $subtitle = 'Administra las marcas de carrocería disponibles para la flota del sistema.';
    protected string $entityName = 'marca carrocería';
    protected string $entityNamePlural = 'marcas de carrocería';
}
