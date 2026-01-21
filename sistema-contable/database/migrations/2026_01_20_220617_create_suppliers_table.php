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

            // Tipo de proveedor
            $table->enum('supplier_type', ['persona', 'empresa']);

            // Identificación (cédula o número tributario)
            $table->string('identification', 20)->unique();

            // Correo electrónico
            $table->string('email', 255)->unique()->nullable();

            // Teléfono
            $table->string('phone', 20)->nullable();

            // Estado
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
