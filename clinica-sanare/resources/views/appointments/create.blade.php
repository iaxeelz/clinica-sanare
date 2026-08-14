@extends('vendor.adminlte.layouts.app')

@section('title', 'Nueva Cita')
@section('page-title', 'Registrar Nueva Cita')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Citas</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos de la Cita</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('appointments.store') }}" method="POST" id="appointmentForm">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="patient_id">Paciente *</label>
                            <select class="form-control @error('patient_id') is-invalid @enderror" id="patient_id"
                                name="patient_id" required>
                                <option value="">Seleccionar paciente...</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
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
                            <input type="text" class="form-control @error('appointment_date') is-invalid @enderror"
                                id="appointment_date" name="appointment_date"
                                placeholder="Seleccionar fecha..."
                                required>
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
                                <option value="">Primero selecciona médico y fecha</option>
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
                                    <!-- Fila 1 (por defecto) -->
                                    <div class="row service-row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Servicio *</label>
                                                <select class="form-control service-select" name="services[0][service_id]"
                                                    required>
                                                    <option value="">Seleccionar servicio...</option>
                                                    @foreach($services as $service)
                                                        <option value="{{ $service->id }}" data-price="{{ $service->price }}"
                                                            data-duration="{{ $service->duration_minutes ?? 30 }}">
                                                            {{ $service->name }} - S/ {{ number_format($service->price, 2) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Médico *</label>
                                                <select class="form-control doctor-select" name="services[0][doctor_id]"
                                                    required>
                                                    <option value="">Primero selecciona un servicio</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Duración (min)</label>
                                                <input type="number" class="form-control duration-input"
                                                    name="services[0][duration_minutes]" value="30" min="5" step="5">
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
                                </div>

                                <!-- Resumen -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong><i class="fas fa-cubes"></i> Total Servicios:</strong>
                                                    <span id="totalServices">1</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong><i class="fas fa-money-bill-wave"></i> Total a Pagar:</strong>
                                                    S/ <span id="totalPrice">0.00</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong><i class="fas fa-clock"></i> Duración Total:</strong>
                                                    <span id="totalDuration">0</span> min
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
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status"
                                required>
                                <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente
                                </option>
                                <option value="confirmada" {{ old('status') == 'confirmada' ? 'selected' : '' }}>Confirmada
                                </option>
                                <option value="en_curso" {{ old('status') == 'en_curso' ? 'selected' : '' }}>En Curso</option>
                                <option value="completada" {{ old('status') == 'completada' ? 'selected' : '' }}>Completada
                                </option>
                                <option value="cancelada" {{ old('status') == 'cancelada' ? 'selected' : '' }}>Cancelada
                                </option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_paid">Estado de Pago</label>
                            <select class="form-control @error('is_paid') is-invalid @enderror" id="is_paid" name="is_paid">
                                <option value="0" {{ old('is_paid', '0') == '0' ? 'selected' : '' }}>No Pagado</option>
                                <option value="1" {{ old('is_paid') == '1' ? 'selected' : '' }}>Pagado</option>
                            </select>
                            @error('is_paid')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Si marcas como "Pagado", se creará automáticamente un
                                ingreso en finanzas.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="reason">Motivo de la Consulta</label>
                            <input type="text" class="form-control @error('reason') is-invalid @enderror" id="reason"
                                name="reason" value="{{ old('reason') }}">
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
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes"
                                rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Cita
                        </button>
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- SCRIPT DIRECTO (DENTRO DEL CONTENIDO) -->
    <!-- ============================================ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔥 JS de citas cargado con Flatpickr');

            var addBtn = document.getElementById('addServiceRow');
            var container = document.getElementById('servicesContainer');
            var serviceIndex = document.querySelectorAll('.service-row').length || 1;
            var timeSelect = document.getElementById('appointment_time');
            var dateInput = document.getElementById('appointment_date');

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
            // INICIALIZAR FLATPICKR
            // ============================================
            var datePicker = flatpickr("#appointment_date", {
                locale: 'es',
                dateFormat: "Y-m-d",
                minDate: "today",
                maxDate: new Date().fp_incr(90), // 90 días hacia adelante
                disable: [
                    function(date) {
                        var doctorId = getSelectedDoctorId();
                        if (!doctorId) return true;
                        
                        var dayOfWeek = date.getDay();
                        var dayMap = [7, 1, 2, 3, 4, 5, 6];
                        var dbDay = dayMap[dayOfWeek];
                        
                        var availableDays = window.availableDays || [];
                        var dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        var dayName = dayNames[dayOfWeek];
                        
                        return !availableDays.includes(dayName);
                    }
                ],
                onOpen: function(selectedDates, dateStr, instance) {
                    var doctorId = getSelectedDoctorId();
                    if (doctorId) {
                        loadAvailableDays(doctorId);
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        loadAvailableSlots();
                    }
                }
            });

            // ============================================
            // CARGAR DÍAS DISPONIBLES DEL MÉDICO
            // ============================================
            function loadAvailableDays(doctorId) {
                console.log('🔍 Cargando días disponibles para médico:', doctorId);
                
                fetch('/doctor/available-days?doctor_id=' + doctorId)
                    .then(response => response.json())
                    .then(data => {
                        window.availableDays = data.days || [];
                        console.log('✅ Días disponibles:', window.availableDays);
                        
                        datePicker.set('disable', [
                            function(date) {
                                if (!window.availableDays || window.availableDays.length === 0) return true;
                                
                                var dayOfWeek = date.getDay();
                                var dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                var dayName = dayNames[dayOfWeek];
                                
                                return !window.availableDays.includes(dayName);
                            }
                        ]);
                        datePicker.redraw();
                    })
                    .catch(error => {
                        console.error('❌ Error cargando días:', error);
                    });
            }

            // ============================================
            // CARGAR HORAS DISPONIBLES
            // ============================================
            function loadAvailableSlots() {
                var doctorId = getSelectedDoctorId();
                var date = dateInput ? dateInput.value : null;

                console.log('🔍 Cargando horarios - Doctor:', doctorId, 'Fecha:', date);

                if (!doctorId) {
                    timeSelect.innerHTML = '<option value="">Primero selecciona un médico</option>';
                    return;
                }

                if (!date) {
                    timeSelect.innerHTML = '<option value="">Primero selecciona una fecha</option>';
                    return;
                }

                timeSelect.innerHTML = '<option value="">Cargando horarios...</option>';

                fetch('/doctor/slots?doctor_id=' + doctorId + '&date=' + date)
                    .then(response => response.json())
                    .then(data => {
                        console.log('✅ Horarios recibidos:', data);
                        timeSelect.innerHTML = '<option value="">Seleccionar hora...</option>';
                        
                        if (data.slots && data.slots.length > 0) {
                            data.slots.forEach(function(slot) {
                                var option = document.createElement('option');
                                option.value = slot;
                                option.textContent = slot;
                                timeSelect.appendChild(option);
                            });
                        } else {
                            var option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'No hay horarios disponibles para este día';
                            timeSelect.appendChild(option);
                        }
                    })
                    .catch(function(error) {
                        console.error('❌ Error cargando horarios:', error);
                        timeSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
                    });
            }

            // ============================================
            // ESCUCHAR CAMBIOS EN MÉDICO
            // ============================================
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('doctor-select')) {
                    console.log('🔄 Cambió el médico:', e.target.value);
                    loadAvailableDays(e.target.value);
                    loadAvailableSlots();
                }
            });

            // ============================================
            // AGREGAR FILA
            // ============================================
            if (addBtn) {
                addBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('🔄 Agregando servicio...');

                    var firstSelect = document.querySelector('.service-select');
                    var options = '<option value="">Seleccionar servicio...</option>';
                    if (firstSelect) {
                        var firstOptions = firstSelect.querySelectorAll('option');
                        firstOptions.forEach(function(opt) {
                            if (opt.value) {
                                options += '<option value="' + opt.value + '" data-price="' + (opt.getAttribute(
                                        'data-price') || 0) + '" data-duration="' + (opt.getAttribute(
                                        'data-duration') || 30) + '">' + opt.text + '</option>';
                            }
                        });
                    }

                    var newRow = document.createElement('div');
                    newRow.className = 'row service-row mb-3';
                    newRow.innerHTML = `
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Servicio *</label>
                                    <select class="form-control service-select" name="services[${serviceIndex}][service_id]" required>
                                        ${options}
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
                        `;

                    container.appendChild(newRow);
                    serviceIndex++;
                    updateTotals();
                    
                    var doctorId = getSelectedDoctorId();
                    if (doctorId) {
                        loadAvailableDays(doctorId);
                        loadAvailableSlots();
                    }
                });
            }

            // ============================================
            // ELIMINAR FILA
            // ============================================
            document.addEventListener('click', function(e) {
                var removeBtn = e.target.closest('.remove-service-row');
                if (removeBtn) {
                    var rows = document.querySelectorAll('.service-row');
                    if (rows.length > 1) {
                        removeBtn.closest('.service-row').remove();
                        updateTotals();
                        
                        var doctorId = getSelectedDoctorId();
                        if (doctorId) {
                            loadAvailableDays(doctorId);
                            loadAvailableSlots();
                        }
                    } else {
                        alert('Debe haber al menos un servicio.');
                    }
                }
            });

            // ============================================
            // CARGAR MÉDICOS AL SELECCIONAR SERVICIO
            // ============================================
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('service-select')) {
                    var serviceId = e.target.value;
                    var row = e.target.closest('.service-row');
                    var doctorSelect = row.querySelector('.doctor-select');
                    var durationInput = row.querySelector('.duration-input');

                    var selected = e.target.options[e.target.selectedIndex];
                    var duration = parseInt(selected.getAttribute('data-duration')) || 30;
                    if (durationInput) durationInput.value = duration;

                    if (!serviceId) {
                        doctorSelect.innerHTML = '<option value="">Primero selecciona un servicio</option>';
                        updateTotals();
                        return;
                    }

                    fetch('/get-doctors-by-service?service_id=' + serviceId)
                        .then(response => response.json())
                        .then(data => {
                            var options = '<option value="">Seleccionar médico...</option>';
                            if (data.doctors && data.doctors.length > 0) {
                                data.doctors.forEach(function(doctor) {
                                    options += `<option value="${doctor.id}">
                                                ${doctor.full_name} 
                                                ${doctor.specialty ? '(' + doctor.specialty + ')' : ''}
                                            </option>`;
                                });
                            } else {
                                options = '<option value="">No hay médicos disponibles</option>';
                            }
                            doctorSelect.innerHTML = options;
                            updateTotals();
                            
                            var doctorId = getSelectedDoctorId();
                            if (doctorId) {
                                loadAvailableDays(doctorId);
                                loadAvailableSlots();
                            }
                        })
                        .catch(function(error) {
                            console.error('Error al cargar médicos:', error);
                            doctorSelect.innerHTML = '<option value="">Error al cargar médicos</option>';
                        });
                }

                if (e.target.classList.contains('duration-input')) {
                    updateTotals();
                }
            });

            // ============================================
            // ACTUALIZAR TOTALES
            // ============================================
            function updateTotals() {
                var totalPrice = 0;
                var totalDuration = 0;
                var serviceCount = document.querySelectorAll('.service-row').length;

                document.querySelectorAll('.service-row').forEach(function(row) {
                    var select = row.querySelector('.service-select');
                    var selected = select.options[select.selectedIndex];
                    var price = parseFloat(selected.getAttribute('data-price')) || 0;
                    var durationInput = row.querySelector('.duration-input');
                    var duration = parseInt(durationInput.value) || 0;
                    totalPrice += price;
                    totalDuration += duration;
                });

                document.getElementById('totalServices').textContent = serviceCount;
                document.getElementById('totalPrice').textContent = totalPrice.toFixed(2);
                document.getElementById('totalDuration').textContent = totalDuration;
            }

            // ============================================
            // INICIALIZAR
            // ============================================
            updateTotals();

            document.querySelectorAll('.service-select').forEach(function(select) {
                if (select.value) {
                    var event = new Event('change');
                    select.dispatchEvent(event);
                }
            });

            var initialDoctorId = getSelectedDoctorId();
            if (initialDoctorId) {
                loadAvailableDays(initialDoctorId);
            }

            // ============================================
            // VALIDAR ANTES DE ENVIAR
            // ============================================
            document.getElementById('appointmentForm').addEventListener('submit', function(e) {
                var rows = document.querySelectorAll('.service-row');
                var hasError = false;

                rows.forEach(function(row) {
                    var serviceSelect = row.querySelector('.service-select');
                    var doctorSelect = row.querySelector('.doctor-select');

                    if (!serviceSelect.value) {
                        hasError = true;
                    }
                    if (!doctorSelect.value) {
                        hasError = true;
                    }
                });

                if (!dateInput.value) {
                    hasError = true;
                    alert('⚠️ Por favor selecciona una fecha disponible.');
                }

                if (!timeSelect.value) {
                    hasError = true;
                    alert('⚠️ Por favor selecciona una hora disponible.');
                }

                if (hasError) {
                    e.preventDefault();
                    alert('⚠️ Por favor completa todos los campos requeridos.');
                }
            });

            console.log('✅ JS de citas configurado correctamente con Flatpickr');
        });
    </script>

@endsection