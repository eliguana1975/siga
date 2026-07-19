@extends('layouts.admin')

@php
    $articulo = $inventario->articulo;
    $deposito = $inventario->deposito;
    $codigo = $articulo?->codigo_producto ?: 'Sin codigo';
    $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=8&data=' . urlencode($qrUrl);
    $barcodeImageUrl = \App\Support\Code39Barcode::svgDataUri($codigo);
@endphp

@push('styles')
    <style>
        .label-page {
            display: grid;
            gap: 1rem;
            justify-items: start;
        }

        .label-print-status {
            margin: 1rem;
            color: #6c757d;
            font-size: .9rem;
        }

        .product-label {
            box-sizing: border-box;
            width: 100mm;
            height: 38mm;
            padding: 3mm;
            overflow: hidden;
            color: #111827;
            background: #fff;
            border: 1px solid #111827;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }

        .product-label .product-label__company {
            height: 6mm !important;
            padding-bottom: .8mm !important;
            margin-bottom: 1.2mm !important;
            border-bottom: 1px solid #111827 !important;
            text-align: center !important;
            overflow: hidden !important;
        }

        .product-label .product-label__company h1 {
            margin: 0 !important;
            color: #111827 !important;
            font-size: 9px !important;
            line-height: 1 !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .product-label .product-label__company p {
            margin: .4mm 0 0 !important;
            color: #374151 !important;
            font-size: 4.8px !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .product-label .product-label__body {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 24mm;
            grid-template-rows: auto 1fr;
            gap: 2.5mm;
            align-items: start;
            height: 24.8mm;
            overflow: hidden;
        }

        .product-label .product-label__name {
            display: block !important;
            min-height: 0 !important;
            max-height: 8mm !important;
            margin: 0 !important;
            color: #111827 !important;
            font-size: 10px !important;
            line-height: 1.08 !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            overflow-wrap: anywhere !important;
            white-space: normal !important;
            overflow: visible !important;
        }

        .product-label__details {
            min-width: 0;
        }

        .product-label__row {
            display: flex;
            gap: 1.5mm;
            margin-bottom: .8mm;
            font-size: 6.5px;
            line-height: 1.1;
            min-width: 0;
        }

        .product-label__row span {
            flex: 0 0 15mm;
            color: #4b5563;
            font-weight: 700;
            text-transform: uppercase;
        }

        .product-label__row strong {
            min-width: 0;
            color: #111827;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-label__qr {
            grid-row: 1 / span 2;
            grid-column: 2;
            display: grid;
            justify-items: center;
            align-content: start;
            overflow: hidden;
        }

        .product-label__qr img {
            width: 22mm;
            height: 22mm;
            image-rendering: pixelated;
        }

        .product-label__qr small {
            display: block;
            max-width: 30mm;
            color: #4b5563;
            font-size: 5.5px;
            line-height: 1.1;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .product-label__barcode {
            grid-column: 1;
            min-width: 0;
            overflow: hidden;
        }

        .product-label__barcode img {
            display: block;
            width: 100%;
            height: 10mm;
            object-fit: fill;
        }

        @media print {
            @page {
                size: 106mm 44mm;
                margin: 3mm;
            }

            html,
            body {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                background: #fff !important;
            }

            body * {
                visibility: hidden;
            }

            .product-label,
            .product-label * {
                visibility: visible;
            }

            #sidebar,
            .siga-main-toolbar,
            .page-heading,
            .no-print {
                display: none !important;
            }

            #main,
            .page-content,
            .section,
            .label-page {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .product-label {
                position: absolute;
                left: 0;
                top: 0;
                box-shadow: none !important;
                break-inside: avoid;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="label-print-status no-print">Preparando etiqueta para imprimir...</div>
        <section class="section label-page">
            <div class="product-label">
                <div class="product-label__company">
                    <h1>{{ $empresa['nombre'] }}</h1>
                    @if (!empty($empresa['descripcion']) || !empty($empresa['localidad']))
                        <p>{{ collect([$empresa['descripcion'] ?? null, $empresa['localidad'] ?? null])->filter()->implode(' - ') }}</p>
                    @endif
                </div>

                <div class="product-label__body">
                    <h2 class="product-label__name">{{ $articulo?->nombre ?? 'Articulo sin nombre' }}</h2>
                    <div class="product-label__details">
                        <div class="product-label__row">
                            <span>Codigo</span>
                            <strong>{{ $codigo }}</strong>
                        </div>
                        <div class="product-label__row">
                            <span>Deposito</span>
                            <strong>{{ $deposito?->nombre ?? 'N/A' }}</strong>
                        </div>
                        <div class="product-label__row">
                            <span>Ubicacion</span>
                            <strong>{{ $ubicacion }}</strong>
                        </div>
                    </div>

                    <div class="product-label__qr">
                        <img src="{{ $qrImageUrl }}" alt="QR {{ $articulo?->nombre ?? 'articulo' }}">
                    </div>

                    <div class="product-label__barcode">
                        <img src="{{ $barcodeImageUrl }}" alt="Codigo de barras {{ $codigo }}">
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qrImage = document.querySelector('.product-label__qr img');
            let printed = false;

            function printLabel() {
                if (printed) {
                    return;
                }

                printed = true;
                window.focus();
                window.print();
            }

            if (qrImage && !qrImage.complete) {
                qrImage.addEventListener('load', printLabel, { once: true });
                qrImage.addEventListener('error', printLabel, { once: true });
                window.setTimeout(printLabel, 1800);
                return;
            }

            window.setTimeout(printLabel, 150);
        });
    </script>
@endpush
