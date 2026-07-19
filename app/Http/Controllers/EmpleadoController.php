<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-empleados');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $query = Empleado::with(['usuario', 'base'])->orderBy('apellidos')->orderBy('nombres');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombres', 'like', '%' . $search . '%')
                    ->orWhere('apellidos', 'like', '%' . $search . '%')
                    ->orWhere('tipo_empleado', 'like', '%' . $search . '%')
                    ->orWhere('turno_laboral', 'like', '%' . $search . '%')
                    ->orWhere('numero_doc', 'like', '%' . $search . '%')
                    ->orWhere('telefono', 'like', '%' . $search . '%')
                    ->orWhere('categoria_carnet_conducir', 'like', '%' . $search . '%')
                    ->orWhere('estado', 'like', '%' . $search . '%')
                    ->orWhereHas('usuario', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('base', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    });
            });
        }

        $empleados = $query->paginate(5)->withQueryString();
        $usuarios = User::orderBy('name')->get();
        $bases = Base::where('estado', 'activa')->orderBy('nombre')->get();
        $empleadosActivos = Empleado::query()
            ->where('estado', 'activo')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos', 'tipo_empleado']);

        return view('admin.empleados.index', compact('empleados', 'usuarios', 'bases', 'search', 'empleadosActivos'));
    }

    public function store(Request $request)
    {
        $request->merge($this->normalizedInput($request));

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return redirect()
                ->route('admin.empleados.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createEmpleadoModal');
        }

        Empleado::create($validator->validated());

        return redirect()
            ->route('admin.empleados.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $empleado = Empleado::findOrFail($id);

        $request->merge($this->normalizedInput($request));

        $validator = Validator::make($request->all(), $this->rules($empleado->id));

        if ($validator->fails()) {
            return redirect()
                ->route('admin.empleados.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editEmpleadoModal-' . $empleado->id);
        }

        $empleado->update($validator->validated());

        return redirect()
            ->route('admin.empleados.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();

        return redirect()
            ->route('admin.empleados.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }

    private function normalizedInput(Request $request): array
    {
        return [
            'usuario_id' => $request->filled('usuario_id') ? $request->input('usuario_id') : null,
            'base_id' => $request->filled('base_id') ? $request->input('base_id') : null,
            'nombres' => mb_strtoupper(trim((string) $request->input('nombres')), 'UTF-8'),
            'apellidos' => mb_strtoupper(trim((string) $request->input('apellidos')), 'UTF-8'),
            'tipo_empleado' => ($tipoEmpleado = mb_strtoupper(trim((string) $request->input('tipo_empleado')), 'UTF-8')) !== '' ? $tipoEmpleado : null,
            'turno_laboral' => $request->filled('turno_laboral') ? $request->input('turno_laboral') : null,
            'es_franquero' => $request->boolean('es_franquero'),
            'franquero_de_tipo_empleado' => ($franqueroDeTipo = mb_strtoupper(trim((string) $request->input('franquero_de_tipo_empleado')), 'UTF-8')) !== '' ? $franqueroDeTipo : null,
            'franquero_de_empleado_id' => $request->filled('franquero_de_empleado_id') ? $request->input('franquero_de_empleado_id') : null,
            'tipo_doc' => mb_strtoupper(trim((string) $request->input('tipo_doc')), 'UTF-8'),
            'numero_doc' => trim((string) $request->input('numero_doc')),
            'telefono' => trim((string) $request->input('telefono')),
            'direccion' => trim((string) $request->input('direccion')),
            'fecha_nacimiento' => $request->filled('fecha_nacimiento') ? $request->input('fecha_nacimiento') : null,
            'categoria_carnet_conducir' => mb_strtoupper(trim((string) $request->input('categoria_carnet_conducir')), 'UTF-8'),
            'vencimiento_carnet_conducir' => $request->filled('vencimiento_carnet_conducir') ? $request->input('vencimiento_carnet_conducir') : null,
            'vencimiento_linti' => $request->filled('vencimiento_linti') ? $request->input('vencimiento_linti') : null,
            'estado' => $request->input('estado', 'activo'),
        ];
    }

    private function rules(?int $empleadoId = null): array
    {
        return [
            'usuario_id' => ['nullable', 'integer', 'exists:users,id'],
            'base_id' => ['nullable', 'integer', 'exists:bases,id'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'tipo_empleado' => ['nullable', 'string', 'max:100'],
            'turno_laboral' => ['nullable', Rule::in(['manana', 'tarde', 'noche'])],
            'es_franquero' => ['required', 'boolean'],
            'franquero_de_tipo_empleado' => ['nullable', 'string', 'max:100'],
            'franquero_de_empleado_id' => ['nullable', 'integer', 'exists:empleados,id', Rule::notIn([$empleadoId])],
            'tipo_doc' => ['required', Rule::in(['CI', 'DNI'])],
            'numero_doc' => ['required', 'string', 'max:50', Rule::unique('empleados', 'numero_doc')->ignore($empleadoId)],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'categoria_carnet_conducir' => ['required', 'string', 'max:50'],
            'vencimiento_carnet_conducir' => ['required', 'date'],
            'vencimiento_linti' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
        ];
    }
}
