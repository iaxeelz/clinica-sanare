@extends('vendor.adminlte.layouts.app')

@section('title', 'Dashboard de Reportes')
@section('page-title', 'Dashboard de Reportes')
@section('breadcrumb')
    <li class="breadcrumb-item active">Reportes</li>
@endsection

@section('content')
    <!-- Tarjetas de resumen -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPatients }}</h3>
                    <p>Pacientes Registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-injured"></i>
                </div>
                <a href="{{ route('reports.patient') }}" class="small-box-footer">
                    Ver Reporte <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalDoctors }}</h3>
                    <p>Médicos Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <a href="{{ route('reports.doctor') }}" class="small-box-footer">
                    Ver Reporte <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalServices }}</h3>
                    <p>Servicios Disponibles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <a href="{{ route('reports.service') }}" class="small-box-footer">
                    Ver Reporte <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalAppointments }}</h3>
                    <p>Total de Citas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <a href="{{ route('reports.doctor') }}" class="small-box-footer">
                    Ver Reporte <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Citas por Estado</h3>
                </div>
                <div class="card-body">
                    <canvas id="appointmentsByStatusChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resumen Financiero del Mes</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Ingresos</span>
                                    <span class="info-box-number">S/ {{ number_format($totalIncome, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Egresos</span>
                                    <span class="info-box-number">S/ {{ number_format($totalExpense, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-{{ $balance >= 0 ? 'info' : 'warning' }}">
                                <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Balance</span>
                                    <span class="info-box-number">S/ {{ number_format($balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acceso rápido a reportes -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reportes Disponibles</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('reports.doctor') }}" class="btn btn-block btn-primary">
                                <i class="fas fa-user-md"></i> Reporte por Médico
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('reports.service') }}" class="btn btn-block btn-success">
                                <i class="fas fa-stethoscope"></i> Reporte por Servicio
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('reports.patient') }}" class="btn btn-block btn-warning">
                                <i class="fas fa-user-injured"></i> Reporte de Pacientes
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('reports.financial') }}" class="btn btn-block btn-info">
                                <i class="fas fa-money-bill-wave"></i> Reporte Financiero
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('appointmentsByStatusChart').getContext('2d');
            const data = @json($appointmentsByStatus);
            
            const statusMap = {
                'pendiente': '#ffc107',
                'confirmada': '#17a2b8',
                'en_curso': '#007bff',
                'completada': '#28a745',
                'cancelada': '#dc3545'
            };
            
            const labels = data.map(item => {
                const map = {
                    'pendiente': 'Pendiente',
                    'confirmada': 'Confirmada',
                    'en_curso': 'En Curso',
                    'completada': 'Completada',
                    'cancelada': 'Cancelada'
                };
                return map[item.status] || item.status;
            });
            
            const values = data.map(item => item.total);
            const colors = data.map(item => statusMap[item.status] || '#6c757d');
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels.length ? labels : ['Sin datos'],
                    datasets: [{
                        data: labels.length ? values : [1],
                        backgroundColor: labels.length ? colors : ['#6c757d'],
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