<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Http\Requests\StoreFixedAssetRequest;
use App\Http\Requests\UpdateFixedAssetRequest;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $fixedAssets = FixedAsset::query()
            ->when(
                $request->status,
                fn($query) =>
                $query->where('status', $request->status)
            )
            ->when(
                $request->search,
                fn($query) =>
                $query->where('asset_name', 'like', '%' . $request->search . '%')
            )
            ->orderByDesc('acquisition_date')
            ->paginate($perPage);

        return response()->json($fixedAssets);
    }

    public function store(StoreFixedAssetRequest $request)
    {
        $fixedAsset = FixedAsset::create($request->validated());

        return response()->json([
            'message' => 'Activo fijo registrado correctamente.',
            'data' => $fixedAsset,
        ], 201);
    }

    public function update(UpdateFixedAssetRequest $request, FixedAsset $fixedAsset)
    {
        $fixedAsset->update($request->validated());

        return response()->json([
            'message' => 'Activo fijo actualizado correctamente.',
            'data' => $fixedAsset,
        ]);
    }
}
