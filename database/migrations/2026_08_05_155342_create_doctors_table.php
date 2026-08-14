<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('specialty');
            $table->string('license_number')->unique();
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->time('schedule_start')->nullable();
            $table->time('schedule_end')->nullable();
            $table->json('days_available')->nullable(); // ['lunes', 'martes', ...]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};