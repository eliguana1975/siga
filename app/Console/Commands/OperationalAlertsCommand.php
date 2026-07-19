<?php

namespace App\Console\Commands;

use App\Support\OperationalAlertService;
use Illuminate\Console\Command;

class OperationalAlertsCommand extends Command
{
    protected $signature = 'siga:alertas-operativas
                            {--limit=8 : Cantidad maxima de items por bloque}
                            {--json : Devuelve salida en formato JSON}';

    protected $description = 'Resume alertas operativas de stock, solicitudes y reparaciones demoradas';

    public function handle(OperationalAlertService $alerts): int
    {
        $summary = $alerts->summary(max(1, (int) $this->option('limit')));

        if ($this->option('json')) {
            $this->line((string) json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('Alertas operativas');
        $this->table(
            ['Tipo', 'Total'],
            [
                ['Stock critico', $summary['counts']['stock_critico']],
                ['Solicitudes demoradas', $summary['counts']['solicitudes_demoradas']],
                ['Reparaciones vencidas', $summary['counts']['reparaciones_vencidas']],
                ['Total', $summary['counts']['total']],
            ]
        );

        return self::SUCCESS;
    }
}
