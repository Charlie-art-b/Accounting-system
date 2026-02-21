<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
            $table->index('customer_id');

            // Tipo de asiento
            $table->string('journal_type')->default('general')->index();

            // Categoría contable
            $table->string('entry_category')->default('Operacion')->index();

            $table->text('description')->nullable();
            $table->string('reference')->nullable();

            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            $table->unsignedBigInteger('fiscal_period_id')->nullable();

            $table->timestamp('posted_at')->nullable()->index();

            $table->unsignedBigInteger('posted_by')->nullable();
            $table->foreign('posted_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->comment('Usuario que registró el asiento');

            $table->boolean('is_reversal')->default(false);

            $table->unsignedBigInteger('reversed_entry_id')->nullable();
            $table->foreign('reversed_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->onDelete('cascade')
                ->comment('Referencia al asiento reversado');

            // Integración con otros módulos
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};