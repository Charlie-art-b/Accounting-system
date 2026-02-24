<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstadosFinancierosController;

Route::prefix('estados-financieros')->group(function () {
    Route::get(
    '{customerId}/estado-resultados-integral-pdf',
    [EstadosFinancierosController::class, 'statementOfComprehensiveIncomePDF']
    );  
    Route::get('{customerId}/balance-general', [EstadosFinancierosController::class, 'balanceGeneral']);
    Route::get('{customerId}/balance-general-pdf', [EstadosFinancierosController::class, 'balanceGeneralPDF']);
    Route::get('{customerId}/estado-resultados', [EstadosFinancierosController::class, 'estadoResultados']);
    Route::get('{customerId}/estado-resultados-pdf', [EstadosFinancierosController::class, 'estadoResultadosPDF']);
    Route::get('{customerId}/balance-comprobacion', [EstadosFinancierosController::class, 'balanceComprobacion']);
    Route::get('{customerId}/balance-comprobacion-pdf', [EstadosFinancierosController::class, 'balanceComprobacionPDF']);
    Route::get('{customerId}/flujo-efectivo', [EstadosFinancierosController::class, 'flujoEfectivo']);
    Route::get('{customerId}/flujo-efectivo-pdf', [EstadosFinancierosController::class, 'flujoEfectivoPDF']);
    Route::get('{customerId}/cambios-patrimonio', [EstadosFinancierosController::class, 'cambiosPatrimonio']);
    Route::get('{customerId}/cambios-patrimonio-pdf', [EstadosFinancierosController::class, 'cambiosPatrimonioPDF']);
    Route::get('{customerId}/completo', [EstadosFinancierosController::class, 'reporteCompleto']);
    Route::get('{customerId}/completo-pdf', [EstadosFinancierosController::class, 'reporteCompletoPDF']);
    
});