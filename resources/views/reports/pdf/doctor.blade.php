<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte por Médico</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
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
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-info { background-color: #17a2b8; }
        .badge-primary { background-color: #007bff; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .text-center { text-align: center; }
        .text-muted { color: #888; }
    </style>
</head>
<body>
    <h1>🏥 Clínica Sanare</h1>
    <div class="subtitle">Reporte de Citas por Médico</div>

    <div class="info-box">
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} &nbsp;|&nbsp;
        <strong>Total de citas:</strong> {{ $appointments->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Servicio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $index => $appointment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</td>
                    <td>{{ $appointment->patient->full_name }}</td>
                    <td>{{ $appointment->doctor->full_name }}</td>
                    <td>{{ $appointment->service->name }}</td>
                    <td>
                        @php
                            $badgeClass = match($appointment->status) {
                                'pendiente' => 'badge-warning',
                                'confirmada' => 'badge-info',
                                'en_curso' => 'badge-primary',
                                'completada' => 'badge-success',
                                'cancelada' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $appointment->status_text }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay citas en el período seleccionado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Reporte generado el {{ now()->format('d/m/Y H:i:s') }} | Clínica Sanare - Todos los derechos reservados
    </div>
</body>
</html>