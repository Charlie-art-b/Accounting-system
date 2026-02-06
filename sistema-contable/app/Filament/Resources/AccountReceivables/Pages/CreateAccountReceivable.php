<?php

namespace App\Filament\Resources\AccountReceivables\Pages;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountReceivable extends CreateRecord
{
    protected static string $resource = AccountReceivableResource::class;
}