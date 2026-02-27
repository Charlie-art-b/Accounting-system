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
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('report_type'); // balance_general, estado_resultados y mas 
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->decimal('tasa_impuestos', 6, 4)->default(0);

            $table->json('payload'); // aqui se guarda TODO el array del reporte
            $table->timestamp('generated_at')->useCurrent();

            $table->timestamps();

            $table->index(['customer_id', 'report_type']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
