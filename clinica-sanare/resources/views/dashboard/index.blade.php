@extends('vendor.adminlte.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Tarjeta de Bienvenida -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline"
                style="border-radius: 16px; border-top: 5px solid #2ECC71; background: linear-gradient(135deg, #0F4C81, #2ECC71); color: white; border: none;">
                <div class="card-body" style="padding: 20px 30px;">
                    <div class="row align-items-center">
                        <div class="col-md-8 col-12">
                            <h2 style="font-weight: 700; color: white; font-size: 28px; margin-bottom: 5px;">
                                <i class="fas fa-heartbeat" style="color: white;"></i>
                                ¡Bienvenido, {{ Auth::user()->name }}!
                            </h2>
                            <p style="color: rgba(255,255,255,0.9); font-size: 16px;">
                                Sistema de Gestión Clínica <strong>SANARE</strong>
                            </p>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;">
                                <span
                                    style="background: rgba(255,255,255,0.2); padding: 6px 14px; border-radius: 30px; font-size: 12px; color: white;">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [del] YYYY') }}
                                </span>
                                <span
                                    style="background: rgba(255,255,255,0.2); padding: 6px 14px; border-radius: 30px; font-size: 12px; color: white;">
                                    <i class="fas fa-user-tag"></i>
                                    {{ Auth::user()->roles->first()->display_name ?? Auth::user()->roles->first()->name ?? 'Usuario' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 text-center mt-3 mt-md-0">
                            <div
                                style="width: 100px; height: 100px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #0F4C81; font-size: 45px; box-shadow: 0 15px 35px rgba(255,255,255,0.2);">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <p style="color: white; margin-top: 8px; font-weight: 600; font-size: 13px;">
                                <span
                                    style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #2ECC71; margin-right: 8px; animation: pulse 1.5s infinite;"></span>
                                Conectado
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ESTADÍSTICAS SEGÚN ROL - RESPONSIVE -->
    <!-- ============================================================ -->

    @if(isset($dashboardData['isDoctor']) && $dashboardData['isDoctor'] === true)
        {{-- DASHBOARD PARA MÉDICO/ENFERMERA --}}
        <div class="row">
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $dashboardData['myAppointmentsToday'] ?? 0 }}</h3>
                        <p>Mis Citas Hoy</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <a href="{{ route('appointments.index', ['date' => now()->format('Y-m-d')]) }}" class="small-box-footer">Ver
                        más <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $dashboardData['myPendingAppointments'] ?? 0 }}</h3>
                        <p>Mis Citas Pendientes</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <a href="{{ route('appointments.index', ['status' => 'pendiente']) }}" class="small-box-footer">Ver más <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dashboardData['myTotalAppointments'] ?? 0 }}</h3>
                        <p>Total de Mis Citas</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <a href="{{ route('appointments.index') }}" class="small-box-footer">Ver más <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Mis Próximas Citas</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(isset($dashboardData['myRecentAppointments']) && $dashboardData['myRecentAppointments']->count() > 0)
                    <!-- Desktop: Tabla -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Servicio</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dashboardData['myRecentAppointments'] as $appointment)
                                    <tr>
                                        <td>{{ $appointment->patient->full_name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->services_list ?: 'N/A' }}</td>
                                        <td>{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $appointment->status_color }}">
                                                {{ $appointment->status_text }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile: Tarjetas -->
                    <div class="d-block d-md-none">
                        @foreach($dashboardData['myRecentAppointments'] as $appointment)
                            <div class="card mb-2">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 font-weight-bold">{{ $appointment->patient->full_name ?? 'N/A' }}</h6>
                                            <p class="text-muted small mb-1"><i class="fas fa-stethoscope mr-1"></i>
                                                {{ $appointment->services_list ?: 'N/A' }}</p>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ $appointment->appointment_date->format('d/m/Y') }}
                                                <i class="fas fa-clock ml-2 mr-1"></i>
                                                {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}
                                            </p>
                                        </div>
                                        <span class="badge badge-{{ $appointment->status_color }}">
                                            {{ $appointment->status_text }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-muted">No tienes citas programadas</p>
                @endif
            </div>
        </div>

    @elseif(isset($dashboardData['isInventory']) && $dashboardData['isInventory'] === true)
        {{-- DASHBOARD PARA INVENTARIO --}}
        <div class="row">
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $dashboardData['totalItems'] ?? 0 }}</h3>
                        <p>Total de Artículos</p>
                    </div>
                    <div class="icon"><i class="fas fa-boxes"></i></div>
                    <a href="{{ route('inventory.index') }}" class="small-box-footer">Ver más <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $dashboardData['lowStockCount'] ?? 0 }}</h3>
                        <p>Artículos con Stock Bajo</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="small-box-footer">Ver más <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dashboardData['totalCategories'] ?? 0 }}</h3>
                        <p>Categorías</p>
                    </div>
                    <div class="icon"><i class="fas fa-tags"></i></div>
                    <a href="{{ route('inventory.index') }}" class="small-box-footer">Ver más <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        @if(isset($dashboardData['lowStockItems']) && $dashboardData['lowStockItems']->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">⚠️ Artículos con Stock Bajo</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Artículo</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Stock Mínimo</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dashboardData['lowStockItems'] as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ ucfirst($item->category) }}</td>
                                        <td><span class="badge badge-danger">{{ $item->quantity }}</span></td>
                                        <td>{{ $item->min_stock }}</td>
                                        <td>
                                            <a href="{{ route('inventory.edit', $item) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Ajustar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    @else
        {{-- DASHBOARD PARA ADMIN Y RECEPCIONISTA --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-12">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $dashboardData['totalPatients'] ?? 0 }}</h3>
                        <p>Pacientes Registrados</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-injured"></i></div>
                    <a href="{{ route('patients.index') }}" class="small-box-footer">Ver más <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dashboardData['appointmentsToday'] ?? 0 }}</h3>
                        <p>Citas Hoy</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <a href="{{ route('appointments.index', ['date' => now()->format('Y-m-d')]) }}" class="small-box-footer">Ver
                        más <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $dashboardData['pendingAppointments'] ?? 0 }}</h3>
                        <p>Citas Pendientes</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <a href="{{ route('appointments.index', ['status' => 'pendiente']) }}" class="small-box-footer">Ver más <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- Balance SOLO para Admin (NO para Recepcionista) --}}
            @if(isset($dashboardData['isAdmin']) && $dashboardData['isAdmin'] === true)
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>S/ {{ number_format($dashboardData['balance'] ?? 0, 2) }}</h3>
                            <p>Balance del Mes</p>
                        </div>
                        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                        <a href="{{ route('incomes.cash-flow') }}" class="small-box-footer">Ver más <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            @else
                {{-- Para Recepcionista: mostrar Médicos Activos --}}
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $dashboardData['totalDoctors'] ?? 0 }}</h3>
                            <p>Médicos Activos</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-md"></i></div>
                        <a href="{{ route('doctors.index') }}" class="small-box-footer">Ver más <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Próximas citas y Stock Bajo --}}
        <div class="row">
            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Próximas Citas</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($dashboardData['recentAppointments']) && $dashboardData['recentAppointments']->count() > 0)
                            <!-- Desktop: Tabla -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Paciente</th>
                                            <th>Médicos</th>
                                            <th>Servicios</th>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dashboardData['recentAppointments'] as $appointment)
                                            <tr>
                                                <td>{{ $appointment->patient->full_name ?? 'N/A' }}</td>
                                                <td>{{ $appointment->doctors_list ?: 'N/A' }}</td>
                                                <td>{{ $appointment->services_list ?: 'N/A' }}</td>
                                                <td>{{ $appointment->appointment_date->format('d/m/Y') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $appointment->status_color }}">
                                                        {{ $appointment->status_text }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile: Tarjetas -->
                            <div class="d-block d-md-none">
                                @foreach($dashboardData['recentAppointments'] as $appointment)
                                    <div class="card mb-2">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 font-weight-bold">{{ $appointment->patient->full_name ?? 'N/A' }}</h6>
                                                    <p class="text-muted small mb-1"><i class="fas fa-user-md mr-1"></i>
                                                        {{ $appointment->doctors_list ?: 'N/A' }}</p>
                                                    <p class="text-muted small mb-1"><i class="fas fa-stethoscope mr-1"></i>
                                                        {{ $appointment->services_list ?: 'N/A' }}</p>
                                                    <p class="text-muted small mb-0">
                                                        <i class="fas fa-calendar mr-1"></i>
                                                        {{ $appointment->appointment_date->format('d/m/Y') }}
                                                        <i class="fas fa-clock ml-2 mr-1"></i>
                                                        {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}
                                                    </p>
                                                </div>
                                                <span class="badge badge-{{ $appointment->status_color }} ml-2">
                                                    {{ $appointment->status_text }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-center text-muted">No hay citas próximas</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Stock Bajo</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($dashboardData['lowStockItems']) && $dashboardData['lowStockItems']->count() > 0)
                            <ul class="list-unstyled">
                                @foreach($dashboardData['lowStockItems'] as $item)
                                    <li class="mb-2 pb-2 border-bottom">
                                        <span class="badge badge-warning">⚠️</span>
                                        <span class="font-weight-bold">{{ $item->name }}</span>
                                        <span class="float-right">
                                            <span class="badge badge-danger">{{ $item->quantity }}</span>
                                            <small class="text-muted">/ {{ $item->min_stock }}</small>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="btn btn-warning btn-sm btn-block">
                                Ver todos los artículos con stock bajo
                            </a>
                        @else
                            <p class="text-center text-muted py-3">✅ No hay artículos con stock bajo</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Anuncios -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bullhorn text-warning"></i> Anuncios y Campañas</h3>
                    @can('manage_users')
                        <div class="card-tools">
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newAnnouncementModal">
                                <i class="fas fa-plus"></i> Nuevo Anuncio
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="row">
                        @forelse($announcements as $announcement)
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                                    @if($announcement->image)
                                        <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}"
                                            style="height: 160px; width: 100%; object-fit: cover;">
                                    @else
                                        <div
                                            style="height: 160px; background: linear-gradient(135deg, #0F4C81, #2ECC71); display: flex; align-items: center; justify-content: center; color: white; font-size: 50px;">
                                            <i class="fas {{ $announcement->type_icon }}"></i>
                                        </div>
                                    @endif
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="badge badge-{{ $announcement->type_color }} mb-2">
                                                <i class="fas {{ $announcement->type_icon }}"></i>
                                                {{ ucfirst($announcement->type) }}
                                            </span>
                                            <small class="text-muted">
                                                <i class="far fa-clock"></i>
                                                {{ $announcement->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <h5 class="card-title">{{ $announcement->title }}</h5>
                                        <p class="card-text text-muted mt-2">{{ Str::limit($announcement->description, 100) }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> {{ $announcement->user->name }}
                                            </small>
                                            @can('manage_users')
                                                <div>
                                                    <button class="btn btn-warning btn-sm" data-toggle="modal"
                                                        data-target="#editAnnouncementModal{{ $announcement->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('¿Eliminar este anuncio?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('announcements.toggle', $announcement) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary btn-sm">
                                                            <i
                                                                class="fas fa-{{ $announcement->is_active ? 'eye-slash' : 'eye' }}"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                                <h4>No hay anuncios disponibles</h4>
                                <p class="text-muted">Los anuncios y campañas se mostrarán aquí.</p>
                                @can('manage_users')
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#newAnnouncementModal">
                                        <i class="fas fa-plus"></i> Crear primer anuncio
                                    </button>
                                @endcan
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            70% {
                transform: scale(1.8);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        .small-box {
            border-radius: 16px !important;
        }

        /* Responsive para móviles */
        @media (max-width: 767.98px) {
            .small-box .inner h3 {
                font-size: 18px;
            }

            .small-box .inner p {
                font-size: 12px;
            }

            .small-box .icon {
                font-size: 28px;
            }

            .card-title {
                font-size: 15px;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-responsive table {
                min-width: 550px;
                font-size: 13px;
            }

            .table-responsive table td,
            .table-responsive table th {
                padding: 6px 8px;
                white-space: nowrap;
            }
        }

        @media (max-width: 480px) {
            .small-box .inner h3 {
                font-size: 16px;
            }

            .small-box {
                padding: 10px !important;
            }

            .card-body {
                padding: 12px 15px !important;
            }

            .table-responsive table {
                min-width: 480px;
                font-size: 12px;
            }

            .btn-sm {
                padding: 3px 6px;
                font-size: 10px;
            }
        }
    </style>
@endsection