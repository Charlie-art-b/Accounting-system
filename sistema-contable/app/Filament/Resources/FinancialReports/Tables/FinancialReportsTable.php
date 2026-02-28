<?php

namespace App\Filament\Resources\FinancialReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinancialReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('report_type')
                    ->label('Tipo reporte')
                    ->searchable(),

                TextColumn::make('rango')
                    ->label('Rango')
                    ->state(fn ($record): string => ($record->fecha_inicio?->format('d/m/Y') ?? '-') . ' - ' . ($record->fecha_fin?->format('d/m/Y') ?? '-')),

                TextColumn::make('generated_at')
                    ->label('Generado')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('tasa_impuestos')
                    ->label('Impuesto')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

