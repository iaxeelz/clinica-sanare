@extends('vendor.adminlte.layouts.app')

@section('title', 'Detalle Servicio')
@section('page-title', 'Detalle del Servicio')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('services.index') }}">Servicios</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $service->name }}</h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td>{{ $service->id }}</td>
                        </tr>
                        <tr>
                            <th>Nombre</th>
                            <td>{{ $service->name }}</td>
                        </tr>
                        <tr>
                            <th>Descripción</th>
                            <td>{{ $service->description ?? 'Sin descripción' }}</td>
                        </tr>
                        <tr>
                            <th>Duración</th>
                            <td>{{ $service->duration_minutes }} minutos</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th>Costo</th>
                            <td>S/ {{ number_format($service->cost, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Precio Venta</th>
                            <td>S/ {{ number_format($service->price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Margen</th>
                            <td>S/ {{ number_format($service->price - $service->cost, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td>
                                @if($service->is_active)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <a href="{{ route('services.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('services.edit', $service) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection