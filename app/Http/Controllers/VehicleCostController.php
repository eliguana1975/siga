<?php

namespace App\Http\Controllers;

use App\Support\VehicleCostService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VehicleCostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:historial-articulos-vehiculo.ver');
    }

    public function index(Request $request, VehicleCostService $service)
    {
        $validated = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $desde = isset($validated['fecha_desde'])
            ? Carbon::parse($validated['fecha_desde'])->startOfDay()
            : now()->startOfMonth();
        $hasta = isset($validated['fecha_hasta'])
            ? Carbon::parse($validated['fecha_hasta'])->endOfDay()
            : now()->endOfDay();

        $costeo = $service->ranking($desde, $hasta);

        return view('admin.costeo-vehiculos.index', compact('costeo', 'desde', 'hasta'));
    }
}
