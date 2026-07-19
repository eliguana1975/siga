<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesNombreCatalog;
use App\Models\TipoCaja;

class TipoCajaController extends Controller
{
    use ManagesNombreCatalog;

    protected string $modelClass = TipoCaja::class;
    protected string $routePrefix = 'admin.tipo-caja';
    protected string $permissionName = 'administrar-tipo-caja';
    protected string $title = 'Gestión de Tipo Caja';
    protected string $subtitle = 'Administra los tipos de caja disponibles para la flota del sistema.';
    protected string $entityName = 'tipo caja';
    protected string $entityNamePlural = 'tipos de caja';
}
