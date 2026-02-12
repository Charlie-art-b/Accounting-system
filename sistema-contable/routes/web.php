<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FixedAssetController;

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
