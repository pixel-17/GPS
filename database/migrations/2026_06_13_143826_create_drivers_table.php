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
        Schema::create('drivers', function (Blueprint $table) {
           $table->id();
    // Enlace directo al usuario. Si se borra el usuario, se borra su perfil de chofer.
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('license_number')->unique(); // Exclusivo de choferes
    $table->enum('status', ['activo', 'inactivo', 'suspendido'])->default('activo'); // Exclusivo de choferes
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
