<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFTestController;
use App\Http\Controllers\EstadosFinancierosController;
Route::get('/', function () {
    return view('welcome');
});

// ✅ Solo para pruebas de PDF
Route::get('/test/balance-general-pdf', [PDFTestController::class, 'balanceGeneralTest'])->name('test.balance-pdf');
 Route::get('/prueba/{customerId}/balance-general', [EstadosFinancierosController::class, 'balanceGeneral']);
