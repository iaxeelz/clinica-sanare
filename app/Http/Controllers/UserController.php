<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        $roles = Role::all();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.users.create', compact('roles', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,id',
            'is_active' => 'sometimes|boolean',
            'specialty' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:50',
            'selected_services' => 'nullable|string', // NUEVO
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true
        ]);

        $role = Role::find($validated['role']);
        $user->assignRole($role);

        // Si es médico o enfermera
        if ($role->name === 'medico' || $role->name === 'enfermera') {
            if (!empty($validated['specialty']) && !empty($validated['license_number'])) {
                $doctor = Doctor::create([
                    'user_id' => $user->id,
                    'specialty' => $validated['specialty'],
                    'license_number' => $validated['license_number'],
                    'consultation_fee' => 0,
                    'is_active' => $validated['is_active'] ?? true
                ]);

                // ============================================
                // LEER SERVICIOS DEL CAMPO HIDDEN
                // ============================================
                $selectedServices = [];
                if ($request->filled('selected_services')) {
                    $selectedServices = json_decode($request->selected_services, true);
                }

                // Asignar servicios al médico
                if (!empty($selectedServices)) {
                    $doctor->services()->sync($selectedServices);
                }

                return redirect()->route('doctors.index')
                    ->with('success', 'Médico/Enfermera registrado exitosamente.');
            } else {
                $user->delete();
                return back()->withInput()
                    ->with('error', 'Debes completar Especialidad y N° Licencia.');
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRole = $user->roles->first();
        $doctor = Doctor::where('user_id', $user->id)->first();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles', 'userRole', 'doctor', 'services'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|exists:roles,id',
            'is_active' => 'sometimes|boolean',
            'specialty' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:50',
            'selected_services' => 'nullable|string', // NUEVO
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $role = Role::find($validated['role']);
        $user->syncRoles([$role]);

        $doctor = Doctor::where('user_id', $user->id)->first();

        if ($role->name === 'medico' || $role->name === 'enfermera') {
            if (!empty($validated['specialty']) && !empty($validated['license_number'])) {
                if ($doctor) {
                    $doctor->update([
                        'specialty' => $validated['specialty'],
                        'license_number' => $validated['license_number'],
                        'is_active' => $validated['is_active'] ?? true
                    ]);
                } else {
                    $doctor = Doctor::create([
                        'user_id' => $user->id,
                        'specialty' => $validated['specialty'],
                        'license_number' => $validated['license_number'],
                        'consultation_fee' => 0,
                        'is_active' => $validated['is_active'] ?? true
                    ]);
                }

                // ============================================
                // LEER SERVICIOS DEL CAMPO HIDDEN (UPDATE)
                // ============================================
                $selectedServices = [];
                if ($request->filled('selected_services')) {
                    $selectedServices = json_decode($request->selected_services, true);
                }

                if (!empty($selectedServices)) {
                    $doctor->services()->sync($selectedServices);
                } else {
                    $doctor->services()->detach();
                }
            }
        } else {
            if ($doctor) {
                $doctor->services()->detach();
                $doctor->delete();
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $doctor = Doctor::where('user_id', $user->id)->first();
        if ($doctor) {
            $doctor->services()->detach();
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}