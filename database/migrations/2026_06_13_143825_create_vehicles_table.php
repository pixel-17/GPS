<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate')->unique();
            $table->string('model');
            $table->integer('year');
            $table->integer('capacity');
            $table->enum('status', ['activo', 'inactivo', 'mantenimiento'])->default('activo');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
