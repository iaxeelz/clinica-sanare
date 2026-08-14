@extends('vendor.adminlte.layouts.app')

@section('title', 'Calendario de Citas')
@section('page-title', 'Calendario de Citas')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Citas</a></li>
    <li class="breadcrumb-item active">Calendario</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<style>
    .fc-event-title {
        font-weight: 600;
        font-size: 12px;
    }
    .fc-event-time {
        font-size: 11px;
    }
    .fc-daygrid-event {
        cursor: pointer;
    }
    .fc-daygrid-event:hover {
        opacity: 0.8;
    }
    .fc-timegrid-event {
        cursor: pointer;
    }
    .fc-timegrid-event:hover {
        opacity: 0.8;
    }
    .badge-service {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 3px;
        margin: 1px;
        display: inline-block;
    }
    .service-list {
        font-size: 11px;
        margin-top: 2px;
    }
    .service-list .badge {
        font-size: 10px;
    }
    .fc-event.blocked-slot {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        opacity: 0.8;
    }
    .fc-event.blocked-slot .fc-event-title {
        color: white !important;
    }
    
    /* Día bloqueado (tachado) */
    .fc-daygrid-day.blocked-day {
        background: repeating-linear-gradient(
            45deg,
            transparent,
            transparent 10px,
            rgba(220, 53, 69, 0.08) 10px,
            rgba(220, 53, 69, 0.08) 20px
        ) !important;
        opacity: 0.7;
    }
    .fc-daygrid-day.blocked-day .fc-daygrid-day-number {
        color: #dc3545 !important;
        font-weight: 700;
        text-decoration: line-through;
    }
    .fc-daygrid-day.blocked-day .fc-daygrid-day-number::after {
        content: " 🚫";
        font-size: 12px;
    }
    
    /* Modo tachar activo */
    .block-mode-active .fc-daygrid-day {
        cursor: crosshair !important;
        transition: all 0.2s;
    }
    .block-mode-active .fc-daygrid-day:hover {
        background-color: rgba(220, 53, 69, 0.1) !important;
        border: 2px dashed #dc3545 !important;
    }
    
    .btn-tachar {
        transition: all 0.3s;
    }
    .btn-tachar.active {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        color: white !important;
        animation: pulse-btn 1.5s infinite;
    }
    
    @keyframes pulse-btn {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
</style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-alt"></i> Calendario
            </h3>
            <div class="card-tools">
                <a href="{{ route('appointments.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Nueva Cita
                </a>
                <a href="{{ route('appointments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-list"></i> Lista
                </a>
                @if(auth()->user()->hasRole(['medico', 'enfermera']))
                    <button class="btn btn-warning btn-sm btn-tachar" id="toggleBlockMode">
                        <i class="fas fa-pencil-alt"></i> Tachar Día
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="doctorFilter">Filtrar por Médico</label>
                        <select id="doctorFilter" class="form-control">
                            <option value="">Todos los médicos</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">
                                    {{ $doctor->full_name }}
                                    @if($doctor->specialty)
                                        ({{ $doctor->specialty }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="viewFilter">Vista</label>
                        <select id="viewFilter" class="form-control">
                            <option value="dayGridMonth">Mes</option>
                            <option value="timeGridWeek" selected>Semana</option>
                            <option value="timeGridDay">Día</option>
                            <option value="listWeek">Lista Semanal</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button class="btn btn-info" id="refreshCalendar">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                            <button class="btn btn-primary" id="todayBtn">
                                <i class="fas fa-calendar-day"></i> Hoy
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendario -->
            <div id="calendar" style="min-height: 600px;"></div>
        </div>
    </div>

    <!-- Modal para ver detalles de la cita -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-check"></i> Detalles de la Cita
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="eventDetails">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando detalles...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <a href="#" id="viewAppointmentBtn" class="btn btn-info">
                        <i class="fas fa-eye"></i> Ver Cita
                    </a>
                    <a href="#" id="editAppointmentBtn" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔥 Calendario iniciando...');
        
        var calendarEl = document.getElementById('calendar');
        var doctorFilter = document.getElementById('doctorFilter');
        var viewFilter = document.getElementById('viewFilter');
        var isDoctor = {{ auth()->user()->hasRole(['medico', 'enfermera']) ? 'true' : 'false' }};
        var blockModeActive = false;
        var toggleBtn = document.getElementById('toggleBlockMode');
        var blockedDays = [];

        // Obtener el ID del médico actual
        var currentDoctorId = null;
        @if(auth()->user()->hasRole(['medico', 'enfermera']))
            @php
                $doctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
            @endphp
            currentDoctorId = {{ $doctor->id ?? 'null' }};
            if (currentDoctorId) {
                doctorFilter.value = currentDoctorId;
                doctorFilter.disabled = true;
            }
        @endif

        // ============================================
        // CARGAR DÍAS BLOQUEADOS
        // ============================================
        function loadBlockedDays(doctorId) {
            if (!doctorId) return;
            
            fetch('/doctor/blocked-days?doctor_id=' + doctorId)
                .then(response => response.json())
                .then(data => {
                    blockedDays = data.days || [];
                    console.log('📅 Días bloqueados:', blockedDays);
                    applyBlockedDays();
                })
                .catch(error => console.error('Error cargando días bloqueados:', error));
        }

        // ============================================
        // APLICAR ESTILO A DÍAS BLOQUEADOS
        // ============================================
        function applyBlockedDays() {
            document.querySelectorAll('.fc-daygrid-day').forEach(function(dayEl) {
                var dateAttr = dayEl.getAttribute('data-date');
                if (dateAttr && blockedDays.includes(dateAttr)) {
                    dayEl.classList.add('blocked-day');
                } else if (dateAttr) {
                    dayEl.classList.remove('blocked-day');
                }
            });
        }

        // ============================================
        // TOGGLE DEL MODO TACHAR
        // ============================================
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                blockModeActive = !blockModeActive;
                
                if (blockModeActive) {
                    this.classList.add('active');
                    this.innerHTML = '<i class="fas fa-times"></i> Salir Tachar';
                    calendarEl.classList.add('block-mode-active');
                    
                    Swal.fire({
                        icon: 'info',
                        title: '✏️ Modo Tachar Activado',
                        text: 'Haz clic en un día del calendario para tacharlo',
                        timer: 2000,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });
                } else {
                    this.classList.remove('active');
                    this.innerHTML = '<i class="fas fa-pencil-alt"></i> Tachar Día';
                    calendarEl.classList.remove('block-mode-active');
                    
                    Swal.fire({
                        icon: 'info',
                        title: 'Modo Tachar Desactivado',
                        timer: 1500,
                        showConfirmButton: false,
                        position: 'top-end',
                        toast: true
                    });
                }
            });
        }

        // ============================================
        // BLOQUEAR DÍA COMPLETO
        // ============================================
        function blockFullDay(date, reason) {
            var data = { date: date, reason: reason || 'Día no disponible' };

            fetch('/doctor/block-day', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al bloquear día: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Día bloqueado',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo bloquear el día'
                    });
                }
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al bloquear el día: ' + error.message
                });
            });
        }

        // ============================================
        // BLOQUEAR RANGO DE HORAS
        // ============================================
        function blockHourRange(date, startTime, endTime, reason) {
            var data = {
                date: date,
                start_time: startTime,
                end_time: endTime,
                reason: reason || 'Rango no disponible'
            };

            fetch('/doctor/unavailable-slots', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al bloquear horas: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Horas bloqueadas',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudieron bloquear las horas'
                    });
                }
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al bloquear las horas: ' + error.message
                });
            });
        }

        // ============================================
        // DESBLOQUEAR DÍA - CORREGIDO
        // ============================================
        function unblockDay(date) {
            console.log('🔓 Desbloqueando día:', date);
            
            if (!date) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se especificó la fecha para desbloquear'
                });
                return;
            }

            fetch('/doctor/unblock-day', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ date: date })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al desbloquear: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Día desbloqueado',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo desbloquear el día'
                    });
                }
            })
            .catch(function(error) {
                console.error('❌ Error al desbloquear:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al desbloquear el día: ' + error.message
                });
            });
        }

        // ============================================
        // MOSTRAR OPCIONES DE BLOQUEO
        // ============================================
        function showBlockOptions(dateStr) {
            var dateObj = new Date(dateStr + 'T00:00:00');
            var dayName = dateObj.toLocaleDateString('es-ES', { weekday: 'long' });
            var dayNumber = dateObj.getDate();
            var monthName = dateObj.toLocaleDateString('es-ES', { month: 'long' });
            var today = new Date().toISOString().split('T')[0];

            if (dateStr < today) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Día pasado',
                    text: 'No puedes bloquear días que ya pasaron.',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            Swal.fire({
                title: '📅 ' + dayName + ' ' + dayNumber + ' de ' + monthName,
                html: `
                    <div class="text-left">
                        <p>¿Cómo deseas bloquear este día?</p>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-2">
                                <button class="btn btn-danger btn-block" id="blockFullDayBtn" style="padding: 15px; font-size: 16px;">
                                    <i class="fas fa-ban"></i> Día Completo
                                </button>
                                <small class="text-muted">Bloquea todo el día</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button class="btn btn-warning btn-block" id="blockHoursBtn" style="padding: 15px; font-size: 16px;">
                                    <i class="fas fa-clock"></i> Rango de Horas
                                </button>
                                <small class="text-muted">Bloquea un rango específico</small>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label for="blockReasonSwal">Motivo (opcional)</label>
                            <input type="text" class="form-control" id="blockReasonSwal" placeholder="Ej: Vacaciones, Capacitación...">
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                cancelButtonColor: '#6c757d',
                didRender: function() {
                    document.getElementById('blockFullDayBtn').addEventListener('click', function() {
                        var reason = document.getElementById('blockReasonSwal').value || 'Día no disponible';
                        blockFullDay(dateStr, reason);
                        Swal.close();
                    });

                    document.getElementById('blockHoursBtn').addEventListener('click', function() {
                        var reason = document.getElementById('blockReasonSwal').value || '';
                        Swal.close();
                        showHourRangeSelector(dateStr, reason);
                    });
                }
            });
        }

        // ============================================
        // SELECCIONAR RANGO DE HORAS
        // ============================================
        function showHourRangeSelector(dateStr, reason) {
            var hourOptions = '';
            for (var h = 7; h < 21; h++) {
                for (var m = 0; m < 60; m += 30) {
                    var hourStr = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                    hourOptions += '<option value="' + hourStr + '">' + hourStr + '</option>';
                }
            }

            Swal.fire({
                title: '⏰ Seleccionar Rango de Horas',
                html: `
                    <div class="text-left">
                        <p><strong>Fecha:</strong> ${dateStr}</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <select class="form-control" id="startTimeSelect">
                                        ${hourOptions}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <select class="form-control" id="endTimeSelect">
                                        ${hourOptions}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Motivo (opcional)</label>
                            <input type="text" class="form-control" id="blockHourReasonSwal" placeholder="Ej: Descanso, Reunión..." value="${reason}">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Bloquear Horas',
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Cancelar',
                preConfirm: function() {
                    var start = document.getElementById('startTimeSelect').value;
                    var end = document.getElementById('endTimeSelect').value;
                    var reason = document.getElementById('blockHourReasonSwal').value;

                    if (!start || !end) {
                        Swal.showValidationMessage('Selecciona ambas horas');
                        return false;
                    }

                    if (start >= end) {
                        Swal.showValidationMessage('La hora de inicio debe ser menor que la de fin');
                        return false;
                    }

                    return { start: start, end: end, reason: reason };
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    var data = result.value;
                    blockHourRange(dateStr, data.start, data.end, data.reason);
                }
            });

            setTimeout(function() {
                var startSelect = document.getElementById('startTimeSelect');
                var endSelect = document.getElementById('endTimeSelect');
                if (startSelect) startSelect.value = '08:00';
                if (endSelect) endSelect.value = '17:00';
            }, 100);
        }

        // ============================================
        // CONFIGURACIÓN DEL CALENDARIO
        // ============================================

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                day: 'Día',
                list: 'Lista'
            },
            slotMinTime: '07:00:00',
            slotMaxTime: '21:00:00',
            allDaySlot: false,
            slotDuration: '00:30:00',
            height: 'auto',
            nowIndicator: true,
            dayMaxEvents: true,
            events: function(fetchInfo, successCallback, failureCallback) {
                var url = '{{ route("appointments.events") }}';
                var params = new URLSearchParams();

                if (doctorFilter.value) {
                    params.append('doctor_id', doctorFilter.value);
                }

                var fullUrl = url + '?' + params.toString();

                fetch(fullUrl)
                    .then(response => response.json())
                    .then(data => {
                        successCallback(data);
                    })
                    .catch(error => {
                        console.error('❌ Error:', error);
                        failureCallback(error);
                    });
            },
            // ============================================
            // CLICK EN UN DÍA - MODO TACHAR
            // ============================================
            dateClick: function(info) {
                if (blockModeActive && isDoctor) {
                    var dateStr = info.dateStr;
                    var today = new Date().toISOString().split('T')[0];

                    if (dateStr < today) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Día pasado',
                            text: 'No puedes bloquear días que ya pasaron.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        return;
                    }

                    // Verificar si ya está bloqueado
                    fetch('/doctor/check-day-blocked?doctor_id=' + currentDoctorId + '&date=' + dateStr)
                        .then(response => response.json())
                        .then(data => {
                            if (data.blocked) {
                                Swal.fire({
                                    title: '⚠️ Día ya bloqueado',
                                    text: 'Este día ya está marcado como no disponible. ¿Deseas desbloquearlo?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#28a745',
                                    cancelButtonColor: '#dc3545',
                                    confirmButtonText: 'Sí, desbloquear',
                                    cancelButtonText: 'Cancelar'
                                }).then(function(result) {
                                    if (result.isConfirmed) {
                                        unblockDay(dateStr);
                                    }
                                });
                            } else {
                                showBlockOptions(dateStr);
                            }
                        })
                        .catch(function() {
                            showBlockOptions(dateStr);
                        });
                }
            },
            // ============================================
            // CUANDO SE CAMBIA DE VISTA (mes/semana)
            // ============================================
            datesSet: function(info) {
                if (currentDoctorId) {
                    loadBlockedDays(currentDoctorId);
                }
            },
            // ============================================
            // CLICK EN UN EVENTO
            // ============================================
            eventClick: function(info) {
                if (blockModeActive) return;

                var props = info.event.extendedProps;
                var appointmentId = props.appointment_id || info.event.id;
                var startTime = info.event.start;
                var date = startTime ? startTime.toISOString().split('T')[0] : null;
                var time = startTime ? startTime.toISOString().split('T')[1].substring(0, 5) : null;
                var isBlocked = info.event.classNames.includes('blocked-slot');

                if (isBlocked) {
                    var content = `
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-danger">
                                    <i class="fas fa-ban"></i> 
                                    <strong>Hora Bloqueada</strong><br>
                                    <strong>Fecha:</strong> ${date || 'N/A'}<br>
                                    <strong>Hora:</strong> ${time || 'N/A'}<br>
                                    ${props.reason ? `<strong>Motivo:</strong> ${props.reason}` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-success btn-block" id="unblockSlotBtn">
                                    <i class="fas fa-check"></i> Desbloquear hora
                                </button>
                            </div>
                        </div>
                    `;

                    document.getElementById('eventDetails').innerHTML = content;
                    
                    document.getElementById('unblockSlotBtn').addEventListener('click', function() {
                        var slotId = props.slot_id;
                        if (slotId) {
                            fetch('/doctor/unavailable-slots/' + slotId, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Hora desbloqueada!',
                                        text: data.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(function() {
                                        calendar.refetchEvents();
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'No se pudo desbloquear la hora'
                                    });
                                }
                            });
                        }
                    });
                    
                    $('#eventModal').modal('show');
                    return;
                }

                // Mostrar detalles normales
                showAppointmentDetails(appointmentId);
            }
        });

        // ============================================
        // FUNCIÓN PARA MOSTRAR DETALLES DE CITA
        // ============================================
        function showAppointmentDetails(appointmentId) {
            document.getElementById('eventDetails').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando detalles...</p>
                </div>
            `;

            fetch('/appointments/' + appointmentId + '/payment-status')
                .then(response => response.json())
                .then(data => {
                    var props = {};
                    var events = calendar.getEvents();
                    var targetEvent = events.find(e => e.id == appointmentId);
                    if (targetEvent) {
                        props = targetEvent.extendedProps;
                    }

                    var servicesHtml = '';
                    if (props.services && props.services.length > 0) {
                        servicesHtml = `
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Servicio</th>
                                            <th>Médico</th>
                                            <th>Duración</th>
                                            <th>Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${props.services.map((s, index) => `
                                            <tr>
                                                <td>${index + 1}</td>
                                                <td>${s.service || 'N/A'}</td>
                                                <td>${s.doctor || 'N/A'}</td>
                                                <td>${s.duration || 0} min</td>
                                                <td>S/ ${(parseFloat(s.price) || 0).toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active font-weight-bold">
                                            <td colspan="3" class="text-right">TOTAL:</td>
                                            <td>${data.total_duration || props.total_duration || 0} min</td>
                                            <td>S/ ${(parseFloat(data.total_price) || parseFloat(props.total_price) || 0).toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        `;
                    }

                    var content = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Paciente</span>
                                        <span class="info-box-number">${props.patient || 'N/A'}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-${data.status_color || 'secondary'}"><i class="fas fa-tag"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Estado</span>
                                        <span class="info-box-number">${data.status_text || 'N/A'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-${data.payment_status_color || 'secondary'}"><i class="fas fa-money-bill-wave"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Pago</span>
                                        <span class="info-box-number">${data.payment_status_text || 'N/A'}</span>
                                        ${data.payment_method_text ? `<small>${data.payment_method_text}</small>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-money-bill"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total</span>
                                        <span class="info-box-number">S/ ${(parseFloat(data.total_price) || 0).toFixed(2)}</span>
                                        <small>${data.services_count || 0} servicio(s)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${servicesHtml}
                        ${props.notes ? `
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card card-secondary">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-comment"></i> Notas</h5>
                                    </div>
                                    <div class="card-body">
                                        ${props.notes}
                                    </div>
                                </div>
                            </div>
                        </div>` : ''}
                    `;

                    document.getElementById('eventDetails').innerHTML = content;
                    document.getElementById('viewAppointmentBtn').href = '/appointments/' + appointmentId;
                    document.getElementById('editAppointmentBtn').href = '/appointments/' + appointmentId + '/edit';
                    $('#eventModal').modal('show');
                })
                .catch(error => {
                    console.error('❌ Error cargando detalles:', error);
                    document.getElementById('eventDetails').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> 
                            Error al cargar los detalles.
                            <br><small class="text-muted">${error.message}</small>
                        </div>
                    `;
                    $('#eventModal').modal('show');
                });
        }

        // ============================================
        // RENDER Y CONFIGURACIÓN
        // ============================================

        calendar.render();
        console.log('✅ Calendario renderizado');

        // Cargar días bloqueados al inicio
        if (currentDoctorId) {
            loadBlockedDays(currentDoctorId);
        }

        doctorFilter.addEventListener('change', function() {
            var doctorId = this.value;
            calendar.refetchEvents();
            if (doctorId) {
                loadBlockedDays(doctorId);
            } else {
                blockedDays = [];
                applyBlockedDays();
            }
        });

        viewFilter.addEventListener('change', function() {
            calendar.changeView(viewFilter.value);
        });

        document.getElementById('refreshCalendar').addEventListener('click', function() {
            calendar.refetchEvents();
            if (currentDoctorId) {
                loadBlockedDays(currentDoctorId);
            }
            Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                timer: 1000,
                showConfirmButton: false
            });
        });

        document.getElementById('todayBtn').addEventListener('click', function() {
            calendar.today();
        });

        window.addEventListener('resize', function() {
            calendar.updateSize();
        });
    });
</script>
@endpush