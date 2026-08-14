<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Inventory;
use App\Models\Announcement;
use App\Models\Appointment;

class NotificationService
{
    /**
     * Enviar notificación a un usuario específico
     */
    public function sendToUser($userId, $type, $title, $message, $link = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => false
        ]);
    }

    /**
     * Enviar notificación a todos los médicos y enfermeras
     */
    public function sendToMedicalStaff($type, $title, $message, $link = null)
    {
        $users = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['medico', 'enfermera']);
        })->get();

        foreach ($users as $user) {
            $this->sendToUser($user->id, $type, $title, $message, $link);
        }
    }

    /**
     * Enviar notificación a todos los usuarios con un rol específico
     */
    public function sendToRole($roleName, $type, $title, $message, $link = null)
    {
        $users = User::whereHas('roles', function($q) use ($roleName) {
            $q->where('name', $roleName);
        })->get();

        foreach ($users as $user) {
            $this->sendToUser($user->id, $type, $title, $message, $link);
        }
    }

    /**
     * Enviar notificación a todos los usuarios (excepto admin)
     */
    public function sendToAll($type, $title, $message, $link = null)
    {
        $users = User::where('id', '!=', 1)->get();

        foreach ($users as $user) {
            $this->sendToUser($user->id, $type, $title, $message, $link);
        }
    }

    /**
     * Notificar nueva cita a un médico
     */
    public function notifyNewAppointment($appointment)
    {
        // Obtener todos los médicos de la cita (múltiples servicios)
        $doctorIds = $appointment->appointmentServices()->pluck('doctor_id')->unique();
        
        foreach ($doctorIds as $doctorId) {
            $doctor = Doctor::find($doctorId);
            if (!$doctor) continue;

            $this->sendToUser(
                $doctor->user_id,
                'new_appointment',
                '📅 Nueva Cita Registrada',
                "Se ha registrado una nueva cita para el paciente {$appointment->patient->full_name} el {$appointment->appointment_date->format('d/m/Y')} a las " . \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A'),
                route('appointments.show', $appointment)
            );
        }
    }

    /**
     * Notificar citas del día a los médicos
     */
    public function notifyAppointmentsToday()
    {
        $doctors = Doctor::with('user')->get();

        foreach ($doctors as $doctor) {
            $appointmentsToday = Appointment::whereHas('appointmentServices', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            })
            ->whereDate('appointment_date', today())
            ->where('status', '!=', 'cancelada')
            ->count();

            if ($appointmentsToday > 0) {
                $this->sendToUser(
                    $doctor->user_id,
                    'appointment_today',
                    '📋 Citas de Hoy',
                    "Tienes {$appointmentsToday} cita(s) programada(s) para hoy.",
                    route('appointments.index', ['date' => today()->format('Y-m-d')])
                );
            }
        }
    }

    /**
     * Notificar stock bajo al inventario
     */
    public function notifyLowStock()
    {
        $lowStockItems = Inventory::whereRaw('quantity <= min_stock')
            ->where('is_active', true)
            ->get();

        if ($lowStockItems->count() > 0) {
            $itemsList = $lowStockItems->pluck('name')->implode(', ');

            $this->sendToRole(
                'inventario',
                'low_stock',
                '⚠️ Stock Bajo',
                "Los siguientes artículos tienen stock bajo: {$itemsList}",
                route('inventory.index', ['low_stock' => 1])
            );
        }
    }

    /**
     * Notificar nuevo anuncio a todos los usuarios
     */
    public function notifyNewAnnouncement($announcement)
    {
        $this->sendToAll(
            'announcement',
            '📢 ' . $announcement->title,
            $announcement->description,
            route('dashboard') . '#anuncios'
        );
    }

    /**
     * Notificar recordatorio de cita (1 hora antes)
     */
    public function notifyAppointmentReminder($appointment)
    {
        $doctorIds = $appointment->appointmentServices()->pluck('doctor_id')->unique();
        
        foreach ($doctorIds as $doctorId) {
            $doctor = Doctor::find($doctorId);
            if (!$doctor) continue;

            $this->sendToUser(
                $doctor->user_id,
                'appointment_reminder',
                '⏰ Recordatorio de Cita',
                "Tienes una cita con {$appointment->patient->full_name} en 1 hora.",
                route('appointments.show', $appointment)
            );
        }
    }
}