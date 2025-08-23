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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Nombre del proveedor');
            $table->string('email', 255)->unique()->comment('Email de contacto del proveedor');
            $table->string('phone', 20)->nullable()->comment('Teléfono de contacto del proveedor');
            $table->text('address')->nullable()->comment('Dirección del proveedor');
            $table->boolean('is_active')->default(true)->comment('Estado activo/inactivo del proveedor');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index('name');
            $table->index('is_active');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
