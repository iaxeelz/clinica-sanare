@extends('vendor.adminlte.layouts.app')

@section('title', 'Editar Médico')
@section('page-title', 'Editar Médico')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('doctors.index') }}">Médicos</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editando Médico: {{ $doctor->full_name }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('doctors.update', $doctor) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nombre Completo *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $doctor->user->name ?? '') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $doctor->user->email ?? '') }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Nueva Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Dejar en blanco para no cambiar">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Mínimo 8 caracteres. Dejar en blanco para mantener la actual.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $doctor->user->phone ?? '') }}">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="specialty">Especialidad *</label>
                            <input type="text" class="form-control @error('specialty') is-invalid @enderror" 
                                   id="specialty" name="specialty" value="{{ old('specialty', $doctor->specialty) }}" required
                                   placeholder="Ej: Oftalmólogo, Traumatólogo, Médico General...">
                            @error('specialty')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                La especialidad es solo informativa. El médico puede ofrecer cualquier servicio.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="license_number">Número de Licencia *</label>
                            <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                   id="license_number" name="license_number" value="{{ old('license_number', $doctor->license_number) }}" required>
                            @error('license_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- SERVICIOS QUE OFRECE -->
                <!-- ============================================ -->
                <div class="card card-outline card-primary mt-3">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-list"></i> Servicios que ofrece este médico
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($services->count() > 0)
                            <div class="row">
                                @foreach($services as $service)
                                    <div class="col-md-3 col-sm-4 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="services[]" value="{{ $service->id }}"
                                                   id="service_{{ $service->id }}"
                                                   {{ $doctor->services->contains($service->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="service_{{ $service->id }}">
                                                {{ $service->name }}
                                                <small class="text-muted d-block">
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

                            <!-- Botones de selección rápida -->
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllServices">
                                    <i class="fas fa-check-double"></i> Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllServices">
                                    <i class="fas fa-times"></i> Deseleccionar Todos
                                </button>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No hay servicios registrados. 
                                <a href="{{ route('services.create') }}" class="alert-link">Crea un servicio primero</a>.
                            </div>
                        @endif

                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Selecciona los servicios que este médico puede ofrecer en la clínica.
                        </small>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Actualizar Médico
                        </button>
                        <a href="{{ route('doctors.index') }}" class="btn btn-secondary">
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
        // Seleccionar todos los servicios
        $('#selectAllServices').on('click', function() {
            $('input[name="services[]"]').prop('checked', true);
        });

        // Deseleccionar todos los servicios
        $('#deselectAllServices').on('click', function() {
            $('input[name="services[]"]').prop('checked', false);
        });
    });
</script>
@endpush