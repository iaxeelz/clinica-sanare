@extends('vendor.adminlte.layouts.app')

@section('title', 'Detalle Artículo')
@section('page-title', 'Detalle del Artículo')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $inventory->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('inventory.edit', $inventory) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#adjustStockModal">
                    <i class="fas fa-exchange-alt"></i> Ajustar Stock
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Información del artículo -->
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">ID</th>
                            <td>{{ $inventory->id }}</td>
                        </tr>
                        <tr>
                            <th>Nombre</th>
                            <td>{{ $inventory->name }}</td>
                        </tr>
                        <tr>
                            <th>Categoría</th>
                            <td><span class="badge badge-info">{{ ucfirst($inventory->category) }}</span></td>
                        </tr>
                        <tr>
                            <th>Descripción</th>
                            <td>{{ $inventory->description ?? 'Sin descripción' }}</td>
                        </tr>
                        <tr>
                            <th>Unidad</th>
                            <td>{{ ucfirst($inventory->unit) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Cantidad</th>
                            <td>
                                <strong>{{ $inventory->quantity }}</strong>
                                @if($inventory->is_low_stock)
                                    <span class="badge badge-warning ml-2">
                                        <i class="fas fa-exclamation-triangle"></i> Stock bajo
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Stock Mínimo</th>
                            <td>{{ $inventory->min_stock }}</td>
                        </tr>
                        <tr>
                            <th>Precio Compra</th>
                            <td>S/ {{ number_format($inventory->purchase_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Precio Venta</th>
                            <td>{{ $inventory->sale_price ? 'S/ '.number_format($inventory->sale_price, 2) : 'No definido' }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td>
                                @if($inventory->is_active)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Información Adicional</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Proveedor:</strong> {{ $inventory->supplier ?? 'No registrado' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Ubicación:</strong> {{ $inventory->location ?? 'No registrada' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Código de Barras:</strong> {{ $inventory->barcode ?? 'No registrado' }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Fecha de Vencimiento:</strong> 
                                        {{ $inventory->expiration_date ? $inventory->expiration_date->format('d/m/Y') : 'No definida' }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Registrado:</strong> {{ $inventory->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Última actualización:</strong> {{ $inventory->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movimientos -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Movimientos de Stock</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Descripción</th>
                                            <th>Usuario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($movements as $movement)
                                            <tr>
                                                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $movement->type_color }}">
                                                        {{ $movement->type_text }}
                                                    </span>
                                                </td>
                                                <td>{{ $movement->quantity }}</td>
                                                <td>S/ {{ number_format($movement->price ?? 0, 2) }}</td>
                                                <td>{{ $movement->description ?? 'Sin descripción' }}</td>
                                                <td>{{ $movement->user->name }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    <i class="fas fa-info-circle"></i> No hay movimientos registrados
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $movements->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajustar Stock -->
    <div class="modal fade" id="adjustStockModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajustar Stock</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('inventory.adjust-stock', $inventory) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="type">Tipo de Movimiento *</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Cantidad *</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Stock actual: <strong>{{ $inventory->quantity }}</strong> {{ $inventory->unit }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection