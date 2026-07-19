<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraPago;
use App\Models\Ajuste;
use App\Models\Banco;
use App\Support\CompraImpuestos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompraPagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(string $compraId)
    {
        $compra = Compra::with([
            'deposito',
            'proveedor',
            'pedidoArticulo',
            'usuario',
            'detalles.articulo.unidadMedida',
            'detalles.proveedor',
            'pagos.bancoSeleccionado',
            'pagos.proveedor',
            'pagos.usuario',
        ])->findOrFail($compraId);

        $impuestosPago = CompraImpuestos::disponiblesParaPago($compra);
        $proveedorPago = $this->proveedorParaPago($compra);
        $bancos = Banco::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        $ajusteEmpresa = Ajuste::query()->first(['nombre', 'cuit']);

        return view('admin.ordenes-compra.pagos', compact('compra', 'impuestosPago', 'proveedorPago', 'bancos', 'ajusteEmpresa'));
    }

    public function store(Request $request, string $compraId)
    {
        if ($request->filled('importe')) {
            $request->merge([
                'importe' => $this->normalizeDecimal($request->input('importe')),
            ]);
        }

        if ($request->filled('porcentaje_pago')) {
            $request->merge([
                'porcentaje_pago' => $this->normalizeDecimal($request->input('porcentaje_pago')),
            ]);
        }

        $request->merge([
            'impuestos_pago' => $this->normalizeTaxPercentages($request->input('impuestos_pago', [])),
        ]);

        $compra = Compra::with(['proveedor', 'detalles.proveedor'])->findOrFail($compraId);
        $proveedorPago = $this->proveedorParaPago($compra);
        $saldoPendiente = round((float) $compra->saldoPendienteConImpuestos(), 2);

        if ($saldoPendiente <= 0) {
            return redirect()
                ->route('admin.ordenes-compra.pagos.create', $compra->id)
                ->with('info', 'Esta orden ya tiene registrado el pago total.');
        }

        $validated = $request->validate([
            'forma_pago' => ['required', 'in:' . implode(',', array_keys(Compra::formasPago()))],
            'tipo_pago' => ['required', 'in:total,parcial'],
            'porcentaje_pago' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'importe' => ['required', 'numeric', 'min:0.01'],
            'impuestos_pago' => ['nullable', 'array'],
            'impuestos_pago.*.aplicar' => ['nullable', 'boolean'],
            'impuestos_pago.*.nombre' => ['nullable', 'string', 'max:120'],
            'impuestos_pago.*.porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'impuestos_pago.*.origen' => ['nullable', 'string', 'max:80'],
            'fecha_pago' => ['nullable', 'date'],
            'nro_cheque' => ['nullable', 'string', 'max:120'],
            'nros_cheques' => ['nullable', 'array'],
            'nros_cheques.*' => ['nullable', 'string', 'max:120'],
            'tipo_cheque' => ['nullable', 'in:fisico,e_check,terceros'],
            'banco_id' => ['nullable', 'exists:bancos,id'],
            'titular_cheque' => ['nullable', 'string', 'max:180'],
            'cuit_librador' => ['nullable', 'string', 'max:30'],
            'nro_cuenta_cheque' => ['nullable', 'string', 'max:80'],
            'nro_operacion_cheque' => ['nullable', 'string', 'max:120'],
            'fecha_emision_cheque' => ['nullable', 'date'],
            'fecha_vencimiento_cheque' => ['nullable', 'date'],
            'plazo_pago' => ['nullable', 'string', 'max:80'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $totalPago = (float) $validated['importe'];

        if ($validated['tipo_pago'] === 'total') {
            $validated['porcentaje_pago'] = null;
            $totalPago = $compra->saldoPendienteConImpuestos();
        } elseif (! empty($validated['porcentaje_pago'])) {
            $totalPago = round($compra->saldoPendienteConImpuestos() * (float) $validated['porcentaje_pago'] / 100, 2);
        }

        $taxSummary = $this->calculateTaxesFromTotal($totalPago, CompraImpuestos::disponiblesParaPago($compra));
        unset($validated['impuestos_pago']);

        if (in_array($validated['forma_pago'], ['cheque', 'e_check'], true)) {
            $banco = ! empty($validated['banco_id'])
                ? Banco::query()->find($validated['banco_id'])
                : null;
            $validated['banco'] = $banco?->nombre;
            $validated['fecha_emision_cheque'] = $validated['fecha_pago'] ?? now()->toDateString();
            $validated['plazo_pago'] = $validated['plazo_pago']
                ?: ($proveedorPago?->condicion_pago_dias ?: $this->inferPaymentTerm($proveedorPago?->datos_pago));

            $vencimientos = $this->calculateDueDates(
                $validated['fecha_emision_cheque'],
                $validated['plazo_pago'] ?? null
            );

            if (! empty($vencimientos)) {
                $validated['vencimientos_pago'] = $vencimientos;
                $validated['fecha_vencimiento_cheque'] = $vencimientos[0];
            }

            if (in_array($validated['tipo_cheque'] ?? null, ['fisico', 'terceros'], true)) {
                $numerosCheques = collect($validated['nros_cheques'] ?? [])
                    ->map(fn ($numero) => trim((string) $numero))
                    ->filter()
                    ->values()
                    ->all();

                $validated['nros_cheques'] = $numerosCheques;
                $validated['nro_cheque'] = implode(' / ', $numerosCheques);
                $validated['nro_operacion_cheque'] = null;
            } else {
                $validated['nros_cheques'] = null;
            }
        } else {
            $validated['nro_cheque'] = null;
            $validated['nros_cheques'] = null;
            $validated['tipo_cheque'] = null;
            $validated['banco_id'] = null;
            $validated['banco'] = null;
            $validated['titular_cheque'] = null;
            $validated['cuit_librador'] = null;
            $validated['nro_cuenta_cheque'] = null;
            $validated['nro_operacion_cheque'] = null;
            $validated['fecha_emision_cheque'] = null;
            $validated['fecha_vencimiento_cheque'] = null;
            $validated['plazo_pago'] = null;
            $validated['vencimientos_pago'] = null;
        }

        CompraPago::create([
            ...$validated,
            'compra_id' => $compra->id,
            'proveedor_id' => $proveedorPago?->id,
            'usuario_id' => Auth::id(),
            'importe_base' => $taxSummary['base'],
            'importe_impuestos' => $taxSummary['total_impuestos'],
            'impuestos_aplicados' => $taxSummary['impuestos'],
            'importe' => $taxSummary['total'],
            'fecha_pago' => $validated['fecha_pago'] ?? now()->toDateString(),
        ]);

        return redirect()
            ->route('admin.ordenes-compra.pagos.create', $compra->id)
            ->with('success', 'Pago registrado correctamente.');
    }

    public function comprobante(string $compraId, string $pagoId)
    {
        $compra = Compra::with([
            'deposito',
            'proveedor',
            'pedidoArticulo',
            'usuario',
            'detalles.articulo.unidadMedida',
            'detalles.proveedor',
        ])->findOrFail($compraId);

        $pago = CompraPago::query()
            ->with(['bancoSeleccionado', 'proveedor', 'usuario'])
            ->where('compra_id', $compra->id)
            ->findOrFail($pagoId);

        return view('admin.ordenes-compra.comprobante-pago', compact('compra', 'pago'));
    }

    public function updateComprobante(Request $request, string $compraId, string $pagoId)
    {
        $compra = Compra::findOrFail($compraId);

        $pago = CompraPago::query()
            ->where('compra_id', $compra->id)
            ->findOrFail($pagoId);

        $validated = $request->validate([
            'nro_recibo' => ['nullable', 'string', 'max:120'],
            'nro_comprobante_pago' => ['nullable', 'string', 'max:120'],
            'nro_transferencia' => ['nullable', 'string', 'max:120'],
            'fecha_comprobante_pago' => ['nullable', 'date'],
            'observaciones_comprobante' => ['nullable', 'string'],
        ]);

        $pago->update($validated);

        return redirect()
            ->route('admin.ordenes-compra.pagos.create', $compra->id)
            ->with('success', 'Comprobante de pago actualizado correctamente.');
    }

    public function destroy(string $compraId, string $pagoId)
    {
        $compra = Compra::findOrFail($compraId);

        CompraPago::query()
            ->where('compra_id', $compra->id)
            ->findOrFail($pagoId)
            ->delete();

        return redirect()
            ->route('admin.ordenes-compra.pagos.create', $compra->id)
            ->with('success', 'Pago eliminado correctamente.');
    }

    private function normalizeDecimal(mixed $value): string
    {
        $value = trim((string) $value);

        if (str_contains($value, ',')) {
            return str_replace(',', '.', str_replace('.', '', $value));
        }

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
            return str_replace('.', '', $value);
        }

        return $value;
    }

    private function proveedorParaPago(Compra $compra)
    {
        return $compra->proveedor
            ?: $compra->detalles->pluck('proveedor')->filter()->unique('id')->first();
    }

    private function calculateDueDates(?string $issueDate, ?string $paymentTerm): array
    {
        if (! $issueDate || $paymentTerm === null || $paymentTerm === '') {
            return [];
        }

        try {
            $baseDate = Carbon::parse($issueDate);
        } catch (\Throwable) {
            return [];
        }

        return collect(explode('-', $paymentTerm))
            ->map(fn ($days) => trim($days))
            ->filter(fn ($days) => preg_match('/^\d+$/', $days))
            ->map(fn ($days) => $baseDate->copy()->addDays((int) $days)->toDateString())
            ->values()
            ->all();
    }

    private function inferPaymentTerm(?string $paymentData): string
    {
        $text = mb_strtolower((string) $paymentData, 'UTF-8');
        $patterns = [
            '/(\d+\s*(?:-\s*\d+\s*){1,4})\s*(?:dias|días)?/',
            '/(?:a|de)?\s*(\d+)\s*(?:dias|días)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return preg_replace('/\s+/', '', $matches[1]) ?: '';
            }
        }

        return '';
    }

    private function calculateTaxes(float $baseAmount, array $taxes): array
    {
        $appliedTaxes = collect($taxes)
            ->filter(fn ($tax) => ! empty($tax['aplicar']) && trim((string) ($tax['nombre'] ?? '')) !== '')
            ->map(function ($tax) use ($baseAmount) {
                $percentage = (float) ($tax['porcentaje'] ?? 0);
                $amount = round($baseAmount * $percentage / 100, 2);

                return [
                    'nombre' => trim((string) $tax['nombre']),
                    'porcentaje' => $percentage,
                    'importe' => $amount,
                    'origen' => trim((string) ($tax['origen'] ?? '')),
                ];
            })
            ->values()
            ->all();

        $totalTaxes = round((float) collect($appliedTaxes)->sum('importe'), 2);

        return [
            'base' => round($baseAmount, 2),
            'total_impuestos' => $totalTaxes,
            'total' => round($baseAmount + $totalTaxes, 2),
            'impuestos' => $appliedTaxes,
        ];
    }

    private function calculateTaxesFromTotal(float $totalAmount, array $taxes): array
    {
        $totalRate = collect($taxes)
            ->filter(fn ($tax) => trim((string) ($tax['nombre'] ?? '')) !== '')
            ->sum(fn ($tax) => (float) ($tax['porcentaje'] ?? 0));

        $baseAmount = $totalRate > 0
            ? round($totalAmount / (1 + ($totalRate / 100)), 2)
            : round($totalAmount, 2);

        $summary = $this->calculateTaxes($baseAmount, collect($taxes)
            ->map(function ($tax) {
                $tax['aplicar'] = true;
                return $tax;
            })
            ->all());

        $difference = round($totalAmount - $summary['total'], 2);

        if ($difference !== 0.0) {
            $summary['base'] = round($summary['base'] + $difference, 2);
            $summary['total'] = round($totalAmount, 2);
        }

        return $summary;
    }

    private function normalizeTaxPercentages(mixed $taxes): array
    {
        if (! is_array($taxes)) {
            return [];
        }

        return collect($taxes)
            ->map(function ($tax) {
                if (! is_array($tax)) {
                    return [];
                }

                if (isset($tax['porcentaje'])) {
                    $tax['porcentaje'] = $this->normalizeDecimal($tax['porcentaje']);
                }

                return $tax;
            })
            ->all();
    }
}
