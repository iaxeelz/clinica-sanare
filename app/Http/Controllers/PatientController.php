<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $patients = Patient::when($search, function ($query, $search) {
                return $query->search($search);
            })
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        return view('patients.index', compact('patients', 'search'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:patients',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:M,F,OTRO',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'allergies' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'is_active' => 'sometimes|boolean'
        ]);

        $patient = Patient::create($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Paciente registrado exitosamente.');
    }

    public function show(Patient $patient)
    {
        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dni' => ['required', 'string', 'max:20', Rule::unique('patients')->ignore($patient->id)],
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:M,F,OTRO',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            'allergies' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'is_active' => 'sometimes|boolean'
        ]);

        $patient->update($validated);

        return redirect()->route('patients.index')
            ->with('success', 'Paciente actualizado exitosamente.');
    }

    public function destroy(Patient $patient)
    {
        // Verificar si tiene citas asociadas
        if ($patient->appointments()->count() > 0) {
            return redirect()->route('patients.index')
                ->with('error', 'No se puede eliminar el paciente porque tiene citas asociadas.');
        }

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Paciente eliminado exitosamente.');
    }
}