<?php

namespace App\Http\Controllers;

use App\Models\OrdenTrabajoMotivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class OrdenTrabajoMotivoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = OrdenTrabajoMotivo::query()
            ->orderByDesc('activo')
            ->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('codigo', 'like', '%' . $search . '%');
            });
        }

        $motivos = $query->paginate(10)->withQueryString();

        return view('admin.ordenes-trabajo-motivos.index', compact('motivos', 'search'));
    }

    public function store(Request $request)
    {
        $data = $this->normalizedInput($request);

        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:orden_trabajo_motivos,codigo'],
            'activo' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ordenes-trabajo-motivos.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createMotivoModal');
        }

        $validated = $validator->validated();
        $validated['codigo'] = $validated['codigo'] ?: Str::slug($validated['nombre']);
        $validated['activo'] = (bool) ($validated['activo'] ?? false);

        OrdenTrabajoMotivo::query()->create($validated);

        return redirect()
            ->route('admin.ordenes-trabajo-motivos.index')
            ->with('success', 'Motivo creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $motivo = OrdenTrabajoMotivo::query()->findOrFail($id);
        $data = $this->normalizedInput($request);

        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:50', Rule::unique('orden_trabajo_motivos', 'codigo')->ignore($motivo->id)],
            'activo' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.ordenes-trabajo-motivos.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editMotivoModal-' . $motivo->id);
        }

        $validated = $validator->validated();
        $validated['codigo'] = $validated['codigo'] ?: Str::slug($validated['nombre']);
        $validated['activo'] = (bool) ($validated['activo'] ?? false);

        $motivo->update($validated);

        return redirect()
            ->route('admin.ordenes-trabajo-motivos.index')
            ->with('success', 'Motivo actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $motivo = OrdenTrabajoMotivo::query()->withCount('ordenesTrabajo')->findOrFail($id);

        if ($motivo->ordenes_trabajo_count > 0) {
            return redirect()
                ->route('admin.ordenes-trabajo-motivos.index')
                ->with('error', 'No se puede eliminar un motivo usado en ordenes de trabajo. Puede dejarlo inactivo.');
        }

        $motivo->delete();

        return redirect()
            ->route('admin.ordenes-trabajo-motivos.index')
            ->with('success', 'Motivo eliminado correctamente.');
    }

    private function normalizedInput(Request $request): array
    {
        $nombre = trim((string) $request->input('nombre'));
        $codigo = trim((string) $request->input('codigo'));

        return [
            'nombre' => mb_strtoupper($nombre, 'UTF-8'),
            'codigo' => $codigo !== '' ? Str::slug($codigo) : null,
            'activo' => $request->boolean('activo'),
        ];
    }
}
