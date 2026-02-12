<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class FixedAsset extends Model
{
    protected $table = 'fixed_assets';

    protected $fillable = [
        'asset_name',
        'description',
        'acquisition_value',
        'acquisition_date',
        'useful_life_years',
        'residual_value',
        'accumulated_depreciation',
        'status',
        'disposal_date',
        'disposal_reason',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'disposal_date' => 'date',
        'acquisition_value' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $asset): void {

            /*
            |--------------------------------------------------------------------------
            | Bloquear edición si está dado de baja
            |--------------------------------------------------------------------------
            */
            if (
                $asset->exists &&
                $asset->getOriginal('status') === 'disposed' &&
                $asset->isDirty()
            ) {
                throw ValidationException::withMessages([
                    'status' => 'No se puede editar un activo que ya fue dado de baja.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Normalización de valores
            |--------------------------------------------------------------------------
            */
            $value = max(0, (float) $asset->acquisition_value);
            $residual = max(0, (float) $asset->residual_value);
            $depreciation = max(0, (float) $asset->accumulated_depreciation);

            if ($depreciation > $value) {
                $depreciation = $value;
            }

            $asset->acquisition_value = $value;
            $asset->residual_value = $residual;
            $asset->accumulated_depreciation = $depreciation;

            /*
            |--------------------------------------------------------------------------
            | Validaciones cuando está dado de baja
            |--------------------------------------------------------------------------
            */
            if ($asset->status === 'disposed') {

                if (!$asset->disposal_date) {
                    $asset->disposal_date = now()->toDateString();
                }

                if (!$asset->disposal_reason) {
                    throw ValidationException::withMessages([
                        'disposal_reason' =>
                            'Debe indicar el motivo de baja cuando el activo está dado de baja.',
                    ]);
                }

            } else {
                $asset->disposal_date = null;
                $asset->disposal_reason = null;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor - Valor neto calculado
    |--------------------------------------------------------------------------
    */
    public function getNetValueCalculatedAttribute(): float
    {
        return (float)$this->acquisition_value
            - (float)$this->accumulated_depreciation;
    }
}
