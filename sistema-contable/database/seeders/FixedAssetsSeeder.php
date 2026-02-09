<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FixedAsset;

class FixedAssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            [
                'asset_name' => 'Office Laptop',
                'description' => 'Accounting department laptop',
                'acquisition_value' => 850000,
                'acquisition_date' => '2024-02-10',
                'useful_life_years' => 5,
                'residual_value' => 50000,
                'accumulated_depreciation' => 150000,
                'status' => 'active',
            ],
            [
                'asset_name' => 'Company Vehicle',
                'description' => 'Vehicle for operations',
                'acquisition_value' => 9500000,
                'acquisition_date' => '2023-06-01',
                'useful_life_years' => 10,
                'residual_value' => 1000000,
                'accumulated_depreciation' => 800000,
                'status' => 'active',
            ],
            [
                'asset_name' => 'Old Printer',
                'description' => 'Obsolete printer',
                'acquisition_value' => 250000,
                'acquisition_date' => '2020-01-15',
                'useful_life_years' => 5,
                'residual_value' => 10000,
                'accumulated_depreciation' => 250000,
                'status' => 'disposed',
                'disposal_date' => '2025-01-10',
                'disposal_reason' => 'Technological obsolescence',
            ],
        ];

        foreach ($assets as $asset) {
            FixedAsset::updateOrCreate(
                ['asset_name' => $asset['asset_name']],
                $asset
            );
        }
    }
}
