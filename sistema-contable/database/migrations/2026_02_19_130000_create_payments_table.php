<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Polimórfico: payable_type/payable_id (AccountReceivable, AccountPayable)
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');

            $table->decimal('amount', 20, 2);
            $table->dateTime('paid_at')->nullable();
            $table->text('note')->nullable();

            // Reversos y trazabilidad mínima
            $table->boolean('is_reversal')->default(false);
            $table->unsignedBigInteger('reversed_payment_id')->nullable();

            // Opcionales: user tracking (puede llenarse cuando habilites auth)
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
