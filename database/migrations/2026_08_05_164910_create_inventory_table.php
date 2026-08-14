<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('otros'); // medicamento, insumo, equipo, otros
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(10);
            $table->string('unit')->default('unidad'); // unidad, caja, frasco, ampolla, etc
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('supplier')->nullable();
            $table->string('location')->nullable(); // ubicación en almacén
            $table->string('barcode')->nullable(); // código de barras
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};