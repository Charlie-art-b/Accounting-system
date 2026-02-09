<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();

            $table->string('asset_name', 150);
            $table->text('description')->nullable();

            $table->decimal('acquisition_value', 15, 2);
            $table->date('acquisition_date')->index();
            $table->unsignedInteger('useful_life_years');

            $table->decimal('residual_value', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);

            $table->decimal('net_value', 15, 2)
                ->storedAs('acquisition_value - accumulated_depreciation');

            $table->enum('status', ['active', 'disposed'])
                ->default('active')
                ->index();

            $table->date('disposal_date')->nullable();
            $table->string('disposal_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
