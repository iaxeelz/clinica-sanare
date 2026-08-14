@extends('vendor.adminlte.layouts.app')

@section('title', 'Egresos')
@section('page-title', 'Lista de Egresos')
@section('breadcrumb')
    <li class="breadcrumb-item active">Egresos</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>S/ {{ number_format($totalToday, 2) }}</h3>
                    <p>Egresos de Hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Egresos Registrados</h3>
            <div class="card-tools">
                <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Egreso
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

            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <form action="{{ route('expenses.index') }}" method="GET" class="form-inline flex-wrap">
                        <div class="input-group mr-2 mb-2">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar..." value="{{ $search ?? '' }}">
                        </div>
                        <div class="input-group mr-2 mb-2">
                            <input type="date" name="date" class="form-control" 
                                   value="{{ $date ?? date('Y-m-d') }}">
                        </div>
                        <div class="input-group mr-2 mb-2">
                            <select name="category" class="form-control">
                                <option value="">Todas las categorías</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ ($category ?? '') == $cat ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $cat)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary mb-2" type="submit">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary mb-2 ml-2">
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
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Categoría</th>
                            <th>Fecha</th>
                            <th>N° Comprobante</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->id }}</td>
                                <td>{{ $expense->concept }}</td>
                                <td>S/ {{ number_format($expense->amount, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $expense->category_color }}">
                                        {{ $expense->category_text }}
                                    </span>
                                </td>
                                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td>{{ $expense->receipt_number ?? '-' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('expenses.show', $expense) }}" class="btn btn-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" 
                                                    onclick="return confirm('¿Está seguro de eliminar este egreso?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No hay egresos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
@endsection