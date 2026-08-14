@extends('vendor.adminlte.layouts.app')

@section('title', 'Reporte de Pacientes')
@section('page-title', 'Reporte de Pacientes')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Pacientes</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.patient') }}" method="GET" class="form-inline flex-wrap">
                <div class="input-group mr-2 mb-2">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar paciente..." value="{{ $search ?? '' }}">
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="start_date" class="mr-2">Desde:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="end_date" class="mr-2">Hasta:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Generar
                </button>
                <a href="{{ route('reports.patient') }}" class="btn btn-secondary mb-2 ml-2">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Pacientes</h3>
            <div class="card-tools">
                <span class="badge badge-primary">Total: {{ $patients->total() }}</span>
            </div>
        </div>
        <div class="card-body">
            @if($patients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>DNI</th>
                                <th>Teléfono</th>
                                <th>Citas ({{ $startDate }} - {{ $endDate }})</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                                <tr>
                                    <td>{{ $patient->first_name }}</td>
                                    <td>{{ $patient->last_name }}</td>
                                    <td>{{ $patient->dni }}</td>
                                    <td>{{ $patient->phone ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $patient->appointments_count }}</span>
                                    </td>
                                    <td>
                                        @if($patient->is_active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $patients->links() }}
                </div>
            @else
                <p class="text-center text-muted">No hay pacientes registrados.</p>
            @endif
        </div>
    </div>
@endsection