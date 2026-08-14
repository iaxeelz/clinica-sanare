@extends('vendor.adminlte.layouts.app')

@section('title', 'Detalle Médico')
@section('page-title', 'Detalle del Médico')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('doctors.index') }}">Médicos</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $doctor->full_name }}</h3>
            <div class="card-tools">
                <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-warning btn-sm">
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
                            <td>{{ $doctor->id }}</td>
                        </tr>
                        <tr>
                            <th>Nombre Completo</th>
                            <td><strong>{{ $doctor->full_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $doctor->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Teléfono</th>
                            <td>{{ $doctor->user->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Especialidad</th>
                            <td>{{ $doctor->specialty }}</td>
                        </tr>
                        <tr>
                            <th>N° Licencia</th>
                            <td>{{ $doctor->license_number }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Estado</th>
                            <td>
                                @if($doctor->is_active)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Registrado</th>
                            <td>{{ $doctor->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Última actualización</th>
                            <td>{{ $doctor->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <a href="{{ route('doctors.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection