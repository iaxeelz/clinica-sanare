<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\NotificationController; // <--- NUEVO
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ============================================
// DESHABILITAR REGISTRO PÚBLICO DE USUARIOS
// ============================================
Route::get('/register', function () {
    return redirect()->route('login');
})->name('register');

Route::post('/register', function () {
    return redirect()->route('login');
});

// ============================================
// DESHABILITAR RECUPERACIÓN DE CONTRASEÑA
// ============================================
Route::get('/forgot-password', function () {
    return redirect()->route('login');
})->name('password.request');

Route::post('/forgot-password', function () {
    return redirect()->route('login');
})->name('password.email');

Route::get('/reset-password/{token}', function () {
    return redirect()->route('login');
})->name('password.reset');

Route::post('/reset-password', function () {
    return redirect()->route('login');
})->name('password.update');

// ============================================
// RUTAS PROTEGIDAS POR AUTENTICACIÓN
// ============================================
Route::middleware(['auth'])->group(function () {
    // Dashboard - todos los roles pueden verlo
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view_dashboard');

    // === RUTAS ESPECÍFICAS ===

    // Calendario de Citas (ANTES de appointments resource)
    Route::get('/appointments/calendar', [AppointmentController::class, 'calendar'])
        ->name('appointments.calendar')
        ->middleware('permission:view_calendar|view_all_appointments|view_own_appointments');

    // Flujo de Caja
    Route::get('/incomes/cash-flow', [IncomeController::class, 'cashFlow'])
        ->name('incomes.cash-flow')
        ->middleware('permission:view_finances|view_cash_flow');

    // === REPORTES ===
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index')
        ->middleware('permission:view_reports');

    Route::get('/reports/doctor', [ReportController::class, 'doctorReport'])
        ->name('reports.doctor')
        ->middleware('permission:view_doctor_reports|view_reports');

    Route::get('/reports/service', [ReportController::class, 'serviceReport'])
        ->name('reports.service')
        ->middleware('permission:view_service_reports|view_reports');

    Route::get('/reports/patient', [ReportController::class, 'patientReport'])
        ->name('reports.patient')
        ->middleware('permission:view_reports');

    Route::get('/reports/financial', [ReportController::class, 'financialReport'])
        ->name('reports.financial')
        ->middleware('permission:view_finances|view_reports');

    // === EXPORTACIONES DE REPORTES ===
    Route::get('/reports/doctor/export/excel', [ReportController::class, 'exportDoctorExcel'])
        ->name('reports.doctor.export.excel')
        ->middleware('permission:view_doctor_reports|view_reports');

    Route::get('/reports/doctor/export/pdf', [ReportController::class, 'exportDoctorPdf'])
        ->name('reports.doctor.export.pdf')
        ->middleware('permission:view_doctor_reports|view_reports');

    Route::get('/reports/financial/export/excel', [ReportController::class, 'exportFinancialExcel'])
        ->name('reports.financial.export.excel')
        ->middleware('permission:view_finances|view_reports');

    Route::get('/reports/financial/export/pdf', [ReportController::class, 'exportFinancialPdf'])
        ->name('reports.financial.export.pdf')
        ->middleware('permission:view_finances|view_reports');

    // === ADMINISTRACIÓN (solo admin) ===
    Route::middleware(['permission:manage_users'])->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware(['permission:manage_roles'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    // === SERVICIOS ===
    Route::get('/services', [ServiceController::class, 'index'])
        ->name('services.index')
        ->middleware('permission:view_services');

    Route::get('/services/create', [ServiceController::class, 'create'])
        ->name('services.create')
        ->middleware('permission:create_services');

    Route::post('/services', [ServiceController::class, 'store'])
        ->name('services.store')
        ->middleware('permission:create_services');

    Route::get('/services/{service}', [ServiceController::class, 'show'])
        ->name('services.show')
        ->middleware('permission:view_services');

    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])
        ->name('services.edit')
        ->middleware('permission:edit_services');

    Route::put('/services/{service}', [ServiceController::class, 'update'])
        ->name('services.update')
        ->middleware('permission:edit_services');

    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
        ->name('services.destroy')
        ->middleware('permission:delete_services');

    // === PACIENTES ===
    Route::get('/patients', [PatientController::class, 'index'])
        ->name('patients.index')
        ->middleware('permission:view_patients');

    Route::get('/patients/create', [PatientController::class, 'create'])
        ->name('patients.create')
        ->middleware('permission:create_patients');

    Route::post('/patients', [PatientController::class, 'store'])
        ->name('patients.store')
        ->middleware('permission:create_patients');

    Route::get('/patients/{patient}', [PatientController::class, 'show'])
        ->name('patients.show')
        ->middleware('permission:view_patients');

    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])
        ->name('patients.edit')
        ->middleware('permission:edit_patients');

    Route::put('/patients/{patient}', [PatientController::class, 'update'])
        ->name('patients.update')
        ->middleware('permission:edit_patients');

    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])
        ->name('patients.destroy')
        ->middleware('permission:delete_patients');

    // === MÉDICOS ===
    Route::get('/doctors', [DoctorController::class, 'index'])
        ->name('doctors.index')
        ->middleware('permission:view_doctors');

    Route::get('/doctors/create', [DoctorController::class, 'create'])
        ->name('doctors.create')
        ->middleware('permission:create_doctors');

    Route::post('/doctors', [DoctorController::class, 'store'])
        ->name('doctors.store')
        ->middleware('permission:create_doctors');

    Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])
        ->name('doctors.show')
        ->middleware('permission:view_doctors');

    Route::get('/doctors/{doctor}/edit', [DoctorController::class, 'edit'])
        ->name('doctors.edit')
        ->middleware('permission:edit_doctors');

    Route::put('/doctors/{doctor}', [DoctorController::class, 'update'])
        ->name('doctors.update')
        ->middleware('permission:edit_doctors');

    Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy'])
        ->name('doctors.destroy')
        ->middleware('permission:delete_doctors');

    // ============================================
    // RUTAS PARA MÚLTIPLES SERVICIOS
    // ============================================

    // Obtener médicos por servicio (para el formulario de citas)
    Route::get('/doctors/by-service', [DoctorController::class, 'getByService'])
        ->name('doctors.by-service')
        ->middleware('permission:create_appointments|view_appointments|view_doctors');

    // NUEVA RUTA - USANDO APPOINTMENTCONTROLLER
    Route::get('/get-doctors-by-service', [AppointmentController::class, 'getDoctorsByService'])
        ->name('get.doctors.by.service')
        ->middleware('permission:create_appointments|view_appointments|view_doctors');

    // Obtener servicios de un médico (API)
    Route::get('/doctors/{doctor}/services', [DoctorController::class, 'getDoctorServices'])
        ->name('doctors.services')
        ->middleware('permission:view_doctors|view_appointments');

    // ============================================
    // RUTAS PARA HORARIOS DE MÉDICOS
    // ============================================
    Route::get('/doctor/schedules', [DoctorScheduleController::class, 'index'])
        ->name('doctor.schedules')
        ->middleware('permission:view_schedules');

    Route::post('/doctor/schedules', [DoctorScheduleController::class, 'store'])
        ->name('doctor.schedules.store')
        ->middleware('permission:create_schedules');

    Route::put('/doctor/schedules/{id}', [DoctorScheduleController::class, 'update'])
        ->name('doctor.schedules.update')
        ->middleware('permission:edit_schedules');

    Route::delete('/doctor/schedules/{id}', [DoctorScheduleController::class, 'destroy'])
        ->name('doctor.schedules.destroy')
        ->middleware('permission:delete_schedules');

    Route::get('/doctor/slots', [DoctorScheduleController::class, 'getAvailableSlots'])
        ->name('doctor.slots')
        ->middleware('permission:view_schedules|view_appointments');

    // ============================================
    // RUTA PARA OBTENER DÍAS DISPONIBLES
    // ============================================
    Route::get('/doctor/available-days', [DoctorScheduleController::class, 'getAvailableDays'])
        ->name('doctor.available-days')
        ->middleware('permission:view_schedules|view_appointments');

    // ============================================
    // RUTAS PARA BLOQUEAR HORAS
    // ============================================
    Route::get('/doctor/unavailable-slots', [DoctorScheduleController::class, 'getUnavailableSlots'])
        ->name('doctor.unavailable-slots')
        ->middleware('permission:view_schedules|view_appointments');

    Route::post('/doctor/unavailable-slots', [DoctorScheduleController::class, 'storeUnavailableSlot'])
        ->name('doctor.unavailable-slots.store')
        ->middleware('permission:create_schedules');

    Route::delete('/doctor/unavailable-slots/{id}', [DoctorScheduleController::class, 'deleteUnavailableSlot'])
        ->name('doctor.unavailable-slots.delete')
        ->middleware('permission:delete_schedules');

    // ============================================
    // RUTAS PARA DÍAS BLOQUEADOS
    // ============================================
    Route::get('/doctor/blocked-days', [DoctorScheduleController::class, 'getBlockedDays'])
        ->name('doctor.blocked-days')
        ->middleware('permission:view_schedules|view_appointments');

    Route::get('/doctor/check-day-blocked', [DoctorScheduleController::class, 'checkDayBlocked'])
        ->name('doctor.check-day-blocked')
        ->middleware('permission:view_schedules|view_appointments');

    Route::post('/doctor/block-day', [DoctorScheduleController::class, 'blockDay'])
        ->name('doctor.block-day')
        ->middleware('permission:view_schedules|view_appointments');

    Route::post('/doctor/unblock-day', [DoctorScheduleController::class, 'unblockDay'])
        ->name('doctor.unblock-day')
        ->middleware('permission:view_schedules|view_appointments');

    // ============================================
    // RUTAS PARA NOTIFICACIONES (NUEVAS)
    // ============================================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/destroy-all', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');

    // === CITAS ===
    // PRIMERO: Rutas específicas (sin parámetros)
    Route::get('/appointments/calendar', [AppointmentController::class, 'calendar'])
        ->name('appointments.calendar')
        ->middleware('permission:view_calendar|view_all_appointments|view_own_appointments');

    Route::get('/appointments/events', [AppointmentController::class, 'getEvents'])
        ->name('appointments.events')
        ->middleware('permission:view_calendar|view_all_appointments|view_own_appointments');

    // SEGUNDO: Rutas con permisos específicos
    Route::get('/appointments', [AppointmentController::class, 'index'])
        ->name('appointments.index')
        ->middleware('permission:view_all_appointments|view_own_appointments');

    Route::get('/appointments/create', [AppointmentController::class, 'create'])
        ->name('appointments.create')
        ->middleware('permission:create_appointments');

    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->name('appointments.store')
        ->middleware('permission:create_appointments');

    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])
        ->name('appointments.show')
        ->middleware('permission:view_all_appointments|view_own_appointments');

    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])
        ->name('appointments.edit')
        ->middleware('permission:edit_all_appointments|edit_own_appointments|edit_appointments');

    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])
        ->name('appointments.update')
        ->middleware('permission:edit_all_appointments|edit_own_appointments|edit_appointments');

    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])
        ->name('appointments.destroy')
        ->middleware('permission:delete_all_appointments|delete_own_appointments');

    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'changeStatus'])
        ->name('appointments.status')
        ->middleware('permission:change_appointment_status');

    // === PAGOS DE CITAS ===
    Route::post('/appointments/{appointment}/mark-paid', [AppointmentController::class, 'markAsPaid'])->name('appointments.mark-paid');
    Route::get('/appointments/{appointment}/payment-status', [AppointmentController::class, 'getPaymentStatus'])->name('appointments.payment-status');

    // === INVENTARIO ===
    Route::get('/inventory', [InventoryController::class, 'index'])
        ->name('inventory.index')
        ->middleware('permission:view_inventory');

    Route::get('/inventory/create', [InventoryController::class, 'create'])
        ->name('inventory.create')
        ->middleware('permission:create_inventory');

    Route::post('/inventory', [InventoryController::class, 'store'])
        ->name('inventory.store')
        ->middleware('permission:create_inventory');

    Route::get('/inventory/{inventory}', [InventoryController::class, 'show'])
        ->name('inventory.show')
        ->middleware('permission:view_inventory');

    Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])
        ->name('inventory.edit')
        ->middleware('permission:edit_inventory');

    Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])
        ->name('inventory.update')
        ->middleware('permission:edit_inventory');

    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])
        ->name('inventory.destroy')
        ->middleware('permission:delete_inventory');

    Route::post('/inventory/{inventory}/adjust-stock', [InventoryController::class, 'adjustStock'])
        ->name('inventory.adjust-stock')
        ->middleware('permission:edit_inventory');

    // === INGRESOS ===
    Route::get('/incomes', [IncomeController::class, 'index'])
        ->name('incomes.index')
        ->middleware('permission:view_incomes|view_finances');

    Route::get('/incomes/create', [IncomeController::class, 'create'])
        ->name('incomes.create')
        ->middleware('permission:create_incomes');

    Route::post('/incomes', [IncomeController::class, 'store'])
        ->name('incomes.store')
        ->middleware('permission:create_incomes');

    Route::get('/incomes/{income}', [IncomeController::class, 'show'])
        ->name('incomes.show')
        ->middleware('permission:view_incomes|view_finances');

    Route::get('/incomes/{income}/edit', [IncomeController::class, 'edit'])
        ->name('incomes.edit')
        ->middleware('permission:edit_incomes');

    Route::put('/incomes/{income}', [IncomeController::class, 'update'])
        ->name('incomes.update')
        ->middleware('permission:edit_incomes');

    Route::delete('/incomes/{income}', [IncomeController::class, 'destroy'])
        ->name('incomes.destroy')
        ->middleware('permission:delete_incomes');

    // === EGRESOS ===
    Route::get('/expenses', [ExpenseController::class, 'index'])
        ->name('expenses.index')
        ->middleware('permission:view_expenses|view_finances');

    Route::get('/expenses/create', [ExpenseController::class, 'create'])
        ->name('expenses.create')
        ->middleware('permission:create_expenses');

    Route::post('/expenses', [ExpenseController::class, 'store'])
        ->name('expenses.store')
        ->middleware('permission:create_expenses');

    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])
        ->name('expenses.show')
        ->middleware('permission:view_expenses|view_finances');

    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])
        ->name('expenses.edit')
        ->middleware('permission:edit_expenses');

    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])
        ->name('expenses.update')
        ->middleware('permission:edit_expenses');

    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->name('expenses.destroy')
        ->middleware('permission:delete_expenses');

    // === ANUNCIOS ===
    Route::resource('announcements', AnnouncementController::class)->except(['index', 'show']);
    Route::post('/announcements/{announcement}/toggle', [AnnouncementController::class, 'toggleStatus'])->name('announcements.toggle');

    // === NOTIFICACIONES (API) ===
    Route::get('/notificaciones', function () {
        return response()->json([
            [
                'message' => 'Nueva cita registrada',
                'icon' => 'fas fa-calendar-plus',
                'color' => 'text-success',
                'time' => 'Hace 5 min'
            ],
            [
                'message' => 'Paciente Juan Pérez tiene cita en 30 min',
                'icon' => 'fas fa-clock',
                'color' => 'text-warning',
                'time' => 'Hace 10 min'
            ]
        ]);
    })->name('notificaciones');
});

require __DIR__ . '/auth.php';