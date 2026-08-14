@extends('vendor.adminlte.layouts.app')

@section('title', 'Editar Cita')
@section('page-title', 'Editar Cita #' . $appointment->id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Citas</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editando Cita #{{ $appointment->id }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('appointments.update', $appointment) }}" method="POST" id="appointmentForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="patient_id">Paciente *</label>
                            <select class="form-control @error('patient_id') is-invalid @enderror" 
                                    id="patient_id" name="patient_id" required>
                                <option value="">Seleccionar paciente...</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name }} - {{ $patient->dni }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="appointment_date">Fecha *</label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                   id="appointment_date" name="appointment_date" 
                                   value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" required>
                            @error('appointment_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="appointment_time">Hora *</label>
                            <select class="form-control @error('appointment_time') is-invalid @enderror" 
                                    id="appointment_time" name="appointment_time" required>
                                <option value="">Seleccionar hora...</option>
                            </select>
                            @error('appointment_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- SERVICIOS Y MÉDICOS (MÚLTIPLES) -->
                <!-- ============================================ -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-list"></i> Servicios y Médicos Asignados
                                </h5>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-primary btn-sm" id="addServiceRow">
                                        <i class="fas fa-plus"></i> Agregar Servicio
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="servicesContainer">
                                    @php $serviceIndex = 0; @endphp
                                    @foreach($appointment->appointmentServices as $appService)
                                        <div class="row service-row mb-3" data-service-id="{{ $appService->id }}">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Servicio *</label>
                                                    <select class="form-control service-select" 
                                                            name="services[{{ $serviceIndex }}][service_id]" 
                                                            data-index="{{ $serviceIndex }}" required>
                                                        <option value="">Seleccionar servicio...</option>
                                                        @foreach($services as $service)
                                                            <option value="{{ $service->id }}" 
                                                                    data-price="{{ $service->price }}" 
                                                                    data-duration="{{ $service->duration_minutes ?? 30 }}"
                                                                    {{ old("services.{$serviceIndex}.service_id", $appService->service_id) == $service->id ? 'selected' : '' }}>
                                                                {{ $service->name }} - S/ {{ number_format($service->price, 2) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="services[{{ $serviceIndex }}][id]" value="{{ $appService->id }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Médico *</label>
                                                    <select class="form-control doctor-select" 
                                                            name="services[{{ $serviceIndex }}][doctor_id]" required>
                                                        <option value="">Seleccionar médico...</option>
                                                        @foreach($doctors as $doctor)
                                                            <option value="{{ $doctor->id }}" 
                                                                    {{ old("services.{$serviceIndex}.doctor_id", $appService->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                                {{ $doctor->full_name }} 
                                                                @if($doctor->specialty)
                                                                    ({{ $doctor->specialty }})
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Duración (min)</label>
                                                    <input type="number" class="form-control duration-input" 
                                                           name="services[{{ $serviceIndex }}][duration_minutes]" 
                                                           value="{{ old("services.{$serviceIndex}.duration_minutes", $appService->duration_minutes) }}" 
                                                           min="5" step="5">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-block remove-service-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @php $serviceIndex++; @endphp
                                    @endforeach
                                </div>

                                <!-- Resumen -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong><i class="fas fa-cubes"></i> Total Servicios:</strong> 
                                                    <span id="totalServices">{{ $appointment->appointmentServices->count() }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong><i class="fas fa-money-bill-wave"></i> Total a Pagar:</strong> 
                                                    S/ <span id="totalPrice">{{ number_format($appointment->total_price, 2) }}</span>
                                                </div>
                                                <div class-col-md-4">
                                                    <strong><i class="fas fa-clock"></i> Duración Total:</strong> 
                                                    <span id="totalDuration">{{ $appointment->total_duration }}</span> min
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Estado *</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="pendiente" {{ old('status', $appointment->status) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="confirmada" {{ old('status', $appointment->status) == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                                <option value="en_curso" {{ old('status', $appointment->status) == 'en_curso' ? 'selected' : '' }}>En Curso</option>
                                <option value="completada" {{ old('status', $appointment->status) == 'completada' ? 'selected' : '' }}>Completada</option>
                                <option value="cancelada" {{ old('status', $appointment->status) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_paid">Estado de Pago</label>
                            <select class="form-control @error('is_paid') is-invalid @enderror" 
                                    id="is_paid" name="is_paid">
                                <option value="0" {{ old('is_paid', $appointment->is_paid ? '1' : '0') == '0' ? 'selected' : '' }}>No Pagado</option>
                                <option value="1" {{ old('is_paid', $appointment->is_paid ? '1' : '0') == '1' ? 'selected' : '' }}>Pagado</option>
                            </select>
                            @error('is_paid')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Si marcas como "Pagado", se creará automáticamente un ingreso en finanzas.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="reason">Motivo de la Consulta</label>
                            <input type="text" class="form-control @error('reason') is-invalid @enderror" 
                                   id="reason" name="reason" value="{{ old('reason', $appointment->reason) }}">
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="notes">Notas</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Actualizar Cita
                        </button>
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let serviceIndex = {{ $appointment->appointmentServices->count() }};

        // ============================================
        // CARGAR HORAS DISPONIBLES
        // ============================================
        function loadAvailableSlots() {
            var doctorId = getSelectedDoctorId();
            var date = $('#appointment_date').val();
            var timeSelect = $('#appointment_time');

            if (!doctorId || !date) {
                timeSelect.html('<option value="">Primero selecciona médico y fecha</option>');
                return;
            }

            timeSelect.html('<option value="">Cargando horarios...</option>');

            fetch('/doctor/slots?doctor_id=' + doctorId + '&date=' + date)
                .then(response => response.json())
                .then(data => {
                    timeSelect.html('<option value="">Seleccionar hora...</option>');
                    
                    if (data.slots && data.slots.length > 0) {
                        var currentTime = '{{ \Carbon\Carbon::parse($appointment->appointment_time)->format("H:i") }}';
                        data.slots.forEach(function(slot) {
                            var selected = (slot == currentTime) ? 'selected' : '';
                            timeSelect.append('<option value="' + slot + '" ' + selected + '>' + slot + '</option>');
                        });
                    } else {
                        timeSelect.append('<option value="">No hay horarios disponibles para este día</option>');
                    }
                })
                .catch(function(error) {
                    console.error('❌ Error cargando horarios:', error);
                    timeSelect.html('<option value="">Error al cargar horarios</option>');
                });
        }

        // ============================================
        // FUNCIÓN PARA OBTENER EL MÉDICO SELECCIONADO
        // ============================================
        function getSelectedDoctorId() {
            var firstRow = document.querySelector('.service-row');
            if (firstRow) {
                var doctorSelect = firstRow.querySelector('.doctor-select');
                return doctorSelect ? doctorSelect.value : null;
            }
            return null;
        }

        // ============================================
        // CARGAR MÉDICOS PARA SERVICIOS EXISTENTES
        // ============================================
        $('.service-select').each(function() {
            const serviceId = $(this).val();
            const row = $(this).closest('.service-row');
            const doctorSelect = row.find('.doctor-select');
            
            if (serviceId) {
                const selectedDoctor = doctorSelect.val();
                
                $.ajax({
                    url: '{{ route("doctors.by-service") }}',
                    method: 'GET',
                    data: { service_id: serviceId },
                    async: false,
                    success: function(response) {
                        let options = '<option value="">Seleccionar médico...</option>';
                        if (response.doctors && response.doctors.length > 0) {
                            response.doctors.forEach(function(doctor) {
                                const selected = doctor.id == selectedDoctor ? 'selected' : '';
                                options += `<option value="${doctor.id}" ${selected}>
                                    ${doctor.full_name} 
                                    ${doctor.specialty ? `(${doctor.specialty})` : ''}
                                </option>`;
                            });
                        }
                        doctorSelect.html(options);
                        // Cargar horarios después de cargar médicos
                        loadAvailableSlots();
                    }
                });
            }
        });

        // ============================================
        // CARGAR HORAS AL CAMBIAR FECHA
        // ============================================
        $('#appointment_date').on('change', function() {
            loadAvailableSlots();
        });

        // ============================================
        // AGREGAR NUEVA FILA DE SERVICIO
        // ============================================
        $('#addServiceRow').on('click', function() {
            const newRow = `
                <div class="row service-row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Servicio *</label>
                            <select class="form-control service-select" name="services[${serviceIndex}][service_id]" required>
                                <option value="">Seleccionar servicio...</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-duration="{{ $service->duration_minutes ?? 30 }}">
                                        {{ $service->name }} - S/ {{ number_format($service->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Médico *</label>
                            <select class="form-control doctor-select" name="services[${serviceIndex}][doctor_id]" required>
                                <option value="">Primero selecciona un servicio</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Duración (min)</label>
                            <input type="number" class="form-control duration-input" 
                                   name="services[${serviceIndex}][duration_minutes]" 
                                   value="30" min="5" step="5">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-block remove-service-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#servicesContainer').append(newRow);
            serviceIndex++;
            updateTotals();
        });

        // ============================================
        // ELIMINAR FILA DE SERVICIO
        // ============================================
        $(document).on('click', '.remove-service-row', function() {
            if ($('.service-row').length > 1) {
                const serviceId = $(this).closest('.service-row').data('service-id');
                if (serviceId) {
                    const hiddenInput = `<input type="hidden" name="deleted_services[]" value="${serviceId}">`;
                    $('#servicesContainer').append(hiddenInput);
                }
                $(this).closest('.service-row').remove();
                updateTotals();
                loadAvailableSlots();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No se puede eliminar',
                    text: 'Debe haber al menos un servicio.',
                    confirmButtonColor: '#3085d6',
                });
            }
        });

        // ============================================
        // CARGAR MÉDICOS AL SELECCIONAR SERVICIO
        // ============================================
        $(document).on('change', '.service-select', function() {
            const serviceId = $(this).val();
            const row = $(this).closest('.service-row');
            const doctorSelect = row.find('.doctor-select');
            const durationInput = row.find('.duration-input');
            
            const duration = $(this).find(':selected').data('duration') || 30;
            durationInput.val(duration);
            
            if (!serviceId) {
                doctorSelect.html('<option value="">Primero selecciona un servicio</option>');
                updateTotals();
                return;
            }

            $.ajax({
                url: '{{ route("doctors.by-service") }}',
                method: 'GET',
                data: { service_id: serviceId },
                beforeSend: function() {
                    doctorSelect.html('<option value="">Cargando médicos...</option>');
                },
                success: function(response) {
                    let options = '<option value="">Seleccionar médico...</option>';
                    if (response.doctors && response.doctors.length > 0) {
                        response.doctors.forEach(function(doctor) {
                            options += `<option value="${doctor.id}">
                                ${doctor.full_name} 
                                ${doctor.specialty ? `(${doctor.specialty})` : ''}
                            </option>`;
                        });
                    } else {
                        options = '<option value="">No hay médicos disponibles para este servicio</option>';
                    }
                    doctorSelect.html(options);
                    updateTotals();
                    loadAvailableSlots();
                },
                error: function() {
                    doctorSelect.html('<option value="">Error al cargar médicos</option>');
                }
            });
        });

        // ============================================
        // ACTUALIZAR TOTALES
        // ============================================
        function updateTotals() {
            let totalPrice = 0;
            let totalDuration = 0;
            let serviceCount = $('.service-row').length;

            $('.service-row').each(function() {
                const serviceSelect = $(this).find('.service-select');
                const selected = serviceSelect.find(':selected');
                const price = parseFloat(selected.data('price')) || 0;
                const duration = parseInt($(this).find('.duration-input').val()) || 0;
                totalPrice += price;
                totalDuration += duration;
            });

            $('#totalServices').text(serviceCount);
            $('#totalPrice').text(totalPrice.toFixed(2));
            $('#totalDuration').text(totalDuration);
        }

        // ============================================
        // ACTUALIZAR AL CAMBIAR DURACIÓN
        // ============================================
        $(document).on('input', '.duration-input', function() {
            updateTotals();
        });

        // ============================================
        // INICIALIZAR
        // ============================================
        updateTotals();

        // Cargar horarios iniciales
        setTimeout(function() {
            loadAvailableSlots();
        }, 500);

        // ============================================
        // VALIDACIÓN ANTES DE ENVIAR
        // ============================================
        $('#appointmentForm').on('submit', function(e) {
            let hasError = false;
            
            $('.service-row').each(function() {
                const serviceId = $(this).find('.service-select').val();
                const doctorId = $(this).find('.doctor-select').val();
                
                if (!serviceId) {
                    hasError = true;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Por favor selecciona un servicio para todas las filas.',
                        confirmButtonColor: '#3085d6',
                    });
                    return false;
                }
                
                if (!doctorId) {
                    hasError = true;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Por favor selecciona un médico para todas las filas.',
                        confirmButtonColor: '#3085d6',
                    });
                    return false;
                }
            });
            
            if (!timeSelect.value) {
                hasError = true;
                alert('⚠️ Por favor selecciona una hora disponible.');
            }
            
            if (hasError) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush