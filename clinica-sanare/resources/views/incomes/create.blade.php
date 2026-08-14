@extends('vendor.adminlte.layouts.app')

@section('title', 'Nuevo Ingreso')
@section('page-title', 'Registrar Nuevo Ingreso')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('incomes.index') }}">Ingresos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del Ingreso</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('incomes.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="patient_id">Paciente *</label>
                            <select class="form-control @error('patient_id') is-invalid @enderror" 
                                    id="patient_id" name="patient_id" required>
                                <option value="">Seleccionar paciente...</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name }} - {{ $patient->dni }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service_id">Servicio *</label>
                            <select class="form-control @error('service_id') is-invalid @enderror" 
                                    id="service_id" name="service_id" required>
                                <option value="">Seleccionar servicio...</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }} - S/ {{ number_format($service->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="doctor_id">Médico *</label>
                            <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                    id="doctor_id" name="doctor_id" required>
                                <option value="">Seleccionar médico...</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->full_name }} - {{ $doctor->specialty }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_date">Fecha de Pago *</label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cost_price">Precio Costo (S/) *</label>
                            <input type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" 
                                   id="cost_price" name="cost_price" value="{{ old('cost_price', 0) }}" required>
                            @error('cost_price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sale_price">Precio Venta (S/) *</label>
                            <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" 
                                   id="sale_price" name="sale_price" value="{{ old('sale_price', 0) }}" required>
                            @error('sale_price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="doctor_payment">Pago al Médico (S/)</label>
                            <input type="number" step="0.01" class="form-control @error('doctor_payment') is-invalid @enderror" 
                                   id="doctor_payment" name="doctor_payment" value="{{ old('doctor_payment', 0) }}">
                            @error('doctor_payment')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_method">Método de Pago *</label>
                            <select class="form-control @error('payment_method') is-invalid @enderror" 
                                    id="payment_method" name="payment_method" required>
                                <option value="">Seleccionar...</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}" {{ old('payment_method') == $method ? 'selected' : '' }}>
                                        {{ ucfirst($method) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="amount_paid">Monto Pagado (S/) *</label>
                            <input type="number" step="0.01" class="form-control @error('amount_paid') is-invalid @enderror" 
                                   id="amount_paid" name="amount_paid" value="{{ old('amount_paid', 0) }}" required>
                            @error('amount_paid')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="change_amount">Vuelto (S/)</label>
                            <input type="number" step="0.01" class="form-control @error('change_amount') is-invalid @enderror" 
                                   id="change_amount" name="change_amount" value="{{ old('change_amount', 0) }}" readonly>
                            @error('change_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="receipt_number">N° Boleta</label>
                            <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                                   id="receipt_number" name="receipt_number" value="{{ old('receipt_number') }}">
                            @error('receipt_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_number">N° Factura</label>
                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                   id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}">
                            @error('invoice_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">Observaciones</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="2">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Ingreso
                        </button>
                        <a href="{{ route('incomes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const salePrice = document.getElementById('sale_price');
            const amountPaid = document.getElementById('amount_paid');
            const changeAmount = document.getElementById('change_amount');
            const paymentMethod = document.getElementById('payment_method');

            function calcularVuelto() {
                if (paymentMethod.value === 'efectivo') {
                    const sale = parseFloat(salePrice.value) || 0;
                    const paid = parseFloat(amountPaid.value) || 0;
                    if (paid > sale) {
                        changeAmount.value = (paid - sale).toFixed(2);
                    } else {
                        changeAmount.value = '0.00';
                    }
                } else {
                    changeAmount.value = '0.00';
                }
            }

            salePrice.addEventListener('input', calcularVuelto);
            amountPaid.addEventListener('input', calcularVuelto);
            paymentMethod.addEventListener('change', calcularVuelto);
        });
    </script>
@endsection