<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->decimal('cost_price', 10, 2)->default(0); // Precio costo
            $table->decimal('sale_price', 10, 2); // Precio venta
            $table->decimal('amount_paid', 10, 2); // Monto pagado
            $table->decimal('change_amount', 10, 2)->default(0); // Vuelto
            $table->decimal('doctor_payment', 10, 2)->default(0); // Pago al médico
            $table->enum('payment_method', ['efectivo', 'yape', 'tarjeta_culqi']);
            $table->string('receipt_number')->nullable(); // N° Boleta
            $table->string('invoice_number')->nullable(); // N° Factura
            $table->text('description')->nullable();
            $table->date('payment_date');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Quién registró
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};