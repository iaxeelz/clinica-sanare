<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Services\NotificationService; // <--- NUEVO
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        $lowStock = $request->get('low_stock');
        
        $query = Inventory::query();
        
        if ($search) {
            $query->search($search);
        }
        
        if ($category) {
            $query->byCategory($category);
        }
        
        if ($lowStock) {
            $query->whereRaw('quantity <= min_stock');
        }
        
        $inventory = $query->orderBy('name')->paginate(15)->withQueryString();
        
        // Obtener categorías para el filtro
        $categories = Inventory::distinct()->pluck('category');
        $lowStockCount = Inventory::whereRaw('quantity <= min_stock')->count();
        
        return view('inventory.index', compact('inventory', 'search', 'category', 'lowStock', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $categories = ['medicamento', 'insumo', 'equipo', 'otros'];
        $units = ['unidad', 'caja', 'frasco', 'ampolla', 'tubo', 'kit', 'par', 'metro', 'litro', 'kilogramo', 'gramo', 'mililitro'];
        
        return view('inventory.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory',
            'category' => 'required|string|in:medicamento,insumo,equipo,otros',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'expiration_date' => 'nullable|date|after:today',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean'
        ]);

        $inventory = Inventory::create($validated);

        // Registrar movimiento inicial si hay stock
        if ($validated['quantity'] > 0) {
            InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'type' => 'entrada',
                'quantity' => $validated['quantity'],
                'price' => $validated['purchase_price'],
                'description' => 'Stock inicial',
                'user_id' => Auth::id()
            ]);
        }

        // ============================================
        // NOTIFICAR STOCK BAJO SI CORRESPONDE
        // ============================================
        if ($inventory->quantity <= $inventory->min_stock) {
            $notificationService = new NotificationService();
            $notificationService->notifyLowStock();
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Artículo registrado exitosamente.');
    }

    public function show(Inventory $inventory)
    {
        $movements = $inventory->movements()->with('user')->orderBy('created_at', 'desc')->paginate(10);
        return view('inventory.show', compact('inventory', 'movements'));
    }

    public function edit(Inventory $inventory)
    {
        $categories = ['medicamento', 'insumo', 'equipo', 'otros'];
        $units = ['unidad', 'caja', 'frasco', 'ampolla', 'tubo', 'kit', 'par', 'metro', 'litro', 'kilogramo', 'gramo', 'mililitro'];
        
        return view('inventory.edit', compact('inventory', 'categories', 'units'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory')->ignore($inventory->id)],
            'category' => 'required|string|in:medicamento,insumo,equipo,otros',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'expiration_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean'
        ]);

        $inventory->update($validated);

        // ============================================
        // NOTIFICAR STOCK BAJO SI CORRESPONDE
        // ============================================
        if ($inventory->quantity <= $inventory->min_stock) {
            $notificationService = new NotificationService();
            $notificationService->notifyLowStock();
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Artículo actualizado exitosamente.');
    }

    public function destroy(Inventory $inventory)
    {
        // Verificar si tiene movimientos
        if ($inventory->movements()->count() > 0) {
            return redirect()->route('inventory.index')
                ->with('error', 'No se puede eliminar el artículo porque tiene movimientos registrados.');
        }

        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Artículo eliminado exitosamente.');
    }

    // Ajustar stock (entrada/salida manual)
    public function adjustStock(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'type' => 'required|in:entrada,salida',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);

        // Verificar que hay stock suficiente para salida
        if ($validated['type'] === 'salida' && $inventory->quantity < $validated['quantity']) {
            return back()->with('error', 'No hay suficiente stock disponible.');
        }

        // Actualizar cantidad
        if ($validated['type'] === 'entrada') {
            $inventory->quantity += $validated['quantity'];
        } else {
            $inventory->quantity -= $validated['quantity'];
        }
        $inventory->save();

        // Registrar movimiento
        InventoryMovement::create([
            'inventory_id' => $inventory->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'price' => $inventory->purchase_price,
            'description' => $validated['description'] ?? ($validated['type'] === 'entrada' ? 'Ajuste de entrada' : 'Ajuste de salida'),
            'user_id' => Auth::id()
        ]);

        // ============================================
        // NOTIFICAR STOCK BAJO SI CORRESPONDE
        // ============================================
        if ($inventory->quantity <= $inventory->min_stock) {
            $notificationService = new NotificationService();
            $notificationService->notifyLowStock();
        }

        $message = $validated['type'] === 'entrada' ? 'entrada' : 'salida';
        return redirect()->route('inventory.show', $inventory)
            ->with('success', "Movimiento de {$message} registrado exitosamente.");
    }
}