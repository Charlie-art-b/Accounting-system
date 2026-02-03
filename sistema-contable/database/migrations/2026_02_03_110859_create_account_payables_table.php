<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts_payable', function (Blueprint $table) {
            $table->id();

            // Relacion con suppliers
            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->string('document_number', 50)->index();
            $table->date('issue_date')->index();
            $table->enum('payment_terms', ['cash', 'credit'])->default('credit');
            $table->unsignedInteger('payment_period')->nullable();
            $table->date('due_date')->index();
            $table->enum('type', ['invoice', 'receipt', 'debit_note', 'other'])->default('invoice');

            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('payment_date')->nullable();

            $table->enum('status', ['pending', 'partial', 'paid', 'voided'])->default('pending')->index();

            $table->timestamps();

            $table->unique(['supplier_id', 'document_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_payable');
    }
};
