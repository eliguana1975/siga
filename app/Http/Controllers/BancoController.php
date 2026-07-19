<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BancoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $this->validateBanco($request);

        Banco::create($validated);

        return redirect()
            ->route('admin.ajustes.index')
            ->with('success', 'Banco agregado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $banco = Banco::findOrFail($id);
        $validated = $this->validateBanco($request, $banco);

        $banco->update($validated);

        return redirect()
            ->route('admin.ajustes.index')
            ->with('success', 'Banco actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        Banco::findOrFail($id)->delete();

        return redirect()
            ->route('admin.ajustes.index')
            ->with('success', 'Banco eliminado correctamente.');
    }

    private function validateBanco(Request $request, ?Banco $banco = null): array
    {
        $request->merge([
            'nombre' => mb_strtoupper(trim((string) $request->input('nombre')), 'UTF-8'),
        ]);

        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('bancos', 'nombre')->ignore($banco?->id),
            ],
            'activo' => ['nullable', 'boolean'],
        ]) + [
            'activo' => $request->boolean('activo'),
        ];
    }
}
