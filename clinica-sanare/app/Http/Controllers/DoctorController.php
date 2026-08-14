<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $doctors = Doctor::with('user', 'services')
            ->when($search, function ($query, $search) {
                return $query->search($search);
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('doctors.index', compact('doctors', 'search'));
    }

    public function create(Request $request)
    {
        // Pasar datos desde la URL si vienen de la creación de usuario
        $name = $request->get('name', '');
        $email = $request->get('email', '');
        $phone = $request->get('phone', '');
        $password = $request->get('password', '');
        $role = $request->get('role', '');

        // Obtener todos los servicios activos
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('doctors.create', compact('name', 'email', 'phone', 'password', 'role', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'specialty' => 'required|string|max:255',
            'license_number' => 'required|string|max:50|unique:doctors',
            'is_active' => 'sometimes|boolean',
            // NUEVO: Servicios que ofrece el médico
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id'
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => true
        ]);

        // Asignar rol de médico
        $medicoRole = Role::where('name', 'medico')->first();
        if ($medicoRole) {
            $user->assignRole($medicoRole);
        }

        // Crear médico
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialty' => $validated['specialty'],
            'license_number' => $validated['license_number'],
            'consultation_fee' => 0,
            'is_active' => $validated['is_active'] ?? true
        ]);

        // NUEVO: Asignar servicios al médico
        if (isset($validated['services']) && !empty($validated['services'])) {
            $doctor->services()->sync($validated['services']);
        }

        return redirect()->route('doctors.index')
            ->with('success', 'Médico registrado exitosamente.');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'services']);
        return view('doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor)
    {
        $doctor->load(['user', 'services']);
        
        // Obtener todos los servicios activos
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('doctors.edit', compact('doctor', 'services'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($doctor->user_id)],
            'phone' => 'nullable|string|max:20',
            'specialty' => 'required|string|max:255',
            'license_number' => ['required', 'string', 'max:50', Rule::unique('doctors')->ignore($doctor->id)],
            'is_active' => 'sometimes|boolean',
            // NUEVO: Servicios que ofrece el médico
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id'
        ]);

        // Actualizar usuario
        $doctor->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true
        ]);

        // Actualizar médico
        $doctor->update([
            'specialty' => $validated['specialty'],
            'license_number' => $validated['license_number'],
            'consultation_fee' => 0,
            'is_active' => $validated['is_active'] ?? true
        ]);

        // Si se proporciona nueva contraseña
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $doctor->user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // NUEVO: Actualizar servicios del médico
        if (isset($validated['services'])) {
            $doctor->services()->sync($validated['services']);
        } else {
            $doctor->services()->detach();
        }

        return redirect()->route('doctors.index')
            ->with('success', 'Médico actualizado exitosamente.');
    }

    public function destroy(Doctor $doctor)
    {
        // Verificar si tiene citas asociadas
        if ($doctor->appointments()->count() > 0) {
            return redirect()->route('doctors.index')
                ->with('error', 'No se puede eliminar el médico porque tiene citas asociadas.');
        }

        // Eliminar servicios asociados
        $doctor->services()->detach();

        // Eliminar usuario (cascade eliminará el médico)
        $doctor->user->delete();

        return redirect()->route('doctors.index')
            ->with('success', 'Médico eliminado exitosamente.');
    }

    /**
     * Obtener médicos por servicio (API para el formulario de citas)
     */
    public function getByService(Request $request)
    {
        $serviceId = $request->get('service_id');
        
        if (!$serviceId) {
            return response()->json(['doctors' => []]);
        }

        $doctors = Doctor::where('is_active', true)
            ->whereHas('services', function($q) use ($serviceId) {
                $q->where('service_id', $serviceId)
                  ->where('is_active', true);
            })
            ->with('user')
            ->get()
            ->map(function($doctor) {
                return [
                    'id' => $doctor->id,
                    'full_name' => $doctor->full_name,
                    'specialty' => $doctor->specialty,
                    'consultation_fee' => $doctor->consultation_fee,
                ];
            });

        return response()->json(['doctors' => $doctors]);
    }

    /**
     * Obtener todos los servicios de un médico (API)
     */
    public function getDoctorServices($doctorId)
    {
        $doctor = Doctor::with('services')->find($doctorId);
        
        if (!$doctor) {
            return response()->json(['services' => []]);
        }

        return response()->json([
            'services' => $doctor->services->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                    'duration_minutes' => $service->duration_minutes,
                    'pivot' => [
                        'is_active' => $service->pivot->is_active,
                        'extra_charge' => $service->pivot->extra_charge,
                        'duration_minutes' => $service->pivot->duration_minutes,
                    ]
                ];
            })
        ]);
    }
}