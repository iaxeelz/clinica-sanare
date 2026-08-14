<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        $date = $request->get('date', date('Y-m-d'));
        
        $query = Expense::with('user');
        
        if ($search) {
            $query->search($search);
        }
        
        if ($category) {
            $query->byCategory($category);
        }
        
        if ($date) {
            $query->forDate($date);
        }
        
        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        
        $categories = ['compra_inventario', 'servicios', 'sueldos', 'otros'];
        $totalToday = Expense::forDate(date('Y-m-d'))->sum('amount');
        
        return view('expenses.index', compact('expenses', 'search', 'category', 'date', 'categories', 'totalToday'));
    }

    public function create()
    {
        $categories = ['compra_inventario', 'servicios', 'sueldos', 'otros'];
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|in:compra_inventario,servicios,sueldos,otros',
            'expense_date' => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'description' => 'nullable|string'
        ]);

        $expense = Expense::create([
            'concept' => $validated['concept'],
            'amount' => $validated['amount'],
            'category' => $validated['category'],
            'expense_date' => $validated['expense_date'],
            'receipt_number' => $validated['receipt_number'] ?? null,
            'description' => $validated['description'] ?? null,
            'user_id' => Auth::id(),
            'is_active' => true
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Egreso registrado exitosamente.');
    }

    public function show(Expense $expense)
    {
        $expense->load('user');
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = ['compra_inventario', 'servicios', 'sueldos', 'otros'];
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|in:compra_inventario,servicios,sueldos,otros',
            'expense_date' => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'description' => 'nullable|string'
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Egreso actualizado exitosamente.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Egreso eliminado exitosamente.');
    }
}