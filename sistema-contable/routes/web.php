<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\PDFTestController;
use App\Services\PdfFallbackService;

Route::get('/', function () {
    return redirect('/admin');
});

/*
|--------------------------------------------------------------------------
| Fixed Assets - Backend actions
|--------------------------------------------------------------------------
*/

// Registrar baja de activo fijo
Route::patch(
    '/fixed-assets/{fixedAsset}/dispose',
    [FixedAssetController::class, 'dispose']
);

// routes/web.php
Route::get('/test/balance-general/{cliente}', function (\App\Models\Customer $cliente) {
    $service = app(\App\Services\EstadoFinancieroService::class);
    $service->setCliente($cliente->id);
    $data = $service->balanceGeneral();
    
    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
});

Route::get('/test/estado-resultados/{cliente}', function (\App\Models\Customer $cliente) {
    $service = app(\App\Services\EstadoFinancieroService::class);
    $service->setCliente($cliente->id);
    $data = $service->estadoResultados();
    
    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
});

Route::get('/test/ratios-financieros/{cliente}', function (\App\Models\Customer $cliente) {
    $service = app(\App\Services\EstadoFinancieroService::class);
    $service->setCliente($cliente->id);
    $data = $service->ratiosFinancieros();
    
    return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
});

Route::get('/test/pdf/balance-general/{cliente}', function (\App\Models\Customer $cliente) {
    $service = app(\App\Services\EstadoFinancieroService::class);
    $service->setCliente($cliente->id);
    $data = $service->balanceGeneral();

    return app(PdfFallbackService::class)->stream(
        view: 'exports.balance-general-pdf',
        data: ['data' => $data, 'fecha' => now()],
        baseFileName: 'Balance_General'
    );
});

Route::get('/test/pdf/estado-resultados/{cliente}', function (\App\Models\Customer $cliente) {
    $service = app(\App\Services\EstadoFinancieroService::class);
    $service->setCliente($cliente->id);
    $data = $service->estadoResultados();

    return app(PdfFallbackService::class)->stream(
        view: 'exports.estado-resultados-pdf',
        data: ['data' => $data],
        baseFileName: 'Estado_Resultados'
    );
});

Route::get('/test/balance-general-pdf', [PDFTestController::class, 'balanceGeneralTest'])->name('test.balance-pdf');
