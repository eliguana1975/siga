@once
    @push('scripts')
        <script>
            function printTransferenciaDeposito(templateId) {
                const template = document.getElementById(templateId);

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
                        .transfer-repeat-heading th,
                        .transfer-repeat-footer td {
                            background: #fff;
                        }
                        .transfer-repeat-heading .sheet-grid {
                            margin: 0;
                        }
                        .sheet-observations,
                        .sheet-summary {
                            margin: 8px 0 16px;
                            padding: 8px;
                            border: 1px solid #111827;
                        }
                        .sheet-observations strong,
                        .sheet-summary strong {
                            display: inline-block;
                            margin-right: 4px;
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
                        @page {
                            size: A4 portrait;
                            margin: 10mm;
                        }
                    </style>
                `;

                const printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>Transferencia entre depositos</title>' + style + '<style>' + window.sigaPrintCompanyStyles() + '</style></head><body>');
                printWindow.document.write(window.sigaPrintCompanyHeader());
                printWindow.document.write(template.innerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }
        </script>
    @endpush
@endonce
