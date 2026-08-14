<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['schedule_start', 'schedule_end', 'days_available']);
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->time('schedule_start')->nullable();
            $table->time('schedule_end')->nullable();
            $table->json('days_available')->nullable();
        });
    }
};