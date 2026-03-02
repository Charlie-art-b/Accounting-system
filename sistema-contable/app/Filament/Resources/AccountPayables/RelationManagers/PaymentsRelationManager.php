<?php

namespace App\Filament\Resources\AccountPayables\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Historial de Pagos';

    protected static ?string $recordTitleAttribute = 'paid_at';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paid_at')->label('Fecha')->dateTime()->sortable(),
                TextColumn::make('amount')->label('Monto')->money('CRC'),
                TextColumn::make('note')->label('Nota')->wrap(),
                TextColumn::make('created_at')->label('Registrado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('paid_at', 'desc');
    }
}
