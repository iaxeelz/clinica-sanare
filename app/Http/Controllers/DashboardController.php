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
        try {
            $user = Auth::user();
            
            // Obtener anuncios activos
            $announcements = collect([]);
            try {
                $announcements = Announcement::with('user')
                    ->where('is_active', true)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Exception $e) {
                // Si falla, continuar con anuncios vacíos
            }

            $dashboardData = [];

            // Admin: ve todos los datos
            if ($user && $user->hasRole('admin')) {
                $dashboardData = $this->getFullDashboardData($user);
            }
            // Recepcionista: ve datos generales SIN balance
            elseif ($user && $user->hasRole('recepcionista')) {
                $dashboardData = $this->getRecepcionistaDashboardData($user);
            }
            // Médico y Enfermera: ven solo sus citas
            elseif ($user && $user->hasRole(['medico', 'enfermera'])) {
                $dashboardData = $this->getDoctorDashboardData();
            }
            // Inventario: ve solo inventario
            elseif ($user && $user->hasRole('inventario')) {
                $dashboardData = $this->getInventoryDashboardData();
            }
            // Usuarios sin permisos específicos
            else {
                $dashboardData = $this->getBasicDashboardData();
            }

            $permissions = [
                'view_patients' => $user ? $user->can('view_patients') : false,
                'view_all_appointments' => $user ? $user->can('view_all_appointments') : false,
                'view_own_appointments' => $user ? $user->can('view_own_appointments') : false,
                'view_inventory' => $user ? $user->can('view_inventory') : false,
                'view_cash_flow' => $user ? $user->can('view_cash_flow') : false,
                'view_reports' => $user ? $user->can('view_reports') : false,
            ];

            return view('dashboard.index', compact('dashboardData', 'announcements', 'permissions'));
            
        } catch (\Exception $e) {
            // Si hay error, mostrar mensaje simple
            return response()->json([
                'error' => 'Error en Dashboard: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Dashboard completo para ADMIN (con balance)
     */
    private function getFullDashboardData($user)
    {
        try {
            // Obtener conteos con manejo de errores
            $totalPatients = 0;
            $totalDoctors = 0;
            $totalServices = 0;
            $totalAppointments = 0;
            $appointmentsToday = 0;
            $pendingAppointments = 0;
            $totalIncome = 0;
            $totalExpense = 0;
            $balance = 0;
            $recentAppointments = collect([]);
            $lowStockItems = collect([]);

            try { $totalPatients = Patient::count(); } catch (\Exception $e) {}
            try { $totalDoctors = Doctor::count(); } catch (\Exception $e) {}
            try { $totalServices = Service::count(); } catch (\Exception $e) {}
            try { $totalAppointments = Appointment::count(); } catch (\Exception $e) {}
            
            try { 
                $appointmentsToday = Appointment::whereDate('appointment_date', today())->count(); 
            } catch (\Exception $e) {}
            
            try { 
                $pendingAppointments = Appointment::where('status', 'pendiente')->count(); 
            } catch (\Exception $e) {}
            
            try { 
                $totalIncome = Income::whereMonth('payment_date', now()->month)->sum('amount_paid'); 
            } catch (\Exception $e) {}
            
            try { 
                $totalExpense = Expense::whereMonth('expense_date', now()->month)->sum('amount'); 
            } catch (\Exception $e) {}
            
            try { 
                $balance = $totalIncome - $totalExpense; 
            } catch (\Exception $e) {}
            
            try { 
                $recentAppointments = Appointment::with(['patient', 'appointmentServices.service', 'appointmentServices.doctor.user'])
                    ->where('status', '!=', 'cancelada')
                    ->orderBy('appointment_date', 'desc')
                    ->orderBy('appointment_time', 'desc')
                    ->limit(5)
                    ->get(); 
            } catch (\Exception $e) {}
            
            try { 
                // PostgreSQL usa sintaxis diferente para comparar columnas
                $lowStockItems = Inventory::whereRaw('quantity <= min_stock')->limit(5)->get(); 
            } catch (\Exception $e) {
                // Si falla, intentar con otra sintaxis
                try {
                    $lowStockItems = Inventory::whereColumn('quantity', '<=', 'min_stock')->limit(5)->get();
                } catch (\Exception $ex) {
                    $lowStockItems = collect([]);
                }
            }

            return [
                'totalPatients' => $totalPatients,
                'totalDoctors' => $totalDoctors,
                'totalServices' => $totalServices,
                'totalAppointments' => $totalAppointments,
                'appointmentsToday' => $appointmentsToday,
                'pendingAppointments' => $pendingAppointments,
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'balance' => $balance,
                'recentAppointments' => $recentAppointments,
                'lowStockItems' => $lowStockItems,
                'isAdmin' => true,
                'isRecepcionista' => false,
            ];
        } catch (\Exception $e) {
            // Si todo falla, devolver datos vacíos
            return [
                'totalPatients' => 0,
                'totalDoctors' => 0,
                'totalServices' => 0,
                'totalAppointments' => 0,
                'appointmentsToday' => 0,
                'pendingAppointments' => 0,
                'totalIncome' => 0,
                'totalExpense' => 0,
                'balance' => 0,
                'recentAppointments' => collect([]),
                'lowStockItems' => collect([]),
                'isAdmin' => true,
                'isRecepcionista' => false,
            ];
        }
    }

    /**
     * Dashboard para RECEPCIONISTA (SIN balance)
     */
    private function getRecepcionistaDashboardData($user)
    {
        try {
            $totalPatients = 0;
            $totalDoctors = 0;
            $totalServices = 0;
            $totalAppointments = 0;
            $appointmentsToday = 0;
            $pendingAppointments = 0;
            $recentAppointments = collect([]);
            $lowStockItems = collect([]);

            try { $totalPatients = Patient::count(); } catch (\Exception $e) {}
            try { $totalDoctors = Doctor::count(); } catch (\Exception $e) {}
            try { $totalServices = Service::count(); } catch (\Exception $e) {}
            try { $totalAppointments = Appointment::count(); } catch (\Exception $e) {}
            
            try { 
                $appointmentsToday = Appointment::whereDate('appointment_date', today())->count(); 
            } catch (\Exception $e) {}
            
            try { 
                $pendingAppointments = Appointment::where('status', 'pendiente')->count(); 
            } catch (\Exception $e) {}
            
            try { 
                $recentAppointments = Appointment::with(['patient', 'appointmentServices.service', 'appointmentServices.doctor.user'])
                    ->where('status', '!=', 'cancelada')
                    ->orderBy('appointment_date', 'desc')
                    ->orderBy('appointment_time', 'desc')
                    ->limit(5)
                    ->get(); 
            } catch (\Exception $e) {}
            
            try { 
                $lowStockItems = Inventory::whereRaw('quantity <= min_stock')->limit(5)->get(); 
            } catch (\Exception $e) {
                try {
                    $lowStockItems = Inventory::whereColumn('quantity', '<=', 'min_stock')->limit(5)->get();
                } catch (\Exception $ex) {
                    $lowStockItems = collect([]);
                }
            }

            return [
                'totalPatients' => $totalPatients,
                'totalDoctors' => $totalDoctors,
                'totalServices' => $totalServices,
                'totalAppointments' => $totalAppointments,
                'appointmentsToday' => $appointmentsToday,
                'pendingAppointments' => $pendingAppointments,
                'recentAppointments' => $recentAppointments,
                'lowStockItems' => $lowStockItems,
                'isAdmin' => false,
                'isRecepcionista' => true,
            ];
        } catch (\Exception $e) {
            return [
                'totalPatients' => 0,
                'totalDoctors' => 0,
                'totalServices' => 0,
                'totalAppointments' => 0,
                'appointmentsToday' => 0,
                'pendingAppointments' => 0,
                'recentAppointments' => collect([]),
                'lowStockItems' => collect([]),
                'isAdmin' => false,
                'isRecepcionista' => true,
            ];
        }
    }

    /**
     * Dashboard para MÉDICO/ENFERMERA (solo sus citas)
     */
    private function getDoctorDashboardData()
    {
        try {
            $doctor = Doctor::where('user_id', Auth::id())->first();
            $doctorId = $doctor ? $doctor->id : 0;

            $myAppointmentsToday = 0;
            $myPendingAppointments = 0;
            $myTotalAppointments = 0;
            $myRecentAppointments = collect([]);

            if ($doctorId > 0) {
                try {
                    $myAppointments = Appointment::whereHas('appointmentServices', function($q) use ($doctorId) {
                        $q->where('doctor_id', $doctorId);
                    })->where('status', '!=', 'cancelada');

                    $myAppointmentsToday = (clone $myAppointments)->whereDate('appointment_date', today())->count();
                    $myPendingAppointments = (clone $myAppointments)->where('status', 'pendiente')->count();
                    $myTotalAppointments = $myAppointments->count();

                    $myRecentAppointments = Appointment::with(['patient', 'appointmentServices.service', 'appointmentServices.doctor.user'])
                        ->whereHas('appointmentServices', function($q) use ($doctorId) {
                            $q->where('doctor_id', $doctorId);
                        })
                        ->where('status', '!=', 'cancelada')
                        ->orderBy('appointment_date', 'desc')
                        ->orderBy('appointment_time', 'desc')
                        ->limit(5)
                        ->get();
                } catch (\Exception $e) {}
            }

            return [
                'myAppointmentsToday' => $myAppointmentsToday,
                'myPendingAppointments' => $myPendingAppointments,
                'myTotalAppointments' => $myTotalAppointments,
                'myRecentAppointments' => $myRecentAppointments,
                'isDoctor' => true,
                'isRecepcionista' => false,
            ];
        } catch (\Exception $e) {
            return [
                'myAppointmentsToday' => 0,
                'myPendingAppointments' => 0,
                'myTotalAppointments' => 0,
                'myRecentAppointments' => collect([]),
                'isDoctor' => true,
                'isRecepcionista' => false,
            ];
        }
    }

    /**
     * Dashboard para INVENTARIO
     */
    private function getInventoryDashboardData()
    {
        try {
            $totalItems = 0;
            $lowStockItems = collect([]);
            $lowStockCount = 0;
            $totalCategories = 0;
            $recentMovements = collect([]);

            try { $totalItems = Inventory::count(); } catch (\Exception $e) {}
            
            try { 
                $lowStockItems = Inventory::whereRaw('quantity <= min_stock')->get(); 
                $lowStockCount = $lowStockItems->count();
            } catch (\Exception $e) {
                try {
                    $lowStockItems = Inventory::whereColumn('quantity', '<=', 'min_stock')->get();
                    $lowStockCount = $lowStockItems->count();
                } catch (\Exception $ex) {
                    $lowStockItems = collect([]);
                    $lowStockCount = 0;
                }
            }
            
            try { 
                $totalCategories = Inventory::distinct('category')->count('category'); 
            } catch (\Exception $e) {}
            
            try { 
                $recentMovements = \App\Models\InventoryMovement::with(['inventory', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(); 
            } catch (\Exception $e) {}

            return [
                'totalItems' => $totalItems,
                'lowStockItems' => $lowStockItems,
                'lowStockCount' => $lowStockCount,
                'totalCategories' => $totalCategories,
                'recentMovements' => $recentMovements,
                'isInventory' => true,
                'isRecepcionista' => false,
            ];
        } catch (\Exception $e) {
            return [
                'totalItems' => 0,
                'lowStockItems' => collect([]),
                'lowStockCount' => 0,
                'totalCategories' => 0,
                'recentMovements' => collect([]),
                'isInventory' => true,
                'isRecepcionista' => false,
            ];
        }
    }

    /**
     * Dashboard básico para usuarios sin permisos específicos
     */
    private function getBasicDashboardData()
    {
        try {
            $user = Auth::user();
            return [
                'welcome' => true,
                'message' => 'Bienvenido al sistema de gestión clínica Sanare',
                'userName' => $user ? $user->name : 'Usuario',
                'userRole' => $user && $user->roles->first() ? ($user->roles->first()->display_name ?? $user->roles->first()->name ?? 'Usuario') : 'Usuario',
                'isRecepcionista' => false,
            ];
        } catch (\Exception $e) {
            return [
                'welcome' => true,
                'message' => 'Bienvenido al sistema',
                'userName' => 'Usuario',
                'userRole' => 'Usuario',
                'isRecepcionista' => false,
            ];
        }
    }
}
