<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('doctor_services', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            
            // Configuración del servicio para este médico
            $table->boolean('is_active')->default(true);
            $table->decimal('extra_charge', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['doctor_id', 'service_id']);
            
            // Índices
            $table->index('doctor_id');
            $table->index('service_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_services');
    }
};