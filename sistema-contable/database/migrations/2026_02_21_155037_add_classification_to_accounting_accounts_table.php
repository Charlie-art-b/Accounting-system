<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            // Si la columna no existe, la agrega
            if (!Schema::hasColumn('accounting_accounts', 'classification')) {
                $table->enum('classification', [
                    'activo_corriente',
                    'activo_no_corriente',
                    'pasivo_corriente',
                    'pasivo_no_corriente',
                    'patrimonio',
                    'ingreso',
                    'gasto',
                ])->nullable()->index()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropColumn('classification');
        });
    }
};