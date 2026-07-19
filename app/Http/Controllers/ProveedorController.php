<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\Proveedor;
use App\Models\Provincia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.permission:administrar-proveedores');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $query = Proveedor::with(['provincia', 'ciudad'])->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('telefono', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('direccion', 'like', '%' . $search . '%')
                    ->orWhere('codigo_postal', 'like', '%' . $search . '%')
                    ->orWhere('contacto', 'like', '%' . $search . '%')
                    ->orWhere('forma_pago_preferida', 'like', '%' . $search . '%')
                    ->orWhere('condicion_pago_dias', 'like', '%' . $search . '%')
                    ->orWhere('datos_pago', 'like', '%' . $search . '%')
                    ->orWhere('impuestos', 'like', '%' . $search . '%')
                    ->orWhereHas('provincia', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('ciudad', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    });
            });
        }

        $proveedores = $query->paginate(5)->withQueryString();
        $provincias = Provincia::orderBy('nombre')->get();
        $ciudades = Ciudad::orderBy('nombre')->get();

        return view('admin.proveedores.index', compact('proveedores', 'provincias', 'ciudades', 'search'));
    }

    public function store(Request $request)
    {
        $request->merge($this->normalizedInput($request));

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return redirect()
                ->route('admin.proveedores.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'createProveedorModal');
        }

        Proveedor::create($validator->validated());

        return redirect()
            ->route('admin.proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $request->merge($this->normalizedInput($request));

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return redirect()
                ->route('admin.proveedores.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'editProveedorModal-' . $proveedor->id);
        }

        $proveedor->update($validator->validated());

        return redirect()
            ->route('admin.proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        return redirect()
            ->route('admin.proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }

    private function normalizedInput(Request $request): array
    {
        return [
            'provincia_id' => $request->filled('provincia_id') ? $request->input('provincia_id') : null,
            'ciudades_id' => $request->filled('ciudades_id') ? $request->input('ciudades_id') : null,
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
            'telefono' => trim((string) $request->input('telefono')),
            'email' => trim((string) $request->input('email')),
            'direccion' => trim((string) $request->input('direccion')),
            'codigo_postal' => trim((string) $request->input('codigo_postal')),
            'contacto' => trim((string) $request->input('contacto')),
            'forma_pago_preferida' => $request->filled('forma_pago_preferida') ? $request->input('forma_pago_preferida') : null,
            'condicion_pago_dias' => $this->normalizePaymentTerm($request->input('condicion_pago_dias')),
            'datos_pago' => trim((string) $request->input('datos_pago')),
            'impuestos' => $this->normalizeImpuestos($this->normalizeImpuestoPercentages($request->input('impuestos', []))),
            'notas' => trim((string) $request->input('notas')),
        ];
    }

    private function rules(): array
    {
        return [
            'provincia_id' => ['nullable', 'integer', 'exists:provincias,id'],
            'ciudades_id' => ['nullable', 'integer', 'exists:ciudades,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'contacto' => ['nullable', 'string', 'max:150'],
            'forma_pago_preferida' => ['nullable', 'in:' . implode(',', array_keys(Proveedor::formasPago()))],
            'condicion_pago_dias' => ['nullable', 'string', 'max:80', 'regex:/^\d+(?:-\d+)*$/'],
            'datos_pago' => ['nullable', 'string'],
            'impuestos' => ['nullable', 'array'],
            'impuestos.*.nombre' => ['nullable', 'string', 'max:120'],
            'impuestos.*.porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'impuestos.*.descripcion' => ['nullable', 'string', 'max:255'],
            'impuestos.*.activo' => ['nullable', 'boolean'],
            'notas' => ['nullable', 'string'],
        ];
    }

    private function normalizeImpuestos(array $impuestos): array
    {
        return collect($impuestos)
            ->map(function (array $impuesto) {
                $nombre = trim((string) ($impuesto['nombre'] ?? ''));

                if ($nombre === '') {
                    return null;
                }

                return [
                    'nombre' => $nombre,
                    'porcentaje' => isset($impuesto['porcentaje']) && $impuesto['porcentaje'] !== ''
                        ? round((float) $impuesto['porcentaje'], 4)
                        : 0,
                    'descripcion' => trim((string) ($impuesto['descripcion'] ?? '')),
                    'activo' => (bool) ($impuesto['activo'] ?? false),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeImpuestoPercentages(mixed $impuestos): array
    {
        if (! is_array($impuestos)) {
            return [];
        }

        return collect($impuestos)
            ->map(function ($impuesto) {
                if (! is_array($impuesto)) {
                    return [];
                }

                if (isset($impuesto['porcentaje'])) {
                    $impuesto['porcentaje'] = str_replace(',', '.', (string) $impuesto['porcentaje']);
                }

                return $impuesto;
            })
            ->all();
    }

    private function normalizePaymentTerm(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return preg_replace('/\s+/', '', $value);
    }
}
