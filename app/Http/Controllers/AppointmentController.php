<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Income;
use App\Services\NotificationService; // <--- NUEVO
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $date = $request->get('date');

        $query = Appointment::with(['patient', 'doctor.user', 'service', 'appointmentServices.service', 'appointmentServices.doctor.user']);

        // Si es médico o enfermera, solo ver sus propias citas (a través de appointment_services)
        if (auth()->user()->hasRole(['medico', 'enfermera'])) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if ($doctor) {
                $query->whereHas('appointmentServices', function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Aplicar filtros
        $query->when($search, function ($q, $search) {
            return $q->search($search);
        })
            ->when($status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($date, function ($q, $date) {
                return $q->whereDate('appointment_date', $date);
            })
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'asc');

        $appointments = $query->paginate(15)->withQueryString();

        $statuses = ['pendiente', 'confirmada', 'en_curso', 'completada', 'cancelada'];

        return view('appointments.index', compact('appointments', 'search', 'status', 'date', 'statuses'));
    }

    public function create()
    {
        $patients = Patient::where('is_active', true)->orderBy('last_name')->get();
        $doctors = Doctor::with('user')->where('is_active', true)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('appointments.create', compact('patients', 'doctors', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|in:pendiente,confirmada,en_curso,completada,cancelada',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_paid' => 'sometimes|boolean',
            'payment_method' => 'nullable|in:efectivo,yape,tarjeta_culqi',
            // NUEVO: Múltiples servicios
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.doctor_id' => 'required|exists:doctors,id',
            'services.*.duration_minutes' => 'nullable|integer|min:5',
            'services.*.notes' => 'nullable|string',
        ]);

        // Verificar disponibilidad de TODOS los médicos
        $appointmentDate = $validated['appointment_date'];
        $appointmentTime = $validated['appointment_time'];
        $doctorIds = collect($validated['services'])->pluck('doctor_id')->unique();

        foreach ($doctorIds as $doctorId) {
            $exists = AppointmentService::where('doctor_id', $doctorId)
                ->whereHas('appointment', function ($q) use ($appointmentDate, $appointmentTime) {
                    $q->where('appointment_date', $appointmentDate)
                        ->where('appointment_time', $appointmentTime)
                        ->where('status', '!=', 'cancelada');
                })
                ->exists();

            if ($exists) {
                $doctor = Doctor::find($doctorId);
                return back()->withInput()
                    ->with('error', "El Dr. {$doctor->full_name} ya tiene una cita en ese horario.");
            }
        }

        $isPaid = $validated['is_paid'] ?? false;

        // Crear la cita principal (sin doctor_id ni service_id)
        $appointment = Appointment::create([
            'patient_id' => $validated['patient_id'],
            'user_id' => Auth::id(),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'status' => $validated['status'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
            'is_paid' => $isPaid,
            'paid_at' => $isPaid ? now() : null,
            'payment_method' => $isPaid ? ($validated['payment_method'] ?? 'efectivo') : null,
        ]);

        // Crear los servicios asociados
        $totalPrice = 0;
        foreach ($validated['services'] as $serviceData) {
            $service = Service::find($serviceData['service_id']);
            $duration = $serviceData['duration_minutes'] ?? $service->duration_minutes ?? 30;
            $price = $service->price;

            AppointmentService::create([
                'appointment_id' => $appointment->id,
                'service_id' => $serviceData['service_id'],
                'doctor_id' => $serviceData['doctor_id'],
                'price' => $price,
                'duration_minutes' => $duration,
                'notes' => $serviceData['notes'] ?? null,
            ]);

            $totalPrice += $price;
        }

        // ============================================
        // NOTIFICAR NUEVA CITA A LOS MÉDICOS
        // ============================================
        $notificationService = new NotificationService();
        $notificationService->notifyNewAppointment($appointment);

        // Si está pagado, crear ingreso en finanzas
        if ($appointment->is_paid) {
            $this->createIncomeFromAppointment($appointment);
        }

        return redirect()->route('appointments.index')
            ->with('success', "Cita registrada exitosamente con " . count($validated['services']) . " servicio(s).");
    }

    public function show(Appointment $appointment)
    {
        // Verificar si el médico o enfermera tiene acceso a esta cita
        if (auth()->user()->hasRole(['medico', 'enfermera'])) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if (!$doctor || !$appointment->appointmentServices()->where('doctor_id', $doctor->id)->exists()) {
                abort(403, 'No tienes permiso para ver esta cita.');
            }
        }

        $appointment->load([
            'patient',
            'user',
            'appointmentServices.service',
            'appointmentServices.doctor.user'
        ]);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        // Verificar si el médico o enfermera tiene acceso a esta cita
        if (auth()->user()->hasRole(['medico', 'enfermera'])) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if (!$doctor || !$appointment->appointmentServices()->where('doctor_id', $doctor->id)->exists()) {
                abort(403, 'No tienes permiso para editar esta cita.');
            }
        }

        $patients = Patient::where('is_active', true)->orderBy('last_name')->get();
        $doctors = Doctor::with('user')->where('is_active', true)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        $appointment->load(['appointmentServices.service', 'appointmentServices.doctor.user']);

        return view('appointments.edit', compact('appointment', 'patients', 'doctors', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        // Verificar si el médico o enfermera tiene acceso a esta cita
        if (auth()->user()->hasRole(['medico', 'enfermera'])) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if (!$doctor || !$appointment->appointmentServices()->where('doctor_id', $doctor->id)->exists()) {
                abort(403, 'No tienes permiso para editar esta cita.');
            }
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|in:pendiente,confirmada,en_curso,completada,cancelada',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_paid' => 'sometimes|boolean',
            'payment_method' => 'nullable|in:efectivo,yape,tarjeta_culqi',
            // NUEVO: Múltiples servicios
            'services' => 'required|array|min:1',
            'services.*.id' => 'nullable|exists:appointment_services,id',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.doctor_id' => 'required|exists:doctors,id',
            'services.*.duration_minutes' => 'nullable|integer|min:5',
            'services.*.notes' => 'nullable|string',
        ]);

        // Verificar disponibilidad de TODOS los médicos (excluyendo la cita actual)
        $appointmentDate = $validated['appointment_date'];
        $appointmentTime = $validated['appointment_time'];
        $doctorIds = collect($validated['services'])->pluck('doctor_id')->unique();

        foreach ($doctorIds as $doctorId) {
            $exists = AppointmentService::where('doctor_id', $doctorId)
                ->whereHas('appointment', function ($q) use ($appointmentDate, $appointmentTime, $appointment) {
                    $q->where('appointment_date', $appointmentDate)
                        ->where('appointment_time', $appointmentTime)
                        ->where('status', '!=', 'cancelada')
                        ->where('id', '!=', $appointment->id);
                })
                ->exists();

            if ($exists) {
                $doctor = Doctor::find($doctorId);
                return back()->withInput()
                    ->with('error', "El Dr. {$doctor->full_name} ya tiene una cita en ese horario.");
            }
        }

        $wasPaid = $appointment->is_paid;
        $isPaid = $validated['is_paid'] ?? false;

        // Actualizar la cita principal
        $appointment->update([
            'patient_id' => $validated['patient_id'],
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'status' => $validated['status'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_paid' => $isPaid,
            'paid_at' => $isPaid ? now() : null,
            'payment_method' => $isPaid ? ($validated['payment_method'] ?? 'efectivo') : null,
        ]);

        // Obtener IDs de servicios existentes
        $existingIds = $appointment->appointmentServices()->pluck('id')->toArray();
        $updatedIds = [];

        // Actualizar o crear servicios
        foreach ($validated['services'] as $serviceData) {
            $service = Service::find($serviceData['service_id']);
            $duration = $serviceData['duration_minutes'] ?? $service->duration_minutes ?? 30;
            $price = $service->price;

            if (isset($serviceData['id']) && in_array($serviceData['id'], $existingIds)) {
                // Actualizar existente
                $appointmentService = AppointmentService::find($serviceData['id']);
                if ($appointmentService) {
                    $appointmentService->update([
                        'service_id' => $serviceData['service_id'],
                        'doctor_id' => $serviceData['doctor_id'],
                        'price' => $price,
                        'duration_minutes' => $duration,
                        'notes' => $serviceData['notes'] ?? null,
                    ]);
                    $updatedIds[] = $appointmentService->id;
                }
            } else {
                // Crear nuevo
                $newService = AppointmentService::create([
                    'appointment_id' => $appointment->id,
                    'service_id' => $serviceData['service_id'],
                    'doctor_id' => $serviceData['doctor_id'],
                    'price' => $price,
                    'duration_minutes' => $duration,
                    'notes' => $serviceData['notes'] ?? null,
                ]);
                $updatedIds[] = $newService->id;
            }
        }

        // Eliminar servicios que ya no existen
        $toDelete = array_diff($existingIds, $updatedIds);
        if (!empty($toDelete)) {
            AppointmentService::whereIn('id', $toDelete)->delete();
        }

        // Si se marcó como pagado y antes no lo estaba, crear ingreso
        if ($isPaid && !$wasPaid) {
            $this->createIncomeFromAppointment($appointment);
        }

        return redirect()->route('appointments.index')
            ->with('success', 'Cita actualizada exitosamente con ' . count($validated['services']) . ' servicio(s).');
    }

    public function destroy(Appointment $appointment)
    {
        // Verificar si el usuario tiene permiso para eliminar citas
        if (!auth()->user()->can('delete_appointments') && !auth()->user()->can('delete_all_appointments')) {
            abort(403, 'No tienes permiso para eliminar citas.');
        }

        // Admin o Recepcionista pueden eliminar cualquier cita
        if (auth()->user()->hasRole(['admin', 'recepcionista'])) {
            // Eliminar servicios asociados
            $appointment->appointmentServices()->delete();
            $appointment->delete();
            return redirect()->route('appointments.index')
                ->with('success', 'Cita eliminada exitosamente.');
        }

        // Médico o Enfermera solo pueden eliminar sus propias citas
        if (auth()->user()->hasRole(['medico', 'enfermera'])) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if (!$doctor || !$appointment->appointmentServices()->where('doctor_id', $doctor->id)->exists()) {
                abort(403, 'No tienes permiso para eliminar esta cita.');
            }
            $appointment->appointmentServices()->delete();
            $appointment->delete();
            return redirect()->route('appointments.index')
                ->with('success', 'Cita eliminada exitosamente.');
        }

        abort(403, 'No tienes permiso para eliminar citas.');
    }

    // Vista del calendario
    public function calendar()
    {
        $doctors = Doctor::where('is_active', true)
            ->with('user')
            ->orderBy('id')
            ->get();

        return view('appointments.calendar', compact('doctors'));
    }

    public function getEvents(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $start = $request->get('start');
        $end = $request->get('end');
        $isDoctor = auth()->user()->hasRole(['medico', 'enfermera']);

        $query = Appointment::with([
            'patient',
            'appointmentServices.service',
            'appointmentServices.doctor.user'
        ])
        ->where('status', '!=', 'cancelada')
        ->where('is_active', true);

        if ($start && $end) {
            $startDate = date('Y-m-d', strtotime($start));
            $endDate = date('Y-m-d', strtotime($end));
            $query->whereBetween('appointment_date', [$startDate, $endDate]);
        }

        if ($isDoctor) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if ($doctor) {
                $query->whereHas('appointmentServices', function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                });
            } else {
                return response()->json([]);
            }
        } elseif ($doctorId) {
            $query->whereHas('appointmentServices', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            });
        }

        $appointments = $query->get();
        $events = [];

        foreach ($appointments as $appointment) {
            if ($appointment->appointmentServices->count() == 0) {
                continue;
            }

            // Usar la fecha directamente de appointment_date y la hora de appointment_time
            $startTime = \Carbon\Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . date('H:i:s', strtotime($appointment->appointment_time)));
            $totalDuration = $appointment->total_duration;
            $endTime = $startTime->copy()->addMinutes($totalDuration);

            $events[] = [
                'id' => $appointment->id,
                'title' => $appointment->patient->full_name . ' (' . $appointment->appointmentServices->count() . ' serv.)',
                'start' => $startTime->format('Y-m-d\TH:i:s'),
                'end' => $endTime->format('Y-m-d\TH:i:s'),
                'backgroundColor' => '#9b59b6',
                'borderColor' => '#8e44ad',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'appointment_id' => $appointment->id,
                    'patient' => $appointment->patient->full_name,
                    'services' => $appointment->appointmentServices->map(function ($s) {
                        return [
                            'service' => $s->service->name,
                            'doctor' => $s->doctor->full_name,
                            'duration' => $s->duration_minutes,
                            'price' => $s->price,
                        ];
                    }),
                    'total_duration' => $totalDuration,
                    'total_price' => $appointment->total_price,
                    'status' => $appointment->status,
                    'status_text' => $appointment->status_text,
                    'notes' => $appointment->notes,
                    'is_paid' => $appointment->is_paid,
                    'payment_status_text' => $appointment->payment_status_text,
                    'payment_status_color' => $appointment->payment_status_color,
                ]
            ];
        }

        return response()->json($events);
    }

    private function getColorByStatus($status)
    {
        return match ($status) {
            'pendiente' => '#ffc107',
            'confirmada' => '#17a2b8',
            'en_curso' => '#007bff',
            'completada' => '#28a745',
            'cancelada' => '#dc3545',
            default => '#6c757d'
        };
    }

    // Cambiar estado de cita (acción rápida)
    public function changeStatus(Request $request, Appointment $appointment)
    {
        // Verificar si el médico o enfermera tiene acceso a esta cita
        if (auth()->user()->hasRole(['medico', 'enfermera'])) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if (!$doctor || !$appointment->appointmentServices()->where('doctor_id', $doctor->id)->exists()) {
                abort(403, 'No tienes permiso para cambiar el estado de esta cita.');
            }
        }

        $request->validate([
            'status' => 'required|in:pendiente,confirmada,en_curso,completada,cancelada'
        ]);

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $appointment->status,
            'status_text' => $appointment->status_text
        ]);
    }

    /**
     * Marcar una cita como pagada y crear ingreso en finanzas (vía modal)
     */
    public function markAsPaid(Request $request, Appointment $appointment)
    {
        // Verificar que la cita no esté cancelada
        if ($appointment->status === 'cancelada') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede marcar como pagada una cita cancelada.'
            ], 400);
        }

        // Verificar que no esté ya pagada
        if ($appointment->is_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cita ya está pagada.'
            ], 400);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:efectivo,yape,tarjeta_culqi',
            'amount_paid' => 'required|numeric|min:0.01',
            'receipt_number' => 'nullable|string|max:50',
            'invoice_number' => 'nullable|string|max:50',
        ]);

        // Obtener el total de la cita
        $totalPrice = $appointment->total_price;
        $amount = $validated['amount_paid'];

        // Actualizar cita
        $appointment->update([
            'is_paid' => true,
            'paid_at' => now(),
            'payment_method' => $validated['payment_method'],
        ]);

        // Crear ingreso en finanzas
        $firstService = $appointment->appointmentServices->first();
        $service = $firstService?->service;

        $income = Income::create([
            'patient_id' => $appointment->patient_id,
            'service_id' => $firstService?->service_id ?? 1,
            'doctor_id' => $firstService?->doctor_id ?? 1,
            'cost_price' => $service?->cost ?? 0,
            'sale_price' => $totalPrice,
            'amount_paid' => $amount,
            'change_amount' => 0,
            'doctor_payment' => 0,
            'payment_method' => $validated['payment_method'],
            'receipt_number' => $validated['receipt_number'] ?? null,
            'invoice_number' => $validated['invoice_number'] ?? null,
            'description' => 'Pago de cita #' . $appointment->id . ' - ' . $appointment->services_list,
            'payment_date' => now()->format('Y-m-d'),
            'user_id' => Auth::id(),
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cita marcada como pagada. Ingreso registrado en finanzas.',
            'income_id' => $income->id,
            'appointment' => $appointment
        ]);
    }

    /**
     * Obtener el estado de pago de una cita
     */
    public function getPaymentStatus(Appointment $appointment)
    {
        return response()->json([
            'is_paid' => $appointment->is_paid,
            'paid_at' => $appointment->paid_at,
            'status_text' => $appointment->payment_status_text,
            'status_color' => $appointment->payment_status_color,
            'payment_method' => $appointment->payment_method,
            'payment_method_text' => $appointment->payment_method_text,
            'total_price' => $appointment->total_price,
            'services_count' => $appointment->services_count,
        ]);
    }

    /**
     * Crear ingreso en finanzas a partir de una cita pagada
     */
    private function createIncomeFromAppointment($appointment)
    {
        $firstService = $appointment->appointmentServices->first();
        $service = $firstService?->service;
        $totalPrice = $appointment->total_price;
        $paymentMethod = $appointment->payment_method ?? 'efectivo';

        Income::create([
            'patient_id' => $appointment->patient_id,
            'service_id' => $firstService?->service_id ?? 1,
            'doctor_id' => $firstService?->doctor_id ?? 1,
            'cost_price' => $service?->cost ?? 0,
            'sale_price' => $totalPrice,
            'amount_paid' => $totalPrice,
            'change_amount' => 0,
            'doctor_payment' => 0,
            'payment_method' => $paymentMethod,
            'description' => 'Pago de cita #' . $appointment->id . ' - ' . $appointment->services_list,
            'payment_date' => now()->format('Y-m-d'),
            'user_id' => Auth::id(),
            'is_active' => true
        ]);
    }

    /**
     * Obtener médicos disponibles para un servicio (API)
     */
    public function getDoctorsByService(Request $request)
    {
        $serviceId = $request->get('service_id');

        if (!$serviceId) {
            return response()->json(['doctors' => []]);
        }

        $doctors = Doctor::where('doctors.is_active', true)
            ->whereHas('services', function ($q) use ($serviceId) {
                $q->where('service_id', $serviceId)
                    ->where('doctor_services.is_active', true);
            })
            ->with('user')
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'full_name' => $doctor->full_name,
                    'specialty' => $doctor->specialty,
                    'consultation_fee' => $doctor->consultation_fee,
                ];
            });

        return response()->json(['doctors' => $doctors]);
    }
}