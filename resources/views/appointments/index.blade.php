@extends('vendor.adminlte.layouts.app')

@section('title', 'Lista de Citas')
@section('page-title', 'Lista de Citas')
@section('breadcrumb')
    <li class="breadcrumb-item active">Citas</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Citas Registradas</h3>
            <div class="card-tools">
                <a href="{{ route('appointments.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Nueva Cita
                </a>
                <a href="{{ route('appointments.calendar') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-calendar-alt"></i> Calendario
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" action="{{ route('appointments.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar paciente, médico..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="">Todos los estados</option>
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption }}" 
                                        {{ request('status') == $statusOption ? 'selected' : '' }}>
                                        {{ ucfirst($statusOption) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="date" name="date" class="form-control" 
                                   value="{{ request('date') }}" placeholder="Filtrar por fecha">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Tabla de Citas -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Paciente</th>
                            <th>Servicios</th>
                            <th>Médicos</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Duración</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td>{{ $appointment->id }}</td>
                                <td>
                                    <strong>{{ $appointment->patient->full_name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">DNI: {{ $appointment->patient->dni ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @php $serviceCount = $appointment->appointmentServices->count(); @endphp
                                    @if($serviceCount > 0)
                                        <span class="badge badge-info">{{ $serviceCount }} servicio(s)</span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $appointment->services_list }}
                                        </small>
                                    @else
                                        <span class="text-muted">Sin servicios</span>
                                    @endif
                                </td>
                                <td>
                                    @if($appointment->appointmentServices->count() > 0)
                                        <small class="text-muted">
                                            {{ $appointment->doctors_list }}
                                        </small>
                                    @else
                                        <span class="text-muted">Sin médicos</span>
                                    @endif
                                </td>
                                <td>{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</td>
                                <td>
                                    <span class="badge badge-secondary">
                                        {{ $appointment->total_duration }} min
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $appointment->status_color }}">
                                        {{ $appointment->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $appointment->payment_status_color }}">
                                        {{ $appointment->payment_status_text }}
                                    </span>
                                    @if($appointment->is_paid && $appointment->payment_method)
                                        <br><small class="text-muted">{{ $appointment->payment_method_text }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('appointments.show', $appointment) }}" 
                                           class="btn btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('appointments.edit', $appointment) }}" 
                                           class="btn btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" 
                                                onclick="eliminarCita({{ $appointment->id }})" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay citas registradas</p>
                                    <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Crear primera cita
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-3">
                {{ $appointments->links() }}
            </div>
        </div>
    </div>

    <!-- Modal para eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de eliminar esta cita?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function eliminarCita(id) {
        const form = document.getElementById('deleteForm');
        form.action = '/appointments/' + id;
        $('#deleteModal').modal('show');
    }
</script>
@endpush