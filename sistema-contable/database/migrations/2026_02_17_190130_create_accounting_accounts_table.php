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

    $table->foreignId('customer_id')
        ->constrained()
        ->cascadeOnDelete()
        ->index();

    $table->string('code');
    $table->string('name');

    $table->enum('type', [
        'Activo',
        'Pasivo',
        'Patrimonio',
        'Ingreso',
        'Gasto'
    ])->index();

    // 🔥 LO ÚNICO QUE FALTABA
    $table->enum('normal_balance', ['debit', 'credit']);

    $table->enum('status', [
        'Activa',
        'Inactiva'
    ])->default('Activa');

    $table->timestamps();

    $table->unique(['customer_id', 'code']);
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
