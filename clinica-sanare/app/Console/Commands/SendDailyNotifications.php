<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendDailyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía notificaciones automáticas diarias a los usuarios';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('📢 Enviando notificaciones diarias...');

        // 1. Notificar citas del día a médicos/enfermeras
        $this->info('📋 Notificando citas del día...');
        $notificationService->notifyAppointmentsToday();

        // 2. Notificar stock bajo a inventario
        $this->info('⚠️ Verificando stock bajo...');
        $notificationService->notifyLowStock();

        $this->info('✅ Notificaciones diarias enviadas correctamente.');
    }
}