<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();

            // Relacion con customers
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->string('invoice_number', 50)->index();
            $table->date('issue_date')->index();
            $table->date('due_date')->index();
            $table->string('description', 255);


            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);

            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending')->index();

            $table->timestamps();

            $table->unique(['customer_id', 'invoice_number']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_receivable');
    }
};
