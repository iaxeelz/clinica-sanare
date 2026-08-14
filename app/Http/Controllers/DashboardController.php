<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $announcements = Announcement::with('user')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $dashboardData = [];

        // Admin: ve todos los datos
        if ($user->hasRole('admin')) {
            $dashboardData = $this->getFullDashboardData($user);
        }
        // Recepcionista: ve datos generales SIN balance
        elseif ($user->hasRole('recepcionista')) {
            $dashboardData = $this->getRecepcionistaDashboardData($user);
        }
        // Médico y Enfermera: ven solo sus citas
        elseif ($user->hasRole(['medico', 'enfermera'])) {
            $dashboardData = $this->getDoctorDashboardData();
        }
        // Inventario: ve solo inventario
        elseif ($user->hasRole('inventario')) {
            $dashboardData = $this->getInventoryDashboardData();
        }
        // Usuarios sin permisos específicos
        else {
            $dashboardData = $this->getBasicDashboardData();
        }

        $permissions = [
            'view_patients' => $user->can('view_patients'),
            'view_all_appointments' => $user->can('view_all_appointments'),
            'view_own_appointments' => $user->can('view_own_appointments'),
            'view_inventory' => $user->can('view_inventory'),
            'view_cash_flow' => $user->can('view_cash_flow'),
            'view_reports' => $user->can('view_reports'),
        ];

        return view('dashboard.index', compact('dashboardData', 'announcements', 'permissions'));
    }

    /**
     * Dashboard completo para ADMIN (con balance)
     */
    private function getFullDashboardData($user)
    {
        return [
            'totalPatients' => Patient::count(),
            'totalDoctors' => Doctor::count(),
            'totalServices' => Service::count(),
            'totalAppointments' => Appointment::count(),
            'appointmentsToday' => Appointment::whereDate('appointment_date', today())->count(),
            'pendingAppointments' => Appointment::where('status', 'pendiente')->count(),
            'totalIncome' => Income::whereMonth('payment_date', now()->month)->sum('amount_paid'),
            'totalExpense' => Expense::whereMonth('expense_date', now()->month)->sum('amount'),
            'balance' => Income::whereMonth('payment_date', now()->month)->sum('amount_paid') - 
                          Expense::whereMonth('expense_date', now()->month)->sum('amount'),
            'recentAppointments' => Appointment::with(['patient', 'appointmentServices.service', 'appointmentServices.doctor.user'])
                ->where('status', '!=', 'cancelada')
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->limit(5)
                ->get(),
            'lowStockItems' => Inventory::whereRaw('quantity <= min_stock')->limit(5)->get(),
            'isAdmin' => true,
            'isRecepcionista' => false,
        ];
    }

    /**
     * Dashboard para RECEPCIONISTA (SIN balance)
     */
    private function getRecepcionistaDashboardData($user)
    {
        return [
            'totalPatients' => Patient::count(),
            'totalDoctors' => Doctor::count(),
            'totalServices' => Service::count(),
            'totalAppointments' => Appointment::count(),
            'appointmentsToday' => Appointment::whereDate('appointment_date', today())->count(),
            'pendingAppointments' => Appointment::where('status', 'pendiente')->count(),
            'recentAppointments' => Appointment::with(['patient', 'appointmentServices.service', 'appointmentServices.doctor.user'])
                ->where('status', '!=', 'cancelada')
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->limit(5)
                ->get(),
            'lowStockItems' => Inventory::whereRaw('quantity <= min_stock')->limit(5)->get(),
            'isAdmin' => false,
            'isRecepcionista' => true,
        ];
    }

    /**
     * Dashboard para MÉDICO/ENFERMERA (solo sus citas)
     */
    private function getDoctorDashboardData()
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        $doctorId = $doctor ? $doctor->id : 0;

        // Obtener citas donde este médico tiene servicios asignados (a través de appointment_services)
        $myAppointments = Appointment::whereHas('appointmentServices', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId);
        })->where('status', '!=', 'cancelada');

        $myAppointmentsToday = Appointment::whereHas('appointmentServices', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId);
        })->whereDate('appointment_date', today())
          ->where('status', '!=', 'cancelada');

        $myPendingAppointments = Appointment::whereHas('appointmentServices', function($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId);
        })->where('status', 'pendiente')
          ->where('status', '!=', 'cancelada');

        return [
            'myAppointmentsToday' => $myAppointmentsToday->count(),
            'myPendingAppointments' => $myPendingAppointments->count(),
            'myTotalAppointments' => $myAppointments->count(),
            'myRecentAppointments' => Appointment::with(['patient', 'appointmentServices.service', 'appointmentServices.doctor.user'])
                ->whereHas('appointmentServices', function($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                })
                ->where('status', '!=', 'cancelada')
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->limit(5)
                ->get(),
            'isDoctor' => true,
            'isRecepcionista' => false,
        ];
    }

    /**
     * Dashboard para INVENTARIO
     */
    private function getInventoryDashboardData()
    {
        return [
            'totalItems' => Inventory::count(),
            'lowStockItems' => Inventory::whereRaw('quantity <= min_stock')->get(),
            'lowStockCount' => Inventory::whereRaw('quantity <= min_stock')->count(),
            'totalCategories' => Inventory::distinct('category')->count('category'),
            'recentMovements' => \App\Models\InventoryMovement::with(['inventory', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'isInventory' => true,
            'isRecepcionista' => false,
        ];
    }

    /**
     * Dashboard básico para usuarios sin permisos específicos
     */
    private function getBasicDashboardData()
    {
        return [
            'welcome' => true,
            'message' => 'Bienvenido al sistema de gestión clínica Sanare',
            'userName' => Auth::user()->name,
            'userRole' => Auth::user()->roles->first()->display_name ?? Auth::user()->roles->first()->name ?? 'Usuario',
            'isRecepcionista' => false,
        ];
    }
}