<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Http\Requests\StoreFixedAssetRequest;

class FixedAssetController extends Controller
{
    public function store(StoreFixedAssetRequest $request)
    {
        $fixedAsset = FixedAsset::create($request->validated());

        return response()->json([
            'message' => 'Activo fijo registrado correctamente.',
            'data' => $fixedAsset,
        ], 201);
    }
}
