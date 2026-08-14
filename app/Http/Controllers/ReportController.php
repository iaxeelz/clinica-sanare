<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\DoctorReportExport;
use App\Exports\IncomeReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // Dashboard de reportes
    public function index()
    {
        // Totales generales
        $totalPatients = Patient::count();
        $totalDoctors = Doctor::count();
        $totalServices = Service::count();
        $totalAppointments = Appointment::count();
        
        // Citas por estado
        $appointmentsByStatus = Appointment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        
        // Ingresos del mes actual
        $currentMonth = date('Y-m');
        $totalIncome = Income::where('payment_date', 'LIKE', "$currentMonth%")->sum('amount_paid');
        $totalExpense = Expense::where('expense_date', 'LIKE', "$currentMonth%")->sum('amount');
        $balance = $totalIncome - $totalExpense;
        
        return view('reports.index', compact(
            'totalPatients', 'totalDoctors', 'totalServices', 'totalAppointments',
            'appointmentsByStatus', 'totalIncome', 'totalExpense', 'balance'
        ));
    }

    // Reporte de citas por médico
    public function doctorReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $doctorId = $request->get('doctor_id');
        
        $query = Appointment::with(['patient', 'doctor.user', 'service'])
            ->whereBetween('appointment_date', [$startDate, $endDate]);
        
        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }
        
        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'asc')
            ->get();
        
        $doctors = Doctor::with('user')->where('is_active', true)->get();
        
        // Estadísticas por médico
        $doctorStats = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->when($doctorId, function ($q) use ($doctorId) {
                return $q->where('doctor_id', $doctorId);
            })
            ->select('doctor_id', DB::raw('count(*) as total'))
            ->groupBy('doctor_id')
            ->with('doctor.user')
            ->get();
        
        return view('reports.doctor', compact(
            'appointments', 'doctors', 'doctorStats', 'startDate', 'endDate', 'doctorId'
        ));
    }

    // Reporte de servicios
    public function serviceReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $serviceId = $request->get('service_id');
        
        $query = Appointment::with(['patient', 'doctor.user', 'service'])
            ->whereBetween('appointment_date', [$startDate, $endDate]);
        
        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }
        
        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'asc')
            ->get();
        
        $services = Service::where('is_active', true)->orderBy('name')->get();
        
        // Estadísticas por servicio
        $serviceStats = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->when($serviceId, function ($q) use ($serviceId) {
                return $q->where('service_id', $serviceId);
            })
            ->select('service_id', DB::raw('count(*) as total'))
            ->groupBy('service_id')
            ->with('service')
            ->get();
        
        return view('reports.service', compact(
            'appointments', 'services', 'serviceStats', 'startDate', 'endDate', 'serviceId'
        ));
    }

    // Reporte de pacientes
    public function patientReport(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        
        $query = Patient::withCount(['appointments' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('appointment_date', [$startDate, $endDate]);
        }]);
        
        if ($search) {
            $query->search($search);
        }
        
        $patients = $query->orderBy('last_name')->paginate(20)->withQueryString();
        
        return view('reports.patient', compact('patients', 'search', 'startDate', 'endDate'));
    }

    // Reporte financiero detallado
    public function financialReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        
        // Ingresos del período
        $incomes = Income::with(['patient', 'service', 'doctor.user'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'desc')
            ->get();
        
        // Egresos del período
        $expenses = Expense::with('user')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();
        
        // Totales
        $totalIncomes = $incomes->sum('amount_paid');
        $totalExpenses = $expenses->sum('amount');
        $balance = $totalIncomes - $totalExpenses;
        
        // Resumen por método de pago
        $incomesByMethod = $incomes->groupBy('payment_method')->map(function ($group) {
            return $group->sum('amount_paid');
        });
        
        // Resumen por categoría de egreso
        $expensesByCategory = $expenses->groupBy('category')->map(function ($group) {
            return $group->sum('amount');
        });
        
        return view('reports.financial', compact(
            'incomes', 'expenses', 'totalIncomes', 'totalExpenses', 'balance',
            'incomesByMethod', 'expensesByCategory', 'startDate', 'endDate'
        ));
    }

    // ============================================
    // EXPORTACIONES A EXCEL Y PDF
    // ============================================

    // Exportar a Excel - Reporte por Médico
    public function exportDoctorExcel(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $doctorId = $request->get('doctor_id');

        return Excel::download(
            new DoctorReportExport($startDate, $endDate, $doctorId),
            'reporte_medico_' . date('Ymd') . '.xlsx'
        );
    }

    // Exportar a PDF - Reporte por Médico
    public function exportDoctorPdf(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $doctorId = $request->get('doctor_id');

        $query = Appointment::with(['patient', 'doctor.user', 'service'])
            ->whereBetween('appointment_date', [$startDate, $endDate]);

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        $pdf = Pdf::loadView('reports.pdf.doctor', compact('appointments', 'startDate', 'endDate'));
        return $pdf->download('reporte_medico_' . date('Ymd') . '.pdf');
    }

    // Exportar a Excel - Reporte Financiero
    public function exportFinancialExcel(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        return Excel::download(
            new IncomeReportExport($startDate, $endDate),
            'reporte_financiero_' . date('Ymd') . '.xlsx'
        );
    }

    // Exportar a PDF - Reporte Financiero (CORREGIDO)
    public function exportFinancialPdf(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $incomes = Income::with(['patient', 'service', 'doctor.user'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'desc')
            ->get();

        $expenses = Expense::with('user')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalIncomes = $incomes->sum('amount_paid');
        $totalExpenses = $expenses->sum('amount');
        $balance = $totalIncomes - $totalExpenses;

        // Resumen por método de pago
        $incomesByMethod = $incomes->groupBy('payment_method')->map(function ($group) {
            return $group->sum('amount_paid');
        });

        // Resumen por categoría de egreso
        $expensesByCategory = $expenses->groupBy('category')->map(function ($group) {
            return $group->sum('amount');
        });

        $pdf = Pdf::loadView('reports.pdf.financial', compact(
            'incomes', 'expenses', 'totalIncomes', 'totalExpenses', 'balance',
            'incomesByMethod', 'expensesByCategory', 'startDate', 'endDate'
        ));
        return $pdf->download('reporte_financiero_' . date('Ymd') . '.pdf');
    }
}