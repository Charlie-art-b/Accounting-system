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
            $table->string('tipo_proveedor', 50)->default('persona')->comment('Tipo de proveedor: persona o empresa');
            $table->string('nombre_razon_social', 255)->comment('Nombre o razón social del proveedor');
            $table->string('identificacion', 50)->unique()->comment('Cédula, pasaporte o número de identificación');
            $table->string('correo', 255)->unique()->comment('Correo electrónico del proveedor');
            $table->string('telefono', 20)->nullable()->comment('Número telefónico del proveedor');
            $table->string('estado', 50)->default('activo')->comment('Estado: activo o inactivo');
            $table->timestamps();
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
