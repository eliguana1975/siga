@extends('layouts.admin')

@section('content')
    <div class="page-heading d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <h3>Manual de Usuario SIGA</h3>
            <p class="text-subtitle text-muted mb-0">
                Consulta operativa de los modulos y procesos del sistema.
            </p>
        </div>
        <div class="d-flex gap-2 align-items-start">
            <button type="button" class="btn btn-light-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="page-content">
        <section class="section">
            <div class="row g-3">
                <div class="col-12 col-xl-3">
                    <div class="card manual-index-card">
                        <div class="card-header pb-2">
                            <h4 class="card-title mb-0">Indice</h4>
                            <small class="text-muted">
                                Actualizado {{ \Illuminate\Support\Carbon::createFromTimestamp($updatedAt)->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="card-body pt-2">
                            <div class="manual-index">
                                @foreach ($sections as $section)
                                    <a href="#{{ $section['id'] }}" class="manual-index-link level-{{ $section['level'] }}">
                                        {{ $section['title'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-9">
                    <div class="card">
                        <div class="card-body">
                            <article class="manual-reader">
                                {!! $manualHtml !!}
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .manual-index-card {
            position: sticky;
            top: 92px;
        }

        .manual-index {
            display: flex;
            flex-direction: column;
            gap: .2rem;
            max-height: calc(100vh - 190px);
            overflow-y: auto;
            padding-right: .25rem;
        }

        .manual-index-link {
            border-left: 2px solid transparent;
            color: #cfd3e3;
            display: block;
            font-size: .9rem;
            line-height: 1.25;
            padding: .38rem .55rem;
            text-decoration: none;
        }

        .manual-index-link:hover,
        .manual-index-link:focus {
            background-color: #2d2d44;
            border-left-color: #435ebe;
            color: #fff;
        }

        .manual-index-link.level-2 {
            padding-left: 1rem;
        }

        .manual-index-link.level-3 {
            color: #aeb4d2;
            font-size: .84rem;
            padding-left: 1.45rem;
        }

        .manual-reader {
            color: #dce0f5;
            font-size: .98rem;
            line-height: 1.7;
            max-width: 920px;
        }

        .manual-reader h1,
        .manual-reader h2,
        .manual-reader h3 {
            color: #f5f6ff;
            font-weight: 800;
            letter-spacing: 0;
            scroll-margin-top: 96px;
        }

        .manual-reader h1 {
            border-bottom: 1px solid #3d3d58;
            font-size: 1.65rem;
            margin-bottom: 1rem;
            padding-bottom: .8rem;
        }

        .manual-reader h2 {
            border-top: 1px solid #33334d;
            font-size: 1.25rem;
            margin-top: 2rem;
            padding-top: 1.2rem;
        }

        .manual-reader h3 {
            font-size: 1.05rem;
            margin-top: 1.35rem;
        }

        .manual-reader p,
        .manual-reader li {
            color: #dce0f5;
        }

        .manual-reader ul,
        .manual-reader ol {
            padding-left: 1.25rem;
        }

        .manual-reader li + li {
            margin-top: .22rem;
        }

        .manual-reader strong {
            color: #fff;
        }

        .manual-reader code {
            border: 1px solid #35354f;
            border-radius: 4px;
            padding: .08rem .28rem;
        }

        .manual-reader table {
            color: #dce0f5;
            margin: 1rem 0;
            width: 100%;
        }

        .manual-reader th,
        .manual-reader td {
            border: 1px solid #3d3d58;
            padding: .55rem .7rem;
        }

        .manual-reader th {
            background-color: #242438;
            color: #fff;
        }

        @media (max-width: 1199px) {
            .manual-index-card {
                position: static;
            }

            .manual-index {
                max-height: 300px;
            }
        }

        @media print {
            .manual-index-card,
            .siga-main-toolbar,
            #sidebar,
            .page-heading .btn {
                display: none !important;
            }

            #main {
                margin-left: 0 !important;
                max-width: none !important;
                width: 100% !important;
            }

            .card {
                border: 0 !important;
                box-shadow: none !important;
            }

            .manual-reader {
                color: #111827 !important;
                max-width: none;
            }

            .manual-reader * {
                color: #111827 !important;
            }
        }
    </style>
@endpush
