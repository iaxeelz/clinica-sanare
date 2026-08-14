@extends('vendor.adminlte.layouts.app')

@section('title', 'Nuevo Usuario')
@section('page-title', 'Crear Nuevo Usuario')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<style>
    .required-field::after {
        content: ' *';
        color: #E74C3C;
        font-weight: 700;
    }

    .form-control {
        border-radius: 12px !important;
        border: 2px solid #e8ecef !important;
        padding: 12px 16px !important;
        font-size: 15px !important;
    }
    .form-control:focus {
        border-color: #2ECC71 !important;
        box-shadow: 0 0 0 0.2rem rgba(46,204,113,0.15) !important;
    }
    select.form-control {
        appearance: auto !important;
        -webkit-appearance: auto !important;
    }

    /* ============================================
       ESTILOS DEL MODAL
    ============================================ */
    .modal-doctor .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 25px 60px rgba(0,0,0,0.25);
        overflow: hidden;
    }
    .modal-doctor .modal-header {
        background: linear-gradient(135deg, #0F4C81, #1a6ea8);
        padding: 20px 30px;
        border-bottom: 4px solid #2ECC71;
    }
    .modal-doctor .modal-header .modal-title {
        color: white;
        font-weight: 700;
        font-size: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .modal-doctor .modal-header .modal-title i {
        color: #2ECC71;
        background: rgba(255,255,255,0.15);
        padding: 10px 12px;
        border-radius: 12px;
        font-size: 18px;
    }
    .modal-doctor .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }
    .modal-doctor .modal-header .btn-close:hover {
        opacity: 1;
    }
    .modal-doctor .modal-body {
        padding: 30px 30px 15px;
        background: #fafbfc;
    }
    .modal-doctor .modal-footer {
        padding: 15px 30px 25px;
        background: #fafbfc;
        border-top: 1px solid #e9ecef;
    }
    .modal-doctor .modal-footer .btn {
        border-radius: 12px;
        padding: 10px 28px;
        font-weight: 600;
    }
    .modal-doctor .modal-footer .btn-primary {
        background: linear-gradient(135deg, #0F4C81, #1a6ea8);
        border: none;
    }
    .modal-doctor .modal-footer .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(15,76,129,0.3);
    }
    .modal-doctor .modal-footer .btn-secondary:hover {
        background: #e9ecef;
    }
    .modal-doctor label {
        font-weight: 600;
        color: #0F4C81;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .modal-doctor label i {
        color: #2ECC71;
        font-size: 16px;
    }
    .modal-doctor .badge-required {
        background: #E74C3C;
        color: white;
        font-size: 10px;
        padding: 2px 12px;
        border-radius: 20px;
        margin-left: 6px;
        font-weight: 700;
    }
    .modal-doctor .form-hint {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
        display: block;
    }
    .modal-doctor .form-hint i {
        color: #0F4C81;
        margin-right: 4px;
    }
    .modal-doctor .form-control {
        border-radius: 12px !important;
        border: 2px solid #e8ecef !important;
        padding: 12px 16px !important;
        font-size: 14px !important;
    }
    .modal-doctor .form-control:focus {
        border-color: #2ECC71 !important;
        box-shadow: 0 0 0 0.2rem rgba(46,204,113,0.15) !important;
    }

    /* Servicios dentro del modal */
    .modal-doctor .card-services {
        background: white;
        border-radius: 14px;
        border: 1px solid #e9ecef;
        overflow: hidden;
        margin-top: 18px;
    }
    .modal-doctor .card-services-header {
        background: linear-gradient(135deg, #1a6ea8, #0F4C81);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 3px solid #2ECC71;
    }
    .modal-doctor .card-services-header i {
        color: #2ECC71;
        font-size: 18px;
        background: rgba(255,255,255,0.15);
        padding: 8px 10px;
        border-radius: 10px;
    }
    .modal-doctor .card-services-header h6 {
        color: white;
        margin: 0;
        font-weight: 700;
        font-size: 15px;
    }
    .modal-doctor .card-services-header small {
        color: rgba(255,255,255,0.75);
        font-size: 12px;
        display: block;
        margin-top: 1px;
    }
    .modal-doctor .card-services-body {
        padding: 18px 20px 15px;
        background: #fafbfc;
    }
    .modal-doctor .card-services-body .form-check {
        padding: 6px 12px;
        border-radius: 8px;
        background: white;
        border: 1px solid #e9ecef;
        transition: all 0.2s;
        margin-bottom: 4px;
    }
    .modal-doctor .card-services-body .form-check:hover {
        background: #f0f7ff;
        border-color: #0F4C81;
    }
    .modal-doctor .card-services-body .form-check-input:checked {
        background-color: #0F4C81;
        border-color: #0F4C81;
    }
    .modal-doctor .card-services-body .form-check-label {
        font-weight: 500;
        color: #1a202c;
        cursor: pointer;
        font-size: 13px;
    }
    .modal-doctor .card-services-body .form-check-label small {
        font-weight: 400;
        color: #6c757d;
        font-size: 11px;
        display: block;
        margin-top: 1px;
    }
    .btn-select-all {
        background: #e8f4fd;
        border: 1px solid #b8d4f0;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        color: #0F4C81;
        transition: all 0.2s;
        cursor: pointer;
    }
    .btn-select-all:hover {
        background: #d4e8fa;
    }
    .alert-doctor-modal {
        border-radius: 10px;
        border-left: 4px solid #0F4C81;
        padding: 10px 16px;
        font-size: 13px;
        background: #e8f4fd;
        border: 1px solid #b8d4f0;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 5px;
    }
    .alert-doctor-modal i {
        color: #0F4C81;
        font-size: 16px;
    }
    .alert-doctor-modal strong {
        color: #0F4C81;
    }

    /* Campos ocultos */
    #specialty, #license_number {
        display: none !important;
    }
</style>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del Usuario</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST" id="userForm">
                @csrf

                <!-- Datos básicos -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="required-field">Nombre Completo</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="required-field">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="required-field">Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Selección de Rol con SELECT -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="roleSelect" class="required-field">Seleccionar Rol</label>
                            <select name="role" id="roleSelect" class="form-control @error('role') is-invalid @enderror" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-role="{{ strtolower($role->name) }}" 
                                            {{ old('role') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name ?? $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- BOTÓN PARA ABRIR MODAL -->
                <div class="row mt-3" id="doctorModalBtnContainer" style="display: none;">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" id="openDoctorModalBtn" style="border-radius: 12px; padding: 12px 24px; font-weight: 600;">
                            <i class="fas fa-user-md"></i> Configurar Datos Profesionales
                        </button>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Haz clic para completar la información del médico/enfermera
                        </small>
                    </div>
                </div>

                <!-- Campos ocultos para almacenar los datos del modal -->
                <input type="hidden" id="specialty" name="specialty" value="{{ old('specialty') }}">
                <input type="hidden" id="license_number" name="license_number" value="{{ old('license_number') }}">
                
                <!-- CAMPO HIDDEN PARA SERVICIOS SELECCIONADOS (NUEVO) -->
                <input type="hidden" id="selected_services" name="selected_services" value="">

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="fas fa-save"></i> Guardar Usuario
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL FLOTANTE PARA MÉDICO/ENFERMERA -->
    <!-- ============================================ -->
    <div class="modal fade modal-doctor" id="doctorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-md"></i>
                        <div>
                            Datos Profesionales
                            <small style="font-size: 13px; font-weight: 400; display: block; color: rgba(255,255,255,0.8);">
                                Completa la información del médico o enfermera
                            </small>
                        </div>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Especialidad -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modal_specialty">
                                    <i class="fas fa-stethoscope"></i> Especialidad
                                    <span class="badge-required">Obligatorio</span>
                                </label>
                                <input type="text" class="form-control" 
                                       id="modal_specialty" placeholder="Ej: Cardiología, Pediatría, Odontología">
                                <small class="form-hint">
                                    <i class="fas fa-info-circle"></i> Ingresa la especialidad del profesional
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modal_license_number">
                                    <i class="fas fa-id-card"></i> N° Licencia
                                    <span class="badge-required">Obligatorio</span>
                                </label>
                                <input type="text" class="form-control" 
                                       id="modal_license_number" placeholder="Ej: CMP-12345, RNE-67890">
                                <small class="form-hint">
                                    <i class="fas fa-info-circle"></i> Ingresa el número de licencia profesional
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Servicios que ofrece -->
                    <div class="card-services">
                        <div class="card-services-header">
                            <i class="fas fa-list"></i>
                            <div>
                                <h6>Servicios que ofrece</h6>
                                <small>Selecciona los servicios que este médico puede ofrecer</small>
                            </div>
                        </div>
                        <div class="card-services-body">
                            @if(isset($services) && $services->count() > 0)
                                <div class="row">
                                    @foreach($services as $service)
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="services[]" value="{{ $service->id }}"
                                                       id="modal_service_{{ $service->id }}"
                                                       {{ old('services') && in_array($service->id, old('services')) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="modal_service_{{ $service->id }}">
                                                    {{ $service->name }}
                                                    <small>
                                                        S/ {{ number_format($service->price, 2) }}
                                                        @if($service->duration_minutes)
                                                            • {{ $service->duration_minutes }} min
                                                        @endif
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3 d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn-select-all" id="modalSelectAllServices">
                                        <i class="fas fa-check-double"></i> Seleccionar Todos
                                    </button>
                                    <button type="button" class="btn-select-all" id="modalDeselectAllServices">
                                        <i class="fas fa-times"></i> Deseleccionar Todos
                                    </button>
                                </div>

                                <small class="form-hint mt-2">
                                    <i class="fas fa-info-circle"></i> 
                                    Selecciona los servicios que este médico puede ofrecer en la clínica.
                                </small>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No hay servicios registrados. 
                                    <a href="{{ route('services.create') }}" class="alert-link">Crea un servicio primero</a>.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="alert-doctor-modal mt-3">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Importante:</strong> Estos datos son necesarios para que el profesional pueda ser asignado a citas.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="saveDoctorDataBtn">
                        <i class="fas fa-save"></i> Guardar Datos
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔥 JS CARGADO (Bootstrap 4)');

        var roleSelect = document.getElementById('roleSelect');
        var modalBtnContainer = document.getElementById('doctorModalBtnContainer');
        var openModalBtn = document.getElementById('openDoctorModalBtn');
        var saveDoctorBtn = document.getElementById('saveDoctorDataBtn');
        var doctorModalEl = document.getElementById('doctorModal');

        // ============================================
        // FUNCIÓN PARA MOSTRAR/OCULTAR BOTÓN DEL MODAL
        // ============================================
        function toggleDoctorModalBtn(roleName) {
            console.log('🔍 toggleDoctorModalBtn:', roleName);
            if (!modalBtnContainer) return;
            if (roleName === 'medico' || roleName === 'enfermera') {
                modalBtnContainer.style.display = 'block';
            } else {
                modalBtnContainer.style.display = 'none';
                // Limpiar campos
                document.getElementById('modal_specialty').value = '';
                document.getElementById('modal_license_number').value = '';
                document.querySelectorAll('#doctorModal input[name="services[]"]').forEach(function(cb) {
                    cb.checked = false;
                });
            }
        }

        // ============================================
        // AL CARGAR Y AL CAMBIAR SELECT
        // ============================================
        if (roleSelect) {
            var selectedOption = roleSelect.options[roleSelect.selectedIndex];
            var roleName = selectedOption ? selectedOption.getAttribute('data-role') : null;
            if (roleName) toggleDoctorModalBtn(roleName);

            roleSelect.addEventListener('change', function() {
                var selected = this.options[this.selectedIndex];
                var role = selected ? selected.getAttribute('data-role') : null;
                toggleDoctorModalBtn(role);
            });
        }

        // ============================================
        // ABRIR MODAL
        // ============================================
        if (openModalBtn) {
            openModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof $ !== 'undefined') {
                    $(doctorModalEl).modal('show');
                }
            });
        }

        // ============================================
        // SELECCIONAR/DESELECCIONAR SERVICIOS EN MODAL
        // ============================================
        document.getElementById('modalSelectAllServices')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('#doctorModal input[name="services[]"]').forEach(function(cb) {
                cb.checked = true;
            });
        });

        document.getElementById('modalDeselectAllServices')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('#doctorModal input[name="services[]"]').forEach(function(cb) {
                cb.checked = false;
            });
        });

        // ============================================
        // GUARDAR DATOS DEL MODAL (CON JSON)
        // ============================================
        if (saveDoctorBtn) {
            saveDoctorBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('💾 Guardando datos...');
                
                var specialty = document.getElementById('modal_specialty');
                var license = document.getElementById('modal_license_number');
                
                var specialtyVal = specialty ? specialty.value.trim() : '';
                var licenseVal = license ? license.value.trim() : '';
                
                var modalCheckboxes = document.querySelectorAll('#doctorModal input[name="services[]"]:checked');
                var selectedServices = [];
                
                modalCheckboxes.forEach(function(cb) {
                    selectedServices.push(cb.value);
                });

                console.log('📝 Servicios seleccionados en modal:', selectedServices);

                if (!specialtyVal) {
                    alert('⚠️ Debes completar la Especialidad.');
                    if (specialty) specialty.focus();
                    return;
                }
                if (!licenseVal) {
                    alert('⚠️ Debes completar el N° Licencia.');
                    if (license) license.focus();
                    return;
                }
                if (selectedServices.length === 0) {
                    alert('⚠️ Debes seleccionar al menos un servicio.');
                    return;
                }

                // ============================================
                // GUARDAR EN EL FORMULARIO PRINCIPAL
                // ============================================
                // 1. Especialidad y Licencia
                document.getElementById('specialty').value = specialtyVal;
                document.getElementById('license_number').value = licenseVal;

                // 2. GUARDAR SERVICIOS COMO JSON EN EL CAMPO HIDDEN
                document.getElementById('selected_services').value = JSON.stringify(selectedServices);
                
                console.log('✅ Servicios guardados en hidden:', document.getElementById('selected_services').value);

                // 3. CERRAR MODAL
                if (typeof $ !== 'undefined') {
                    $(doctorModalEl).modal('hide');
                }

                // 4. MOSTRAR MENSAJE DE ÉXITO
                Swal.fire({
                    icon: 'success',
                    title: '¡Datos guardados!',
                    text: selectedServices.length + ' servicio(s) seleccionado(s).',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }

        // ============================================
        // SINCRONIZAR AL ABRIR MODAL
        // ============================================
        if (doctorModalEl) {
            doctorModalEl.addEventListener('show.bs.modal', function() {
                console.log('🔄 Sincronizando al abrir modal...');
                
                // Cargar especialidad y licencia
                var mainSpecialty = document.getElementById('specialty');
                var mainLicense = document.getElementById('license_number');
                
                if (mainSpecialty) document.getElementById('modal_specialty').value = mainSpecialty.value || '';
                if (mainLicense) document.getElementById('modal_license_number').value = mainLicense.value || '';

                // Cargar servicios seleccionados desde el campo hidden
                var selectedServicesField = document.getElementById('selected_services');
                var selectedServices = [];
                
                if (selectedServicesField && selectedServicesField.value) {
                    try {
                        selectedServices = JSON.parse(selectedServicesField.value);
                        console.log('📝 Servicios cargados desde hidden:', selectedServices);
                    } catch(e) {
                        selectedServices = [];
                    }
                }

                // Marcar checkboxes en el modal
                document.querySelectorAll('#doctorModal input[name="services[]"]').forEach(function(modalCb) {
                    modalCb.checked = selectedServices.includes(modalCb.value);
                });
            });
        }

        // ============================================
        // VALIDAR AL ENVIAR EL FORMULARIO
        // ============================================
        document.getElementById('userForm').addEventListener('submit', function(e) {
            if (!roleSelect) return true;
            
            var selected = roleSelect.options[roleSelect.selectedIndex];
            var roleName = selected ? selected.getAttribute('data-role') : null;
            
            if (roleName === 'medico' || roleName === 'enfermera') {
                var specialty = document.getElementById('specialty');
                var license = document.getElementById('license_number');
                var selectedServicesField = document.getElementById('selected_services');
                
                // Leer servicios del campo hidden
                var servicesSelected = 0;
                if (selectedServicesField && selectedServicesField.value) {
                    try {
                        var services = JSON.parse(selectedServicesField.value);
                        servicesSelected = services.length;
                        console.log('📝 Servicios en hidden al enviar:', services);
                    } catch(e) {
                        servicesSelected = 0;
                    }
                }

                console.log('🔍 Validando envío:');
                console.log('  Especialidad:', specialty ? specialty.value : 'null');
                console.log('  Licencia:', license ? license.value : 'null');
                console.log('  Servicios seleccionados:', servicesSelected);

                if (!specialty || !specialty.value.trim()) {
                    e.preventDefault();
                    alert('⚠️ Debes completar la Especialidad.\n\nHaz clic en "Configurar Datos Profesionales".');
                    return false;
                }
                if (!license || !license.value.trim()) {
                    e.preventDefault();
                    alert('⚠️ Debes completar el N° Licencia.\n\nHaz clic en "Configurar Datos Profesionales".');
                    return false;
                }
                if (servicesSelected === 0) {
                    e.preventDefault();
                    alert('⚠️ Debes seleccionar al menos un servicio para el médico.\n\nAsegúrate de marcar los servicios en el modal.');
                    return false;
                }
            }
            return true;
        });

        console.log('✅ Eventos configurados correctamente');
    });
</script>

@endsection