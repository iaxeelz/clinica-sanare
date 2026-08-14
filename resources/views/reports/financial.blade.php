@extends('vendor.adminlte.layouts.app')

@section('title', 'Reporte Financiero')
@section('page-title', 'Reporte Financiero Detallado')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Financiero</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
            <div class="card-tools">
                <div class="btn-group">
                    <a href="{{ route('reports.financial.export.excel', request()->all()) }}"
                        class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
                    <a href="{{ route('reports.financial.export.pdf', request()->all()) }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.financial') }}" method="GET" class="form-inline flex-wrap">
                <div class="form-group mr-2 mb-2">
                    <label for="start_date" class="mr-2">Desde:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="end_date" class="mr-2">Hasta:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Generar
                </button>
                <a href="{{ route('reports.financial') }}" class="btn btn-secondary mb-2 ml-2">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>S/ {{ number_format($totalIncomes, 2) }}</h3>
                    <p>Total Ingresos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>S/ {{ number_format($totalExpenses, 2) }}</h3>
                    <p>Total Egresos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box {{ $balance >= 0 ? 'bg-info' : 'bg-warning' }}">
                <div class="inner">
                    <h3>S/ {{ number_format($balance, 2) }}</h3>
                    <p>Balance</p>
                </div>
                <div class="icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por método de pago y categoría -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Ingresos por Método de Pago</h5>
                    @if($incomesByMethod->count() > 0)
                        <div class="card-tools">
                            <a href="{{ route('reports.financial.export.excel', request()->all()) }}"
                                class="btn btn-success btn-xs">
                                <i class="fas fa-file-excel"></i>
                            </a>
                            <a href="{{ route('reports.financial.export.pdf', request()->all()) }}"
                                class="btn btn-danger btn-xs">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomesByMethod as $method => $total)
                                <tr>
                                    <td>{{ ucfirst($method) }}</td>
                                    <td>S/ {{ number_format($total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Sin datos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Egresos por Categoría</h5>
                    @if($expensesByCategory->count() > 0)
                        <div class="card-tools">
                            <a href="{{ route('reports.financial.export.excel', request()->all()) }}"
                                class="btn btn-success btn-xs">
                                <i class="fas fa-file-excel"></i>
                            </a>
                            <a href="{{ route('reports.financial.export.pdf', request()->all()) }}"
                                class="btn btn-danger btn-xs">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expensesByCategory as $category => $total)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $category)) }}</td>
                                    <td>S/ {{ number_format($total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Sin datos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de ingresos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detalle de Ingresos</h3>
            <div class="card-tools">
                <span class="badge badge-primary">Total: {{ $incomes->count() }}</span>
                @if($incomes->count() > 0)
                    <div class="btn-group ml-2">
                        <a href="{{ route('reports.financial.export.excel', request()->all()) }}"
                            class="btn btn-success btn-xs">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="{{ route('reports.financial.export.pdf', request()->all()) }}" class="btn btn-danger btn-xs">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($incomes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Paciente</th>
                                <th>Servicio</th>
                                <th>Médico</th>
                                <th>Costo (S/)</th>
                                <th>Venta (S/)</th>
                                <th>Margen (S/)</th>
                                <th>Pagado (S/)</th>
                                <th>Método</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($incomes as $index => $income)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $income->payment_date->format('d/m/Y') }}</td>
                                    <td>{{ $income->patient->full_name }}</td>
                                    <td>{{ $income->service->name }}</td>
                                    <td>{{ $income->doctor->full_name }}</td>
                                    <td>S/ {{ number_format($income->cost_price, 2) }}</td>
                                    <td>S/ {{ number_format($income->sale_price, 2) }}</td>
                                    <td>
                                        @php
                                            $margen = $income->sale_price - $income->cost_price;
                                        @endphp
                                        <span class="badge badge-{{ $margen >= 0 ? 'success' : 'danger' }}">
                                            S/ {{ number_format($margen, 2) }}
                                        </span>
                                    </td>
                                    <td>S/ {{ number_format($income->amount_paid, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $income->payment_method_color }}">
                                            {{ $income->payment_method_text }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold bg-light">
                                <td colspan="5" class="text-right">TOTALES</td>
                                <td>S/ {{ number_format($incomes->sum('cost_price'), 2) }}</td>
                                <td>S/ {{ number_format($incomes->sum('sale_price'), 2) }}</td>
                                <td>S/ {{ number_format($incomes->sum('sale_price') - $incomes->sum('cost_price'), 2) }}</td>
                                <td>S/ {{ number_format($totalIncomes, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="text-center text-muted">No hay ingresos en el período seleccionado.</p>
            @endif
        </div>
    </div>

    <!-- Detalle de egresos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detalle de Egresos</h3>
            <div class="card-tools">
                <span class="badge badge-primary">Total: {{ $expenses->count() }}</span>
                @if($expenses->count() > 0)
                    <div class="btn-group ml-2">
                        <a href="{{ route('reports.financial.export.excel', request()->all()) }}"
                            class="btn btn-success btn-xs">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="{{ route('reports.financial.export.pdf', request()->all()) }}" class="btn btn-danger btn-xs">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($expenses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Categoría</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $index => $expense)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                    <td>{{ $expense->concept }}</td>
                                    <td>
                                        <span class="badge badge-{{ $expense->category_color }}">
                                            {{ $expense->category_text }}
                                        </span>
                                    </td>
                                    <td>S/ {{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold bg-light">
                                <td colspan="4" class="text-right">TOTAL EGRESOS</td>
                                <td>S/ {{ number_format($totalExpenses, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="text-center text-muted">No hay egresos en el período seleccionado.</p>
            @endif
        </div>
    </div>
@endsection