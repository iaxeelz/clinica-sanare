@extends('vendor.adminlte.layouts.app')

@section('title', 'Mis Horarios')
@section('page-title', 'Mis Horarios de Atención')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Horarios</li>
@endsection

@push('styles')
<style>
    .schedule-card {
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        background: white;
    }
    .schedule-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .schedule-card .card-header-custom {
        background: linear-gradient(135deg, #0F4C81, #1a6ea8);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .schedule-card .card-header-custom i {
        color: #2ECC71;
        margin-right: 8px;
    }
    .schedule-card .card-body-custom {
        padding: 20px;
        text-align: center;
    }
    .day-badge {
        font-size: 14px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        background: #e8f4fd;
        color: #0F4C81;
        display: inline-block;
    }
    .time-badge {
        font-size: 14px;
        padding: 6px 14px;
        border-radius: 8px;
        background: #f0f0f0;
        color: #333;
        display: inline-block;
        margin-top: 8px;
    }
    .empty-state {
        text-align: center;
        padding: 50px 20px;
    }
    .empty-state i {
        font-size: 60px;
        color: #dee2e6;
        margin-bottom: 15px;
    }
    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-action:hover {
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt text-success"></i> Configura tu disponibilidad
                </h3>
                <div class="card-tools">
                    <button class="btn btn-primary btn-sm" id="addScheduleBtn">
                        <i class="fas fa-plus"></i> Agregar Horario
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @if($schedules->count() > 0)
                    <div class="row">
                        @foreach($schedules as $schedule)
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="schedule-card">
                                    <div class="card-header-custom">
                                        <span><i class="fas fa-calendar-day"></i> {{ $schedule->day_name }}</span>
                                        <span class="badge badge-{{ $schedule->is_active ? 'success' : 'danger' }}">
                                            {{ $schedule->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                    <div class="card-body-custom">
                                        <div class="day-badge">
                                            <i class="fas fa-clock"></i> {{ $schedule->start_time_formatted }}
                                        </div>
                                        <div class="time-badge">
                                            <i class="fas fa-arrow-right"></i> {{ $schedule->end_time_formatted }}
                                        </div>
                                        <div class="mt-3 d-flex justify-content-center gap-2">
                                            <button class="btn btn-warning btn-sm edit-schedule mx-1" 
                                                    data-id="{{ $schedule->id }}"
                                                    data-day="{{ $schedule->day_of_week }}"
                                                    data-start="{{ $schedule->start_time }}"
                                                    data-end="{{ $schedule->end_time }}"
                                                    data-active="{{ $schedule->is_active ? 1 : 0 }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm delete-schedule mx-1" 
                                                    data-id="{{ $schedule->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-calendar-plus"></i>
                        <h4>No tienes horarios configurados</h4>
                        <p class="text-muted">Agrega tu disponibilidad para que los pacientes puedan agendar citas.</p>
                        <button class="btn btn-primary" id="addScheduleBtn">
                            <i class="fas fa-plus"></i> Agregar mi primer horario
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar horario -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalTitle">
                    <i class="fas fa-clock"></i> Agregar Horario
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="scheduleForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="schedule_id" name="schedule_id">
                    
                    <div class="form-group">
                        <label for="day_of_week">Día de la Semana *</label>
                        <select class="form-control" id="day_of_week" name="day_of_week" required>
                            <option value="">Seleccionar...</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="7">Domingo</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_time">Hora Inicio *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_time">Hora Fin *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active">Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveScheduleBtn">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Abrir modal para agregar
        $('#addScheduleBtn').on('click', function() {
            $('#scheduleModalTitle').html('<i class="fas fa-clock"></i> Agregar Horario');
            $('#scheduleForm')[0].reset();
            $('#schedule_id').val('');
            $('#is_active').prop('checked', true);
            $('#scheduleModal').modal('show');
        });

        // Editar horario
        $(document).on('click', '.edit-schedule', function() {
            var id = $(this).data('id');
            var day = $(this).data('day');
            var start = $(this).data('start');
            var end = $(this).data('end');
            var active = $(this).data('active');

            $('#scheduleModalTitle').html('<i class="fas fa-edit"></i> Editar Horario');
            $('#schedule_id').val(id);
            $('#day_of_week').val(day);
            $('#start_time').val(start);
            $('#end_time').val(end);
            $('#is_active').prop('checked', active == 1);
            $('#scheduleModal').modal('show');
        });

        // Guardar horario
        $('#scheduleForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            var id = $('#schedule_id').val();
            var url = id ? '/doctor/schedules/' + id : '/doctor/schedules';
            var method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMsg = '';
                    if (errors) {
                        for (var key in errors) {
                            errorMsg += errors[key][0] + '\n';
                        }
                    } else {
                        errorMsg = xhr.responseJSON.message || 'Error al guardar el horario';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg
                    });
                }
            });
        });

        // Eliminar horario
        $(document).on('click', '.delete-schedule', function() {
            var id = $(this).data('id');
            
            Swal.fire({
                title: '¿Eliminar horario?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/doctor/schedules/' + id,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(function() {
                                    location.reload();
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo eliminar el horario'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush