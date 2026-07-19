<?php

namespace App\Http\Controllers;

use App\Support\OperationalAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationalAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:auditoria-operativa.ver');
    }

    public function index(Request $request, OperationalAuditService $service)
    {
        $diasOt = max(1, (int) $request->input('dias_ot', 30));
        $audit = $service->audit($diasOt);

        return view('admin.auditoria-operativa.index', compact('audit', 'diasOt'));
    }

    public function json(Request $request, OperationalAuditService $service): JsonResponse
    {
        $diasOt = max(1, (int) $request->input('dias_ot', 30));

        return response()->json($service->audit($diasOt));
    }
}
