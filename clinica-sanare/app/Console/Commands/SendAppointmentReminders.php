<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de citas próximas a los médicos';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('⏰ Enviando recordatorios de citas...');

        // Obtener citas que empiezan en 1 hora (margen de 5 minutos)
        $now = Carbon::now();
        $targetTime = $now->copy()->addHour();
        $startRange = $targetTime->copy()->subMinutes(5);
        $endRange = $targetTime->copy()->addMinutes(5);

        // Buscar citas que tengan programada su hora de inicio dentro del rango
        $appointments = Appointment::with(['patient', 'appointmentServices.doctor'])
            ->where('status', '!=', 'cancelada')
            ->where('is_active', true)
            ->whereDate('appointment_date', $now->toDateString())
            ->whereTime('appointment_time', '>=', $startRange->format('H:i:s'))
            ->whereTime('appointment_time', '<=', $endRange->format('H:i:s'))
            ->get();

        $count = 0;

        foreach ($appointments as $appointment) {
            // Obtener todos los médicos de la cita
            $doctorIds = $appointment->appointmentServices()->pluck('doctor_id')->unique();

            foreach ($doctorIds as $doctorId) {
                $doctor = Doctor::find($doctorId);
                if (!$doctor || !$doctor->user_id) continue;

                // Enviar recordatorio al médico
                $notificationService->sendToUser(
                    $doctor->user_id,
                    'appointment_reminder',
                    '⏰ Recordatorio de Cita',
                    "Tienes una cita con {$appointment->patient->full_name} en 1 hora.\n\n📅 {$appointment->appointment_date->format('d/m/Y')} a las " . Carbon::parse($appointment->appointment_time)->format('h:i A'),
                    route('appointments.show', $appointment)
                );

                $count++;
                $this->info("✅ Recordatorio enviado al médico: {$doctor->full_name}");
            }
        }

        $this->info("📨 Se enviaron {$count} recordatorio(s).");
    }
}