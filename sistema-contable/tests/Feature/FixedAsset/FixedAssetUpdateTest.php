<?php

namespace Tests\Feature\FixedAsset;

use Tests\TestCase;
use App\Models\FixedAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class FixedAssetUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function updating_to_disposed_requires_reason_and_sets_disposal_date(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Proyector',
            'description' => null,
            'acquisition_value' => 800,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 4,
            'residual_value' => 0,
            'accumulated_depreciation' => 200,
            'status' => 'active',
        ]);

        $asset->status = 'disposed';
        $asset->disposal_reason = 'Obsoleto';
        $asset->disposal_date = null;
        $asset->save();

        $asset = $asset->fresh();

        $this->assertEquals('disposed', $asset->status);
        $this->assertNotNull($asset->disposal_date);
        $this->assertEquals('Obsoleto', $asset->disposal_reason);
    }

    #[Test]
    public function updating_from_disposed_back_to_active_is_not_allowed_if_original_was_disposed(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Router',
            'description' => null,
            'acquisition_value' => 150,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 3,
            'residual_value' => 0,
            'accumulated_depreciation' => 50,
            'status' => 'disposed',
            'disposal_reason' => 'Cambio de equipo',
        ]);

        $this->assertEquals('disposed', $asset->fresh()->status);

        // Si el original esta disposed, cualquier edicion debe bloquearse
        $asset->status = 'active';

        $this->expectException(ValidationException::class);
        $asset->save();
    }

    #[Test]
    public function updating_any_field_when_original_status_is_disposed_is_blocked(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Cámara',
            'description' => null,
            'acquisition_value' => 400,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 3,
            'residual_value' => 0,
            'accumulated_depreciation' => 100,
            'status' => 'disposed',
            'disposal_reason' => 'Se vendió',
        ]);

        $asset->description = 'Cambio no permitido';

        $this->expectException(ValidationException::class);
        $asset->save();
    }

    #[Test]
    public function update_active_clears_disposal_fields_even_if_sent(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Tablet',
            'description' => null,
            'acquisition_value' => 300,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 2,
            'residual_value' => 0,
            'accumulated_depreciation' => 0,
            'status' => 'active',
        ]);

        $asset->disposal_date = '2026-02-10';
        $asset->disposal_reason = 'X';
        $asset->save();

        $asset = $asset->fresh();

        $this->assertNull($asset->disposal_date);
        $this->assertNull($asset->disposal_reason);
        $this->assertEquals('active', $asset->status);
    }

    #[Test]
    public function update_caps_depreciation_to_acquisition_value(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'PC de escritorio',
            'description' => null,
            'acquisition_value' => 1000,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 4,
            'residual_value' => 0,
            'accumulated_depreciation' => 200,
            'status' => 'active',
        ]);

        $asset->accumulated_depreciation = 9999;
        $asset->save();

        $asset = $asset->fresh();

        $this->assertEquals(1000.0, (float) $asset->accumulated_depreciation);
        $this->assertEquals(0.0, (float) $asset->net_value);
    }
}
