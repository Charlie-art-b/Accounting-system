<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FinancialExportController;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Simple login route (non-Livewire fallback)
require __DIR__.'/simple-login.php';

Route::get('/exports/financial/pdf', [FinancialExportController::class, 'pdf'])
    ->name('exports.financial.pdf');

Route::get('/exports/financial/excel', [FinancialExportController::class, 'excel'])
    ->name('exports.financial.excel');

