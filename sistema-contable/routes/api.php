<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstadosFinancierosController;

Route::prefix('estados-financieros')->group(function () {
    Route::get('{customerId}/balance-general', [EstadosFinancierosController::class, 'balanceGeneral']);
    Route::get('{customerId}/balance-general-pdf', [EstadosFinancierosController::class, 'balanceGeneralPDF']);
    Route::get('{customerId}/estado-resultados', [EstadosFinancierosController::class, 'estadoResultados']);
    Route::get('{customerId}/balance-comprobacion', [EstadosFinancierosController::class, 'balanceComprobacion']);
    Route::get('{customerId}/flujo-efectivo', [EstadosFinancierosController::class, 'flujoEfectivo']);
    Route::get('{customerId}/ratios-financieros', [EstadosFinancierosController::class, 'ratiosFinancieros']);
    Route::get('{customerId}/cambios-patrimonio', [EstadosFinancierosController::class, 'cambiosPatrimonio']);
    Route::get('{customerId}/completo', [EstadosFinancierosController::class, 'reporteCompleto']);
});