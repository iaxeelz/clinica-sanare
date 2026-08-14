@extends('vendor.adminlte.layouts.app')

@section('title', 'Pacientes')
@section('page-title', 'Lista de Pacientes')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pacientes</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pacientes Registrados</h3>
            <div class="card-tools">
                <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Paciente
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Buscador -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="{{ route('patients.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar por nombre, DNI o teléfono..." 
                                   value="{{ $search ?? '' }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                @if(isset($search) && $search)
                                    <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                            <tr>
                                <td>{{ $patient->id }}</td>
                                <td>{{ $patient->first_name }}</td>
                                <td>{{ $patient->last_name }}</td>
                                <td>{{ $patient->dni }}</td>
                                <td>{{ $patient->phone ?? '-' }}</td>
                                <td>{{ $patient->email ?? '-' }}</td>
                                <td>
                                    @if($patient->is_active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('patients.show', $patient) }}" class="btn btn-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('patients.destroy', $patient) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" 
                                                    onclick="return confirm('¿Está seguro de eliminar este paciente?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No hay pacientes registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $patients->links() }}
            </div>
        </div>
    </div>
@endsection