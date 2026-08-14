<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Expense;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $payment_method = $request->get('payment_method');
        $date = $request->get('date', date('Y-m-d'));
        
        $query = Income::with(['patient', 'service', 'doctor.user']);
        
        if ($search) {
            $query->search($search);
        }
        
        if ($payment_method) {
            $query->byPaymentMethod($payment_method);
        }
        
        if ($date) {
            $query->forDate($date);
        }
        
        $incomes = $query->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        
        $paymentMethods = ['efectivo', 'yape', 'tarjeta_culqi'];
        $totalToday = Income::forDate(date('Y-m-d'))->sum('amount_paid');
        
        return view('incomes.index', compact('incomes', 'search', 'payment_method', 'date', 'paymentMethods', 'totalToday'));
    }

    public function create()
    {
        $patients = Patient::where('is_active', true)->orderBy('last_name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with('user')->where('is_active', true)->get();
        $paymentMethods = ['efectivo', 'yape', 'tarjeta_culqi'];
        
        return view('incomes.create', compact('patients', 'services', 'doctors', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:doctors,id',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
            'doctor_payment' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:efectivo,yape,tarjeta_culqi',
            'receipt_number' => 'nullable|string|max:50',
            'invoice_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'payment_date' => 'required|date'
        ]);

        // Calcular vuelto automáticamente si es efectivo
        if ($validated['payment_method'] === 'efectivo' && $validated['amount_paid'] > $validated['sale_price']) {
            $validated['change_amount'] = $validated['amount_paid'] - $validated['sale_price'];
        }

        $income = Income::create([
            'patient_id' => $validated['patient_id'],
            'service_id' => $validated['service_id'],
            'doctor_id' => $validated['doctor_id'],
            'cost_price' => $validated['cost_price'],
            'sale_price' => $validated['sale_price'],
            'amount_paid' => $validated['amount_paid'],
            'change_amount' => $validated['change_amount'] ?? 0,
            'doctor_payment' => $validated['doctor_payment'] ?? 0,
            'payment_method' => $validated['payment_method'],
            'receipt_number' => $validated['receipt_number'] ?? null,
            'invoice_number' => $validated['invoice_number'] ?? null,
            'description' => $validated['description'] ?? null,
            'payment_date' => $validated['payment_date'],
            'user_id' => Auth::id(),
            'is_active' => true
        ]);

        return redirect()->route('incomes.index')
            ->with('success', 'Ingreso registrado exitosamente.');
    }

    public function show(Income $income)
    {
        $income->load(['patient', 'service', 'doctor.user', 'user']);
        return view('incomes.show', compact('income'));
    }

    public function edit(Income $income)
    {
        $patients = Patient::where('is_active', true)->orderBy('last_name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with('user')->where('is_active', true)->get();
        $paymentMethods = ['efectivo', 'yape', 'tarjeta_culqi'];
        
        return view('incomes.edit', compact('income', 'patients', 'services', 'doctors', 'paymentMethods'));
    }

    public function update(Request $request, Income $income)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:doctors,id',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
            'doctor_payment' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:efectivo,yape,tarjeta_culqi',
            'receipt_number' => 'nullable|string|max:50',
            'invoice_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'payment_date' => 'required|date'
        ]);

        if ($validated['payment_method'] === 'efectivo' && $validated['amount_paid'] > $validated['sale_price']) {
            $validated['change_amount'] = $validated['amount_paid'] - $validated['sale_price'];
        }

        $income->update($validated);

        return redirect()->route('incomes.index')
            ->with('success', 'Ingreso actualizado exitosamente.');
    }

    public function destroy(Income $income)
    {
        $income->delete();

        return redirect()->route('incomes.index')
            ->with('success', 'Ingreso eliminado exitosamente.');
    }

    // Reporte de flujo de caja
    public function cashFlow(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        
        // Ingresos del período
        $totalIncomes = Income::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount_paid');
        
        // Egresos del período
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Balance
        $balance = $totalIncomes - $totalExpenses;
        
        // Ingresos por método de pago
        $incomesByMethod = Income::whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('payment_method, SUM(amount_paid) as total')
            ->groupBy('payment_method')
            ->get();
        
        // Ingresos por servicio
        $incomesByService = Income::whereBetween('payment_date', [$startDate, $endDate])
            ->with('service')
            ->selectRaw('service_id, SUM(amount_paid) as total')
            ->groupBy('service_id')
            ->get();
        
        // Egresos por categoría
        $expensesByCategory = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();
        
        return view('incomes.cash-flow', compact(
            'startDate', 'endDate', 'totalIncomes', 'totalExpenses', 'balance',
            'incomesByMethod', 'incomesByService', 'expensesByCategory'
        ));
    }
}