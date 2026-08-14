<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->integer('day_of_week'); // 1=Lunes, 2=Martes, ..., 7=Domingo
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['doctor_id', 'day_of_week']);
            $table->index(['doctor_id', 'day_of_week', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_schedules');
    }
};