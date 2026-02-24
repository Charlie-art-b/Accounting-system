<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFTestController;

Route::get('/', function () {
    return view('welcome');
});

// ✅ Solo para pruebas de PDF
Route::get('/test/balance-general-pdf', [PDFTestController::class, 'balanceGeneralTest'])->name('test.balance-pdf');
