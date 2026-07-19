@once
    @push('scripts')
        <script>
            function printReparacionTemplate(templateId, title, options) {
                const template = document.getElementById(templateId);
                const settings = options || {};
                const includeCompanyHeader = settings.includeCompanyHeader !== false;

                if (!template) {
                    return;
                }

                const style = `
                    <style>
                        * { box-sizing: border-box; }
                        body {
                            margin: 0;
                            padding: 14px;
                            color: #111827;
                            font-family: Arial, Helvetica, sans-serif;
                            font-size: 11px;
                            line-height: 1.25;
                        }
                        h1 {
                            margin: 0 0 4px;
                            text-align: center;
                            color: #25396f;
                            font-size: 18px;
                        }
                        .sheet-subtitle {
                            margin: 0 0 18px;
                            text-align: center;
                            color: #4b5563;
                        }
                        .sheet-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 8px 18px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 12px;
                        }
                        thead { display: table-header-group; }
                        tfoot { display: table-footer-group; }
                        th,
                        td {
                            border: 1px solid #111827;
                            padding: 5px 6px;
                            text-align: left;
                            vertical-align: top;
                        }
                        tr {
                            page-break-inside: avoid;
                            break-inside: avoid;
                        }
                        th {
                            background: #f3f4f6;
                            font-weight: 700;
                        }
                        .repair-repeat-heading th,
                        .repair-repeat-footer td {
                            background: #fff;
                        }
                        .sheet-observations {
                            margin: 8px 0 16px;
                            padding: 8px;
                            border: 1px solid #111827;
                        }
                        .sheet-observations strong {
                            display: block;
                            margin-bottom: 4px;
                        }
                        .sheet-text {
                            margin: 8px 0 24px;
                        }
                        .signature-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 26px 28px;
                            margin-top: 34px;
                        }
                        .signature-line {
                            border-top: 1px solid #111827;
                            padding-top: 7px;
                            text-align: center;
                        }
                        .rotulos-grid {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 10px;
                        }
                        .rotulo-card {
                            border: 1px dashed #111827;
                            padding: 8px;
                            page-break-inside: avoid;
                            break-inside: avoid;
                            min-height: 150px;
                        }
                        .rotulo-head {
                            display: flex;
                            justify-content: flex-start;
                            align-items: baseline;
                            margin-bottom: 6px;
                            border-bottom: 1px solid #d1d5db;
                            padding-bottom: 4px;
                        }
                        .rotulo-title {
                            font-size: 12px;
                            font-weight: 700;
                            color: #25396f;
                        }
                        .rotulo-item {
                            margin: 2px 0;
                        }
                        .rotulo-item strong {
                            display: inline-block;
                            min-width: 74px;
                        }
                        @page {
                            size: A4 portrait;
                            margin: 10mm;
                        }
                    </style>
                `;

                const printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>' + title + '</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style></head><body>');
                if (includeCompanyHeader) {
                    printWindow.document.write(window.sigaPrintCompanyHeader());
                }
                printWindow.document.write(template.innerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }

            function printReparacionPlanilla(templateId) {
                printReparacionTemplate(templateId, 'Planilla reparacion articulos');
            }

            function printReparacionReclamo(templateId) {
                printReparacionTemplate(templateId, 'Planilla reclamo reparacion');
            }

            function printReparacionRotulos(templateId) {
                printReparacionTemplate(templateId, 'Rotulos envio reparacion', { includeCompanyHeader: false });
            }
        </script>
    @endpush
@endonce
