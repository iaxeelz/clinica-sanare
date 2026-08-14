<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            
            // Datos del servicio en el momento de la cita
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->default(30);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['appointment_id', 'doctor_id']);
            $table->index('doctor_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointment_services');
    }
};