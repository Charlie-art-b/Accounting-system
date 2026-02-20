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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            //$table->foreignId('customer_id')->constrained()->cascadeOnDelete()->index();

            $table->string('journal_type')->default('general');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->unsignedBigInteger('fiscal_period_id')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_reversal')->default(false);
            $table->unsignedBigInteger('reversed_entry_id')->nullable();
            $table->foreign('reversed_entry_id')->references('id')->on('journal_entries')->cascadeOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['source_type', 'source_id']);
            $table->index('posted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
