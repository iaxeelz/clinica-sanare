@extends('vendor.adminlte.layouts.app')

@section('title', 'Inventario')
@section('page-title', 'Lista de Inventario')
@section('breadcrumb')
    <li class="breadcrumb-item active">Inventario</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Artículos Registrados</h3>
            <div class="card-tools">
                @if($lowStockCount > 0)
                    <span class="badge badge-warning mr-2">
                        <i class="fas fa-exclamation-triangle"></i> {{ $lowStockCount }} con stock bajo
                    </span>
                @endif
                <a href="{{ route('inventory.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nuevo Artículo
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
                    <form action="{{ route('inventory.index') }}" method="GET" class="form-inline flex-wrap">
                        <div class="input-group mr-2 mb-2">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar..." value="{{ $search ?? '' }}">
                        </div>
                        <div class="input-group mr-2 mb-2">
                            <select name="category" class="form-control">
                                <option value="">Todas las categorías</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ ($category ?? '') == $cat ? 'selected' : '' }}>
                                        {{ ucfirst($cat) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group mr-2 mb-2">
                            <select name="low_stock" class="form-control">
                                <option value="">Todos</option>
                                <option value="1" {{ ($lowStock ?? '') == '1' ? 'selected' : '' }}>Stock bajo</option>
                            </select>
                        </div>
                        <button class="btn btn-primary mb-2" type="submit">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-secondary mb-2 ml-2">
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
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Stock Mínimo</th>
                            <th>Precio Compra</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td><span class="badge badge-info">{{ ucfirst($item->category) }}</span></td>
                                <td>
                                    {{ $item->quantity }}
                                    @if($item->is_low_stock)
                                        <span class="badge badge-warning" title="Stock bajo">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $item->min_stock }}</td>
                                <td>S/ {{ number_format($item->purchase_price, 2) }}</td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('inventory.show', $item) }}" class="btn btn-info btn-sm" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('inventory.edit', $item) }}" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('inventory.destroy', $item) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" 
                                                    onclick="return confirm('¿Está seguro de eliminar este artículo?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No hay artículos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $inventory->links() }}
            </div>
        </div>
    </div>
@endsection