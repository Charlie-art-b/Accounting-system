<?php

namespace Tests\Feature\FixedAsset;

use Tests\TestCase;
use App\Models\FixedAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class FixedAssetDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function allow_delete_active_asset(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Teclado',
            'description' => null,
            'acquisition_value' => 50,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 2,
            'residual_value' => 0,
            'accumulated_depreciation' => 0,
            'status' => 'active',
        ]);

        $asset->delete();

        $this->assertDatabaseMissing('fixed_assets', [
            'id' => $asset->id,
        ]);
    }

    #[Test]
    public function allow_delete_disposed_asset(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Mouse',
            'description' => null,
            'acquisition_value' => 20,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 1,
            'residual_value' => 0,
            'accumulated_depreciation' => 10,
            'status' => 'disposed',
            'disposal_reason' => 'Dañado',
        ]);

        $asset->delete();

        $this->assertDatabaseMissing('fixed_assets', [
            'id' => $asset->id,
        ]);
    }
}
