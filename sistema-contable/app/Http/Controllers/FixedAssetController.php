<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFixedAssetRequest;
use App\Http\Requests\UpdateFixedAssetRequest;

class FixedAssetController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Consulta paginada + filtros
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $fixedAssets = FixedAsset::query()
            ->when(
                $request->status,
                fn ($query) =>
                $query->where('status', $request->status)
            )
            ->when(
                $request->search,
                fn ($query) =>
                $query->where('asset_name', 'like', '%' . $request->search . '%')
            )
            ->orderByDesc('acquisition_date')
            ->paginate($perPage);

        return response()->json($fixedAssets);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear activo fijo
    |--------------------------------------------------------------------------
    */
    public function store(StoreFixedAssetRequest $request)
    {
        $fixedAsset = FixedAsset::create($request->validated());

        return response()->json([
            'message' => 'Activo fijo registrado correctamente.',
            'data' => $fixedAsset,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar activo fijo
    |--------------------------------------------------------------------------
    */
    public function update(UpdateFixedAssetRequest $request, FixedAsset $fixedAsset)
    {
        $fixedAsset->update($request->validated());

        return response()->json([
            'message' => 'Activo fijo actualizado correctamente.',
            'data' => $fixedAsset,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dar de baja activo fijo
    |--------------------------------------------------------------------------
    */
    public function dispose(Request $request, FixedAsset $fixedAsset)
    {
        $request->validate([
            'disposal_date' => ['nullable', 'date'],
            'disposal_reason' => ['required', 'string', 'max:255'],
        ]);

        $fixedAsset->dispose($request->only([
            'disposal_date',
            'disposal_reason'
        ]));

        return response()->json([
            'message' => 'Activo dado de baja correctamente.',
            'data' => $fixedAsset->fresh(),
        ]);
    }
}
