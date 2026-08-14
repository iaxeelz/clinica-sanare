<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Hacer nullable los campos doctor_id y service_id
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
            $table->unsignedBigInteger('service_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Revertir a NOT NULL (esto puede fallar si hay datos)
            $table->unsignedBigInteger('doctor_id')->nullable(false)->change();
            $table->unsignedBigInteger('service_id')->nullable(false)->change();
        });
    }
};