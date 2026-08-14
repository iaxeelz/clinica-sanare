<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\DoctorUnavailableSlot;
use App\Models\DoctorBlockedDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DoctorScheduleController extends Controller
{
    /**
     * Mostrar la vista de horarios del médico
     */
    public function index()
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        
        if (!$doctor) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $schedules = DoctorSchedule::where('doctor_id', $doctor->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('doctors.schedules', compact('schedules'));
    }

    /**
     * Guardar un nuevo horario
     */
    public function store(Request $request)
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Médico no encontrado'], 404);
        }

        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'sometimes|boolean'
        ]);

        // Verificar que no exista un horario para el mismo día
        $exists = DoctorSchedule::where('doctor_id', $doctor->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un horario para este día'
            ], 422);
        }

        $schedule = DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_active' => $request->has('is_active')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Horario creado exitosamente',
            'schedule' => $schedule
        ]);
    }

    /**
     * Actualizar un horario
     */
    public function update(Request $request, $id)
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Médico no encontrado'], 404);
        }

        $schedule = DoctorSchedule::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'Horario no encontrado'], 404);
        }

        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'sometimes|boolean'
        ]);

        // Verificar duplicado (excluyendo el actual)
        $exists = DoctorSchedule::where('doctor_id', $doctor->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un horario para este día'
            ], 422);
        }

        $schedule->update([
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_active' => $request->has('is_active')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Horario actualizado exitosamente'
        ]);
    }

    /**
     * Eliminar un horario
     */
    public function destroy($id)
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Médico no encontrado'], 404);
        }

        $schedule = DoctorSchedule::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'Horario no encontrado'], 404);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Horario eliminado exitosamente'
        ]);
    }

    /**
     * Obtener horarios disponibles de un médico para una fecha específica
     * Excluye citas existentes, horas bloqueadas y días bloqueados
     */
    public function getAvailableSlots(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $date = $request->get('date');

        if (!$doctorId || !$date) {
            return response()->json(['slots' => []]);
        }

        // Verificar si el día está bloqueado
        $dayBlocked = DoctorBlockedDay::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('is_active', true)
            ->exists();

        if ($dayBlocked) {
            return response()->json(['slots' => [], 'blocked_day' => true]);
        }

        $dayOfWeek = date('N', strtotime($date));

        // Obtener horario del médico para ese día
        $schedule = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return response()->json(['slots' => []]);
        }

        // Obtener citas existentes para ese día
        $appointments = Appointment::whereHas('appointmentServices', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId);
        })
        ->where('appointment_date', $date)
        ->where('status', '!=', 'cancelada')
        ->get();

        // Obtener horas bloqueadas para ese día
        $blockedSlots = DoctorUnavailableSlot::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('is_active', true)
            ->get();

        // Generar slots de 30 minutos
        $slots = [];
        $start = strtotime($schedule->start_time);
        $end = strtotime($schedule->end_time);
        $interval = 30;

        while ($start < $end) {
            $time = date('H:i', $start);
            $isAvailable = true;

            // Verificar citas existentes
            foreach ($appointments as $appointment) {
                $appointmentTime = strtotime($appointment->appointment_time);
                if ($appointmentTime == $start) {
                    $isAvailable = false;
                    break;
                }
            }

            // Verificar horas bloqueadas
            if ($isAvailable) {
                foreach ($blockedSlots as $blocked) {
                    $blockStart = strtotime($blocked->start_time);
                    $blockEnd = strtotime($blocked->end_time);
                    if ($start >= $blockStart && $start < $blockEnd) {
                        $isAvailable = false;
                        break;
                    }
                }
            }

            if ($isAvailable) {
                $slots[] = $time;
            }

            $start += $interval * 60;
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * Obtener días disponibles de un médico
     */
    public function getAvailableDays(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        
        if (!$doctorId) {
            return response()->json(['days' => []]);
        }

        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->get();

        // Mapear días de la semana
        $dayMap = [
            1 => 'Lunes',
            2 => 'Martes', 
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        
        $availableDays = [];
        foreach ($schedules as $schedule) {
            $availableDays[] = $dayMap[$schedule->day_of_week];
        }

        return response()->json(['days' => $availableDays]);
    }

    /**
     * Obtener horas bloqueadas de un médico para una fecha
     */
    public function getUnavailableSlots(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $date = $request->get('date');

        if (!$doctorId || !$date) {
            return response()->json(['slots' => []]);
        }

        $slots = DoctorUnavailableSlot::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('is_active', true)
            ->get()
            ->map(function($slot) {
                return [
                    'start' => $slot->start_time,
                    'end' => $slot->end_time,
                    'reason' => $slot->reason,
                    'id' => $slot->id
                ];
            });

        return response()->json(['slots' => $slots]);
    }

    /**
     * Guardar una hora bloqueada
     */
    public function storeUnavailableSlot(Request $request)
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Médico no encontrado'], 404);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:255'
        ]);

        // Verificar que no haya conflicto con horarios existentes
        $exists = DoctorUnavailableSlot::where('doctor_id', $doctor->id)
            ->where('date', $validated['date'])
            ->where('start_time', $validated['start_time'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un bloqueo para esta hora'
            ], 422);
        }

        $slot = DoctorUnavailableSlot::create([
            'doctor_id' => $doctor->id,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'reason' => $validated['reason'] ?? null,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hora bloqueada exitosamente',
            'slot' => $slot
        ]);
    }

    /**
     * Eliminar una hora bloqueada
     */
    public function deleteUnavailableSlot($id)
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Médico no encontrado'], 404);
        }

        $slot = DoctorUnavailableSlot::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$slot) {
            return response()->json(['success' => false, 'message' => 'Bloqueo no encontrado'], 404);
        }

        $slot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bloqueo eliminado exitosamente'
        ]);
    }

    // ============================================
    // MÉTODOS PARA DÍAS BLOQUEADOS
    // ============================================

    /**
     * Obtener días bloqueados del médico
     */
    public function getBlockedDays(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        
        if (!$doctorId) {
            return response()->json(['days' => []]);
        }

        $blockedDays = DoctorBlockedDay::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->pluck('date')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();

        return response()->json(['days' => $blockedDays]);
    }

    /**
     * Verificar si un día está bloqueado
     */
    public function checkDayBlocked(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $date = $request->get('date');

        if (!$doctorId || !$date) {
            return response()->json(['blocked' => false]);
        }

        $blocked = DoctorBlockedDay::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('is_active', true)
            ->exists();

        return response()->json(['blocked' => $blocked]);
    }

    /**
     * Bloquear un día completo
     */
    public function blockDay(Request $request)
    {
        try {
            $doctor = Doctor::where('user_id', Auth::id())->first();
            
            if (!$doctor) {
                return response()->json(['success' => false, 'message' => 'Médico no encontrado'], 404);
            }

            $validated = $request->validate([
                'date' => 'required|date',
                'reason' => 'nullable|string|max:255'
            ]);

            // Verificar si ya existe
            $exists = DoctorBlockedDay::where('doctor_id', $doctor->id)
                ->where('date', $validated['date'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este día ya está bloqueado'
                ], 422);
            }

            $blocked = DoctorBlockedDay::create([
                'doctor_id' => $doctor->id,
                'date' => $validated['date'],
                'reason' => $validated['reason'] ?? null,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Día bloqueado exitosamente',
                'blocked' => $blocked
            ]);
        } catch (\Exception $e) {
            Log::error('Error al bloquear día: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al bloquear el día: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desbloquear un día
     */
    public function unblockDay(Request $request)
    {
        try {
            $doctor = Doctor::where('user_id', Auth::id())->first();
            
            if (!$doctor) {
                return response()->json(['success' => false, 'message' => 'Médico no encontrado'], 404);
            }

            $validated = $request->validate([
                'date' => 'required|date'
            ]);

            $blocked = DoctorBlockedDay::where('doctor_id', $doctor->id)
                ->where('date', $validated['date'])
                ->first();

            if (!$blocked) {
                return response()->json(['success' => false, 'message' => 'Este día no está bloqueado'], 404);
            }

            $blocked->delete();

            return response()->json([
                'success' => true,
                'message' => 'Día desbloqueado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al desbloquear día: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al desbloquear el día: ' . $e->getMessage()
            ], 500);
        }
    }
}