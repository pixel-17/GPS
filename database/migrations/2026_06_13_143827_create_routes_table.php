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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('geojson')->nullable();
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->time('estimated_end_time')->nullable();
            $table->enum('status', ['programada', 'en_progreso', 'completada', 'cancelada'])->default('programada');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['scheduled_date', 'status']);
            $table->index('vehicle_id');
            $table->index('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
