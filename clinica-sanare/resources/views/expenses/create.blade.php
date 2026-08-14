@extends('vendor.adminlte.layouts.app')

@section('title', 'Nuevo Egreso')
@section('page-title', 'Registrar Nuevo Egreso')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Egresos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Datos del Egreso</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="concept">Concepto *</label>
                            <input type="text" class="form-control @error('concept') is-invalid @enderror" 
                                   id="concept" name="concept" value="{{ old('concept') }}" required>
                            @error('concept')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="amount">Monto (S/) *</label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" 
                                   id="amount" name="amount" value="{{ old('amount', 0) }}" required>
                            @error('amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="category">Categoría *</label>
                            <select class="form-control @error('category') is-invalid @enderror" 
                                    id="category" name="category" required>
                                <option value="">Seleccionar...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $cat)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expense_date">Fecha *</label>
                            <input type="date" class="form-control @error('expense_date') is-invalid @enderror" 
                                   id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                            @error('expense_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="receipt_number">N° Comprobante</label>
                            <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                                   id="receipt_number" name="receipt_number" value="{{ old('receipt_number') }}">
                            @error('receipt_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">Descripción</label>
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
                            <i class="fas fa-save"></i> Guardar Egreso
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection