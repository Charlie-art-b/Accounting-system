<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFTestController;
<<<<<<< HEAD
=======
use App\Http\Controllers\EstadosFinancierosController;
use App\Services\PdfFallbackService;
>>>>>>> c056526a51b8a766519de194d1fd9be857ed5f5c

Route::get('/', function () {
    return view('welcome');
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
    
    $pdf = \PDF::loadView('exports.balance-general-pdf', ['data' => $data, 'fecha' => now()]);
    return $pdf->stream('Balance_General.pdf');
});

Route::get('/test/pdf/estado-resultados/{cliente}', function (\App\Models\Customer $cliente) {
    $service = app(\App\Services\EstadoFinancieroService::class);
    $service->setCliente($cliente->id);
    $data = $service->estadoResultados();
    
    $pdf = \PDF::loadView('exports.estado-resultados-pdf', ['data' => $data]);
    return $pdf->stream('Estado_Resultados.pdf');
});

Route::get('/test/balance-general-pdf', [PDFTestController::class, 'balanceGeneralTest'])->name('test.balance-pdf');
