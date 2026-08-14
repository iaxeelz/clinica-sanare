<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte Financiero</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            margin: 20px;
        }

        h1 {
            color: #1A5276;
            text-align: center;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .summary-box {
            flex: 1;
            padding: 12px 15px;
            border-radius: 5px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid #ddd;
        }

        .summary-box.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .summary-box.danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .summary-box.info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .summary-box .number {
            font-size: 18px;
            font-weight: bold;
        }

        .summary-box .label {
            font-size: 12px;
            color: #555;
        }

        .info-box {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #1A5276;
        }

        .info-box strong {
            color: #1A5276;
        }

        h2 {
            color: #1A5276;
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #1A5276;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            text-align: left;
        }

        th {
            background-color: #1A5276;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            color: white;
            display: inline-block;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #888;
        }

        .text-right {
            text-align: right;
        }

        .page-break {
            page-break-after: always;
        }

        .total-row {
            font-weight: bold;
            background-color: #e9ecef !important;
        }

        .total-row td {
            border-top: 2px solid #1A5276;
        }
    </style>
</head>

<body>
    <h1>🏥 Clínica Sanare</h1>
    <div class="subtitle">Reporte Financiero Detallado</div>

    <div class="info-box">
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    </div>

    <!-- Resumen -->
    <div class="summary">
        <div class="summary-box success">
            <div class="label">Total Ingresos</div>
            <div class="number">S/ {{ number_format($totalIncomes, 2) }}</div>
        </div>
        <div class="summary-box danger">
            <div class="label">Total Egresos</div>
            <div class="number">S/ {{ number_format($totalExpenses, 2) }}</div>
        </div>
        <div class="summary-box info">
            <div class="label">Balance</div>
            <div class="number" style="color: {{ $balance >= 0 ? '#28a745' : '#dc3545' }};">
                S/ {{ number_format($balance, 2) }}
            </div>
        </div>
    </div>

    <!-- Ingresos -->
    <h2>📊 Ingresos</h2>
    <table>
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
            @forelse($incomes as $index => $income)
                @php
                    $margen = $income->sale_price - $income->cost_price;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $income->payment_date->format('d/m/Y') }}</td>
                    <td>{{ $income->patient->full_name }}</td>
                    <td>{{ $income->service->name }}</td>
                    <td>{{ $income->doctor->full_name }}</td>
                    <td class="text-right">S/ {{ number_format($income->cost_price, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($income->sale_price, 2) }}</td>
                    <td class="text-right">
                        <span style="color: {{ $margen >= 0 ? '#28a745' : '#dc3545' }};">
                            S/ {{ number_format($margen, 2) }}
                        </span>
                    </td>
                    <td class="text-right">S/ {{ number_format($income->amount_paid, 2) }}</td>
                    <td>{{ $income->payment_method_text }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">No hay ingresos en el período</td>
                </tr>
            @endforelse
            @if($incomes->count() > 0)
                <tr class="total-row">
                    <td colspan="5" class="text-right">TOTALES</td>
                    <td class="text-right">S/ {{ number_format($incomes->sum('cost_price'), 2) }}</td>
                    <td class="text-right">S/ {{ number_format($incomes->sum('sale_price'), 2) }}</td>
                    <td class="text-right">S/
                        {{ number_format($incomes->sum('sale_price') - $incomes->sum('cost_price'), 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalIncomes, 2) }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Egresos -->
    <h2>📉 Egresos</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Categoría</th>
                <th>Monto</th>
                <th>Comprobante</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $index => $expense)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td>{{ $expense->concept }}</td>
                    <td>
                        <span class="badge badge-{{ $expense->category_color }}">
                            {{ $expense->category_text }}
                        </span>
                    </td>
                    <td class="text-right">S/ {{ number_format($expense->amount, 2) }}</td>
                    <td>{{ $expense->receipt_number ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No hay egresos en el período</td>
                </tr>
            @endforelse
            @if($expenses->count() > 0)
                <tr class="total-row">
                    <td colspan="4" class="text-right">TOTAL EGRESOS</td>
                    <td class="text-right">S/ {{ number_format($totalExpenses, 2) }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Resumen por método de pago -->
    @if($incomes->count() > 0)
        <h2>📊 Resumen por Método de Pago</h2>
        <table style="width: 50%;">
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomesByMethod as $method => $total)
                    <tr>
                        <td>{{ ucfirst($method) }}</td>
                        <td class="text-right">S/ {{ number_format($total, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="text-right">TOTAL</td>
                    <td class="text-right">S/ {{ number_format($totalIncomes, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Resumen por categoría de egreso -->
    @if($expenses->count() > 0)
        <h2>📊 Resumen por Categoría de Egreso</h2>
        <table style="width: 50%;">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByCategory as $category => $total)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $category)) }}</td>
                        <td class="text-right">S/ {{ number_format($total, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="text-right">TOTAL</td>
                    <td class="text-right">S/ {{ number_format($totalExpenses, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="footer">
        Reporte generado el {{ now()->format('d/m/Y H:i:s') }} | Clínica Sanare - Todos los derechos reservados
    </div>
</body>

</html>