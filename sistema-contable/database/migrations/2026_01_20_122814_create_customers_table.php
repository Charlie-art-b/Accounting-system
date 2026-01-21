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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->string('first_last_name', 100);
            $table->string('second_last_name', 100)->nullable();
            $table->enum('id_type', ['identification', 'dimex', 'passport']);
            $table->string('identification', 20)->unique();
            $table->string('email', 255)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('address', 355)->nullable();
            $table->enum('customer_type', ['individual', 'legal_person'])->default('individual');
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
