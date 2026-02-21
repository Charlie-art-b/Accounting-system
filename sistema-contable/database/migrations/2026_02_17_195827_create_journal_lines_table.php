<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('journal_entry_id');
            $table->foreign('journal_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->onDelete('cascade');

            $table->unsignedBigInteger('accounting_account_id');
            $table->foreign('accounting_account_id')
                ->references('id')
                ->on('accounting_accounts');

            $table->text('description')->nullable();

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('accounting_account_id');
        });

        // Agregar CHECK constraint solo para MySQL (no compatible con SQLite)
        if (env('DB_CONNECTION') === 'mysql') {
            DB::statement("
                ALTER TABLE journal_lines
                ADD CONSTRAINT check_only_one_side
                CHECK (
                    (debit > 0 AND credit = 0)
                    OR
                    (credit > 0 AND debit = 0)
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};