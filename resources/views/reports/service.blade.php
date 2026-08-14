@extends('vendor.adminlte.layouts.app')

@section('title', 'Reporte por Servicio')
@section('page-title', 'Reporte de Citas por Servicio')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Por Servicio</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.service') }}" method="GET" class="form-inline flex-wrap">
                <div class="form-group mr-2 mb-2">
                    <label for="start_date" class="mr-2">Desde:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="end_date" class="mr-2">Hasta:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="service_id" class="mr-2">Servicio:</label>
                    <select name="service_id" id="service_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ $serviceId == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Generar
                </button>
                <a href="{{ route('reports.service') }}" class="btn btn-secondary mb-2 ml-2">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </form>
        </div>
    </div>

    <!-- Estadísticas por servicio -->
    @if($serviceStats->count() > 0)
        <div class="row">
            @foreach($serviceStats as $stat)
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stat->total }}</h3>
                            <p>{{ $stat->service->name ?? 'Sin servicio' }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Lista de citas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Citas Registradas</h3>
            <div class="card-tools">
                <span class="badge badge-primary">Total: {{ $appointments->count() }}</span>
            </div>
        </div>
        <div class="card-body">
            @if($appointments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</td>
                                    <td>{{ $appointment->patient->full_name }}</td>
                                    <td>{{ $appointment->doctor->full_name }}</td>
                                    <td>{{ $appointment->service->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $appointment->status_color }}">
                                            {{ $appointment->status_text }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-muted">No hay citas en el período seleccionado.</p>
            @endif
        </div>
    </div>
@endsection