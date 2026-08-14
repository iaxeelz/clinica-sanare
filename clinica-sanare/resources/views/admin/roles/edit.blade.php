@extends('vendor.adminlte.layouts.app')

@section('title', 'Editar Rol')
@section('page-title', 'Editar Rol y Permisos')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editando Rol: {{ $role->display_name ?? $role->name }}</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="display_name">Nombre Mostrado</label>
                            <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                                   id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}">
                            @error('display_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                   id="description" name="description" value="{{ old('description', $role->description) }}">
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Permisos</label>
                            <div class="row">
                                @foreach($permissions->chunk(4) as $chunk)
                                    <div class="col-md-3">
                                        @foreach($chunk as $permission)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="permissions[]" value="{{ $permission->id }}"
                                                       id="perm_{{ $permission->id }}"
                                                       {{ $role->hasPermissionTo($permission) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Actualizar Rol
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection