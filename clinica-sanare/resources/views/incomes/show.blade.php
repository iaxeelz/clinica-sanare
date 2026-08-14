@extends('vendor.adminlte.layouts.app')

@section('title', 'Detalle del Ingreso')
@section('page-title', 'Detalle del Ingreso #' . $income->id)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('incomes.index') }}">Ingresos</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información del Ingreso</h3>
            <div class="card-tools">
                <a href="{{ route('incomes.edit', $income) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('incomes.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Paciente</span>
                            <span class="info-box-number">{{ $income->patient->full_name ?? 'N/A' }}</span>
                            <small>DNI: {{ $income->patient->dni ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-stethoscope"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Servicio</span>
                            <span class="info-box-number">{{ $income->service->name ?? 'N/A' }}</span>
                            <small>Costo: S/ {{ number_format($income->cost_price, 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-user-md"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Médico</span>
                            <span class="info-box-number">{{ $income->doctor->user->name ?? 'N/A' }}</span>
                            <small>{{ $income->doctor->specialty ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha de Pago</span>
                            <span class="info-box-number">{{ $income->payment_date->format('d/m/Y') }}</span>
                            <small>Registrado por: {{ $income->user->name ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="text-muted">Detalles Financieros</h5>
                    <hr>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-tag"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Precio Costo</span>
                            <span class="info-box-number">S/ {{ number_format($income->cost_price, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Precio Venta</span>
                            <span class="info-box-number">S/ {{ number_format($income->sale_price, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Monto Pagado</span>
                            <span class="info-box-number">S/ {{ number_format($income->amount_paid, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-hand-holding-usd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pago al Médico</span>
                            <span class="info-box-number">S/ {{ number_format($income->doctor_payment, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $income->payment_method_color }}"><i class="fas fa-credit-card"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Método de Pago</span>
                            <span class="info-box-number">{{ $income->payment_method_text }}</span>
                            @if($income->payment_method == 'efectivo' && $income->change_amount > 0)
                                <small>Vuelto: S/ {{ number_format($income->change_amount, 2) }}</small>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-receipt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">N° Boleta</span>
                            <span class="info-box-number">{{ $income->receipt_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">N° Factura</span>
                            <span class="info-box-number">{{ $income->invoice_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($income->description)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h5 class="card-title"><i class="fas fa-comment"></i> Observaciones</h5>
                            </div>
                            <div class="card-body">
                                {{ $income->description }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Estado:</strong> 
                        @if($income->is_active)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-danger">Inactivo</span>
                        @endif
                        <span class="ml-3">
                            <strong>Registrado:</strong> {{ $income->created_at->format('d/m/Y H:i') }}
                        </span>
                        @if($income->updated_at != $income->created_at)
                            <span class="ml-3">
                                <strong>Última actualización:</strong> {{ $income->updated_at->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <form action="{{ route('incomes.destroy', $income) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este ingreso?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                    <a href="{{ route('incomes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection