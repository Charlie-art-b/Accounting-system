<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_managements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_receivable_id')
                ->constrained('accounts_receivable')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->dateTime('next_reminder_at')->nullable()->index();
            $table->unsignedSmallInteger('reminder_attempts')->default(0);

            $table->string('last_action', 80)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_managements');
    }
};
