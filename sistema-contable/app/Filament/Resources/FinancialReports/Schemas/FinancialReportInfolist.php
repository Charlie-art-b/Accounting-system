<?php

namespace App\Filament\Resources\FinancialReports\Schemas;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;

class FinancialReportInfolist
{
    public static function make(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información general')
                    ->schema([
                        TextEntry::make('id')->label('#'),
                        TextEntry::make('customer.name')->label('Cliente'),
                        TextEntry::make('report_type')
                            ->label('Tipo')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'balance_general' => 'Balance General',
                                'estado_resultados' => 'Estado de Resultados',
                                'balance_comprobacion' => 'Balance de Comprobación',
                                'flujo_efectivo' => 'Flujo de Efectivo',
                                'cambios_patrimonio' => 'Cambios Patrimonio',
                                'estado_resultados_integral' => 'Estado Resultados Integral',
                                default => $state,
                            }),
                        TextEntry::make('fecha_inicio')->date(),
                        TextEntry::make('fecha_fin')->date(),
                        TextEntry::make('generated_at')->dateTime(),
                    ]),

                Section::make('Payload (JSON)')
                    ->schema([
                        ViewEntry::make('payload')
                            ->view('filament.financial-reports.payload'),
                    ]),
            ]);
    }
}