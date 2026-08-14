<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('doctor_blocked_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['doctor_id', 'date']);
            $table->index(['doctor_id', 'date', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_blocked_days');
    }
};