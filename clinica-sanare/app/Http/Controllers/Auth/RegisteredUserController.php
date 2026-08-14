<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View|RedirectResponse
    {
        // Si el usuario está autenticado y es admin, mostrar el formulario
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            $roles = Role::all();
            return view('auth.register', compact('roles'));
        }
        
        // Si no está autenticado, redirigir al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        // Si está autenticado pero no es admin, redirigir al dashboard
        return redirect()->route('dashboard');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Solo admin puede registrar usuarios
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            return redirect()->route('login');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Asignar rol si se seleccionó
        if ($request->filled('role')) {
            $user->assignRole($request->role);
        } else {
            // Rol por defecto: recepcionista
            $user->assignRole('recepcionista');
        }

        event(new Registered($user));

        // NO iniciar sesión automáticamente, redirigir a usuarios
        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }
}