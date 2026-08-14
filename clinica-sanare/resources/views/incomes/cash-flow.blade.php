@extends('vendor.adminlte.layouts.app')

@section('title', 'Flujo de Caja')
@section('page-title', 'Flujo de Caja')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('incomes.index') }}">Ingresos</a></li>
    <li class="breadcrumb-item active">Flujo de Caja</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen Financiero</h3>
        </div>

        <div class="card-body">
            <!-- Filtro de fechas -->
            <form action="{{ route('incomes.cash-flow') }}" method="GET" class="form-inline mb-4">
                <div class="form-group mr-2">
                    <label for="start_date" class="mr-2">Desde:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="{{ $startDate }}">
                </div>
                <div class="form-group mr-2">
                    <label for="end_date" class="mr-2">Hasta:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="{{ $endDate }}">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </form>

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

            <!-- Gráficos -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Ingresos por Método de Pago</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="incomesByMethodChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Egresos por Categoría</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="expensesByCategoryChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Ingresos por Servicio</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($incomesByService as $item)
                                        <tr>
                                            <td>{{ $item->service->name ?? 'Sin servicio' }}</td>
                                            <td>S/ {{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">
                                                No hay datos
                                            </td>
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
                            <h5 class="card-title">Detalle de Egresos</h5>
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
                                    @forelse($expensesByCategory as $item)
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $item->category)) }}</td>
                                            <td>S/ {{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">
                                                No hay datos
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico: Ingresos por método de pago
            const ctx1 = document.getElementById('incomesByMethodChart').getContext('2d');
            const methodData = @json($incomesByMethod);
            const methodLabels = methodData.map(item => {
                const map = { 'efectivo': 'Efectivo', 'yape': 'Yape', 'tarjeta_culqi': 'Tarjeta Culqi' };
                return map[item.payment_method] || item.payment_method;
            });
            const methodTotals = methodData.map(item => item.total);

            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: methodLabels.length ? methodLabels : ['Sin datos'],
                    datasets: [{
                        data: methodLabels.length ? methodTotals : [1],
                        backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Gráfico: Egresos por categoría
            const ctx2 = document.getElementById('expensesByCategoryChart').getContext('2d');
            const expenseData = @json($expensesByCategory);
            const expenseLabels = expenseData.map(item => {
                const map = { 'compra_inventario': 'Compra Inventario', 'servicios': 'Servicios', 'sueldos': 'Sueldos', 'otros': 'Otros' };
                return map[item.category] || item.category;
            });
            const expenseTotals = expenseData.map(item => item.total);

            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: expenseLabels.length ? expenseLabels : ['Sin datos'],
                    datasets: [{
                        data: expenseLabels.length ? expenseTotals : [1],
                        backgroundColor: ['#17a2b8', '#ffc107', '#007bff', '#6c757d'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
@endsection