<?php

namespace Tests\Feature\FixedAsset;

use Tests\TestCase;
use App\Models\FixedAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class FixedAssetListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function list_all_fixed_assets(): void
    {
        FixedAsset::create([
            'asset_name' => 'Activo 1',
            'description' => null,
            'acquisition_value' => 100,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 2,
            'residual_value' => 0,
            'accumulated_depreciation' => 0,
            'status' => 'active',
        ]);

        FixedAsset::create([
            'asset_name' => 'Activo 2',
            'description' => null,
            'acquisition_value' => 200,
            'acquisition_date' => '2026-02-02',
            'useful_life_years' => 3,
            'residual_value' => 0,
            'accumulated_depreciation' => 50,
            'status' => 'active',
        ]);

        $this->assertDatabaseCount('fixed_assets', 2);
    }

    #[Test]
    public function can_filter_fixed_assets_by_status(): void
    {
        FixedAsset::create([
            'asset_name' => 'Activo Pendiente Baja',
            'description' => null,
            'acquisition_value' => 300,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 3,
            'residual_value' => 0,
            'accumulated_depreciation' => 100,
            'status' => 'active',
        ]);

        FixedAsset::create([
            'asset_name' => 'Activo Dado de Baja',
            'description' => null,
            'acquisition_value' => 400,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 4,
            'residual_value' => 0,
            'accumulated_depreciation' => 200,
            'status' => 'disposed',
            'disposal_reason' => 'Venta',
        ]);

        $disposed = FixedAsset::where('status', 'disposed')->get();

        $this->assertCount(1, $disposed);
        $this->assertEquals('Activo Dado de Baja', $disposed->first()->asset_name);
    }

    #[Test]
    public function net_value_stored_column_matches_calculation(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Activo neto',
            'description' => null,
            'acquisition_value' => 1000,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 5,
            'residual_value' => 0,
            'accumulated_depreciation' => 250,
            'status' => 'active',
        ])->fresh();

        $this->assertEquals(750.0, (float) $asset->net_value);
        $this->assertEquals(750.0, $asset->net_value_calculated);
    }
}
