<?php

namespace App\Console\Commands;

use App\Support\OperationalAuditService;
use Illuminate\Console\Command;

class AuditOperativoCommand extends Command
{
    protected $signature = 'siga:audit-operativo
                            {--dias-ot=30 : Dias para considerar OT antigua}
                            {--json : Devuelve salida en formato JSON}';

    protected $description = 'Audita inconsistencias operativas (stock, entregas y OT)';

    public function handle(OperationalAuditService $auditService): int
    {
        $diasOt = max(1, (int) $this->option('dias-ot'));
        $result = $auditService->audit($diasOt);
        $issues = $result['issues'];

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return empty($issues) ? self::SUCCESS : self::FAILURE;
        }

        if (empty($issues)) {
            $this->info('Auditoria operativa OK. No se detectaron inconsistencias.');

            return self::SUCCESS;
        }

        $this->warn('Se detectaron inconsistencias operativas:');
        $this->table(['Codigo', 'Severidad', 'Total', 'Detalle'], array_map(fn ($issue) => [$issue['codigo'], $issue['severidad'], $issue['total'], $issue['detalle']], $issues));

        return self::FAILURE;
    }
}
