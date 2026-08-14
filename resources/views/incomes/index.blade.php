@extends('vendor.adminlte.layouts.app')

@section('title', 'Ingresos')
@section('page-title', 'Lista de Ingresos')
@section('breadcrumb')
    <li class="breadcrumb-item active">Ingresos</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>S/ {{ number_format($totalToday, 2) }}</h3>
                    <p>Ingresos de Hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ingresos Registrados</h3>
            <div class="card-tools">
                <a href="{{ route('incomes.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Ingreso
                </a>
                <a href="{{ route('incomes.cash-flow') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-chart-line"></i> Flujo de Caja
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

            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <form action="{{ route('incomes.index') }}" method="GET" class="form-inline flex-wrap">
                        <div class="input-group mr-2 mb-2">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar..." value="{{ $search ?? '' }}">
                        </div>
                        <div class="input-group mr-2 mb-2">
                            <input type="date" name="date" class="form-control" 
                                   value="{{ $date ?? date('Y-m-d') }}">
                        </div>
                        <div class="input-group mr-2 mb-2">
                            <select name="payment_method" class="form-control">
                                <option value="">Todos los métodos</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}" {{ ($payment_method ?? '') == $method ? 'selected' : '' }}>
                                        {{ ucfirst($method) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary mb-2" type="submit">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('incomes.index') }}" class="btn btn-secondary mb-2 ml-2">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Paciente</th>
                            <th>Servicio</th>
                            <th>Médico</th>
                            <th>Precio Venta</th>
                            <th>Pagado</th>
                            <th>Método</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomes as $income)
                            <tr>
                                <td>{{ $income->id }}</td>
                                <td>{{ $income->patient->full_name }}</td>
                                <td>{{ $income->service->name }}</td>
                                <td>{{ $income->doctor->full_name }}</td>
                                <td>S/ {{ number_format($income->sale_price, 2) }}</td>
                                <td>S/ {{ number_format($income->amount_paid, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $income->payment_method_color }}">
                                        {{ $income->payment_method_text }}
                                    </span>
                                </td>
                                <td>{{ $income->payment_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('incomes.show', $income) }}" class="btn btn-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('incomes.edit', $income) }}" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('incomes.destroy', $income) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" 
                                                    onclick="return confirm('¿Está seguro de eliminar este ingreso?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No hay ingresos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $incomes->links() }}
            </div>
        </div>
    </div>
@endsection