<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained()
                ->onDelete('cascade');

            // Código contable (ej: 1.01.01)
            $table->string('code');

            $table->string('name');

            // Tipo principal
            $table->string('type')->index();

            // ✅ NUEVO: Clasificación detallada
            $table->enum('classification', [
                'activo_corriente',
                'activo_no_corriente',
                'pasivo_corriente',
                'pasivo_no_corriente',
                'patrimonio',
                'ingreso',
                'gasto',
            ])->nullable()->index();

            // Para flujo de efectivo
            $table->string('report_section')->nullable()->index();

            $table->string('normal_balance');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('accounting_accounts')
                ->onDelete('set null');
            
            $table->unsignedInteger('level')->default(1);

            $table->string('status')->default('Activa')->index();

            $table->timestamps();

            $table->unique(['customer_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_accounts');
    }
};