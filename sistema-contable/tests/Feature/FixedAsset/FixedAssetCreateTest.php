<?php

namespace Tests\Feature\FixedAsset;

use Tests\TestCase;
use App\Models\FixedAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class FixedAssetCreateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_fixed_asset_active_by_default_and_clears_disposal_fields(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Laptop Dell',
            'description' => 'Equipo para desarrollo',
            'acquisition_value' => 1500,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 3,
            'residual_value' => 200,
            'accumulated_depreciation' => 100,
            'status' => 'active',
            'disposal_date' => '2026-02-10',      
            'disposal_reason' => 'X',            
        ]);

        $asset = $asset->fresh();

        $this->assertEquals('active', $asset->status);
        $this->assertNull($asset->disposal_date);
        $this->assertNull($asset->disposal_reason);
    }

    #[Test]
    public function create_fixed_asset_normalizes_negative_values_to_zero(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Impresora',
            'description' => null,
            'acquisition_value' => -100,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 5,
            'residual_value' => -10,
            'accumulated_depreciation' => -50,
            'status' => 'active',
        ]);

        $asset = $asset->fresh();

        $this->assertEquals(0.0, (float) $asset->acquisition_value);
        $this->assertEquals(0.0, (float) $asset->residual_value);
        $this->assertEquals(0.0, (float) $asset->accumulated_depreciation);
    }

    #[Test]
    public function create_fixed_asset_caps_depreciation_to_acquisition_value(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Vehículo',
            'description' => 'Uso empresarial',
            'acquisition_value' => 1000,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 10,
            'residual_value' => 0,
            'accumulated_depreciation' => 9999, // debe quedar en 1000
            'status' => 'active',
        ]);

        $asset = $asset->fresh();

        $this->assertEquals(1000.0, (float) $asset->accumulated_depreciation);
        $this->assertEquals(0.0, (float) $asset->net_value);
    }

    #[Test]
    public function create_fixed_asset_disposed_requires_disposal_reason(): void
    {
        $this->expectException(ValidationException::class);

        FixedAsset::create([
            'asset_name' => 'Monitor',
            'description' => null,
            'acquisition_value' => 500,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 4,
            'residual_value' => 0,
            'accumulated_depreciation' => 100,
            'status' => 'disposed',
            // disposal_reason omitido -> debe fallar
        ]);
    }

    #[Test]
    public function create_fixed_asset_disposed_sets_disposal_date_if_missing(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Silla ergonómica',
            'description' => null,
            'acquisition_value' => 200,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 2,
            'residual_value' => 0,
            'accumulated_depreciation' => 50,
            'status' => 'disposed',
            'disposal_reason' => 'Daño irreparable',
            'disposal_date' => null, // el model debe asignarla
        ]);

        $asset = $asset->fresh();

        $this->assertEquals('disposed', $asset->status);
        $this->assertNotNull($asset->disposal_date);
        $this->assertEquals('Daño irreparable', $asset->disposal_reason);
    }

    #[Test]
    public function net_value_calculated_accessor_is_correct(): void
    {
        $asset = FixedAsset::create([
            'asset_name' => 'Servidor',
            'description' => null,
            'acquisition_value' => 3000,
            'acquisition_date' => '2026-02-01',
            'useful_life_years' => 5,
            'residual_value' => 0,
            'accumulated_depreciation' => 1200,
            'status' => 'active',
        ]);

        $asset = $asset->fresh();

        $this->assertEquals(1800.0, $asset->net_value_calculated);
        $this->assertEquals(1800.0, (float) $asset->net_value); 
    }
}
