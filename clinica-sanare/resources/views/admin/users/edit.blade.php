@extends('vendor.adminlte.layouts.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editando: {{ $user->name }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nombre Completo *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Nueva Contraseña (opcional)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Dejar en blanco para no cambiar">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">Rol *</label>
                            <select class="form-control @error('role') is-invalid @enderror" 
                                    id="role" name="role" required>
                                <option value="">Seleccionar...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role', $userRole->id ?? '') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name ?? $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_active">Estado</label>
                            <select class="form-control @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('is_active', $user->is_active) ? '' : 'selected' }}>Inactivo</option>
                            </select>
                            @error('is_active')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Actualizar Usuario
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection