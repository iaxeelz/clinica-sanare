@extends('vendor.adminlte.layouts.app')

@section('title', 'Detalle de Cita')
@section('page-title', 'Detalle de Cita #' . $appointment->id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Citas</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la Cita</h3>
            <div class="card-tools">
                <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('appointments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Información General -->
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Paciente</span>
                            <span class="info-box-number">{{ $appointment->patient->full_name ?? 'N/A' }}</span>
                            <small>DNI: {{ $appointment->patient->dni ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha y Hora</span>
                            <span class="info-box-number">{{ $appointment->appointment_date->format('d/m/Y') }}</span>
                            <small>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado y Pago -->
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $appointment->status_color }}"><i class="fas fa-tag"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado</span>
                            <span class="info-box-number">
                                <span class="badge badge-{{ $appointment->status_color }}">
                                    {{ $appointment->status_text }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $appointment->payment_status_color }}"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Estado de Pago</span>
                            <span class="info-box-number">
                                <span class="badge badge-{{ $appointment->payment_status_color }}">
                                    {{ $appointment->payment_status_text }}
                                </span>
                            </span>
                            @if($appointment->is_paid)
                                <small>Método: {{ $appointment->payment_method_text }}</small>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Duración Total</span>
                            <span class="info-box-number">{{ $appointment->total_duration }} min</span>
                            <small>{{ $appointment->appointmentServices->count() }} servicio(s)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- SERVICIOS Y MÉDICOS ASIGNADOS -->
            <!-- ============================================ -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-list"></i> Servicios y Médicos Asignados
                            </h5>
                            <div class="card-tools">
                                <span class="badge badge-primary">{{ $appointment->appointmentServices->count() }} servicios</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Servicio</th>
                                            <th>Médico</th>
                                            <th>Duración</th>
                                            <th>Precio</th>
                                            <th>Horario</th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $counter = 1; @endphp
                                        @foreach($appointment->appointmentServices as $appService)
                                            @php
                                                $startTime = $appointment->getServiceStartTime($appService->id);
                                                $endTime = $appointment->getServiceEndTime($appService->id);
                                            @endphp
                                            <tr>
                                                <td>{{ $counter++ }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ $appService->service->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{{ $appService->doctor->full_name ?? 'N/A' }}</strong>
                                                    @if($appService->doctor->specialty)
                                                        <br><small class="text-muted">{{ $appService->doctor->specialty }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $appService->duration_minutes }} min</td>
                                                <td><strong>S/ {{ number_format($appService->price, 2) }}</strong></td>
                                                <td>
                                                    <small>
                                                        <i class="fas fa-clock text-muted"></i>
                                                        {{ $startTime->format('h:i A') }} - {{ $endTime->format('h:i A') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @if($appService->notes)
                                                        <span class="text-muted" title="{{ $appService->notes }}">
                                                            <i class="fas fa-comment"></i> {{ Str::limit($appService->notes, 30) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active font-weight-bold">
                                            <td colspan="3" class="text-right">TOTALES:</td>
                                            <td>{{ $appointment->total_duration }} min</td>
                                            <td>S/ {{ number_format($appointment->total_price, 2) }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-comment"></i> Motivo de la Consulta</h5>
                        </div>
                        <div class="card-body">
                            @if($appointment->reason)
                                <p>{{ $appointment->reason }}</p>
                            @else
                                <p class="text-muted">No se registró motivo de consulta.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-sticky-note"></i> Notas Adicionales</h5>
                        </div>
                        <div class="card-body">
                            @if($appointment->notes)
                                <p>{{ $appointment->notes }}</p>
                            @else
                                <p class="text-muted">No hay notas adicionales.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Registro -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-light">
                        <i class="fas fa-info-circle"></i>
                        <strong>Registrado por:</strong> {{ $appointment->user->name ?? 'N/A' }}
                        <span class="mx-3">|</span>
                        <strong>Fecha de registro:</strong> {{ $appointment->created_at->format('d/m/Y h:i A') }}
                        @if($appointment->created_at != $appointment->updated_at)
                            <span class="mx-3">|</span>
                            <strong>Última actualización:</strong> {{ $appointment->updated_at->format('d/m/Y h:i A') }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta cita?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                    <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection