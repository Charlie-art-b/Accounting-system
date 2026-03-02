<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'profile_photo_path',
        'logo_path',
        'favicon_path',
    ];

    public static function current(): self
    {
        if (! Schema::hasTable('app_settings')) {
            return new self([
                'company_name' => 'CAHEN Servicios Contables',
            ]);
        }

        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'CAHEN Servicios Contables',
            ],
        );
    }

    public static function companyName(): string
    {
        return static::current()->company_name ?: 'CAHEN Servicios Contables';
    }

    public static function logoUrl(): string
    {
        $setting = static::current();

        if (filled($setting->logo_path)) {
            return Storage::disk('public')->url($setting->logo_path);
        }

        return asset('images/logo.png');
    }

    public static function faviconUrl(): string
    {
        $setting = static::current();

        if (filled($setting->favicon_path)) {
            return Storage::disk('public')->url($setting->favicon_path);
        }

        if (filled($setting->logo_path)) {
            return Storage::disk('public')->url($setting->logo_path);
        }

        return asset('images/logo.png');
    }

    public static function profilePhotoUrl(): ?string
    {
        $setting = static::current();

        if (! filled($setting->profile_photo_path)) {
            return null;
        }

        return Storage::disk('public')->url($setting->profile_photo_path);
    }
}
