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
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();

            // Código de la cuenta (Ej: 1.1.01, 4.02)
            $table->string('code')->unique();

            // Nombre de la cuenta (Ej: Caja, Ingresos por servicios)
            $table->string('name');

            // Tipo de cuenta
            $table->enum('type', [
                'Activo',
                'Pasivo',
                'Patrimonio',
                'Ingreso',
                'Gasto'
            ]);

            // Estado
            $table->enum('status', [
                'Activa',
                'Inactiva'
            ])->default('Activa');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_accounts');
    }
};
