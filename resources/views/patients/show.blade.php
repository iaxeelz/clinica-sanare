@extends('vendor.adminlte.layouts.app')

@section('title', 'Detalle Paciente')
@section('page-title', 'Detalle del Paciente')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('patients.index') }}">Pacientes</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $patient->full_name }}</h3>
            <div class="card-tools">
                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">ID</th>
                            <td>{{ $patient->id }}</td>
                        </tr>
                        <tr>
                            <th>Nombres</th>
                            <td>{{ $patient->first_name }}</td>
                        </tr>
                        <tr>
                            <th>Apellidos</th>
                            <td>{{ $patient->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Nombre Completo</th>
                            <td><strong>{{ $patient->full_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>DNI</th>
                            <td>{{ $patient->dni }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de Nacimiento</th>
                            <td>{{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Género</th>
                            <td>
                                @if($patient->gender == 'M')
                                    <span class="badge badge-info">Masculino</span>
                                @elseif($patient->gender == 'F')
                                    <span class="badge badge-pink">Femenino</span>
                                @elseif($patient->gender == 'OTRO')
                                    <span class="badge badge-secondary">Otro</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Teléfono</th>
                            <td>{{ $patient->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $patient->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Dirección</th>
                            <td>{{ $patient->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Contacto Emergencia</th>
                            <td>{{ $patient->emergency_contact ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Alergias</th>
                            <td>{{ $patient->allergies ?? 'Ninguna' }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td>
                                @if($patient->is_active)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Registrado</th>
                            <td>{{ $patient->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Historial Médico</h5>
                        </div>
                        <div class="card-body">
                            {{ $patient->medical_history ?? 'Sin historial médico registrado.' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection