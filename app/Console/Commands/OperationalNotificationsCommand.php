<?php

namespace App\Console\Commands;

use App\Support\OperationalNotificationService;
use Illuminate\Console\Command;

class OperationalNotificationsCommand extends Command
{
    protected $signature = 'siga:notificaciones-operativas {--limit=50 : Cantidad maxima por tipo de alerta}';

    protected $description = 'Sincroniza la bandeja de notificaciones operativas';

    public function handle(OperationalNotificationService $service): int
    {
        $result = $service->sync(max(1, (int) $this->option('limit')));

        $this->info('Notificaciones operativas sincronizadas.');
        $this->table(['Creadas', 'Actualizadas', 'Activas'], [[$result['created'], $result['updated'], $result['active']]]);

        return self::SUCCESS;
    }
}
