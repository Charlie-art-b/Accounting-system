<?php

namespace App\Filament\Resources\FinancialReports\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;

class FinancialReportInfolist
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información general')
                    ->components([
                        TextEntry::make('id')->label('Nº de Reporte'),
                        TextEntry::make('customer.name')->label('Cliente'),
                        TextEntry::make('report_type')
                            ->label('Tipo de Reporte')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'balance_general' => 'Balance General',
                                'estado_resultados' => 'Estado de Resultados',
                                'balance_comprobacion' => 'Balance de Comprobación',
                                'flujo_efectivo' => 'Flujo de Efectivo',
                                'cambios_patrimonio' => 'Cambios Patrimonio',
                                'estado_resultados_integral' => 'Estado Resultados Integral',
                                default => $state,
                            }),
                        TextEntry::make('fecha_inicio')
                            ->label('Desde')
                            ->date(),
                        TextEntry::make('fecha_fin')
                            ->label('Hasta')
                            ->date(),
                        TextEntry::make('generated_at')
                            ->dateTime()
                            ->label('Generado'),
                    ]),

                Section::make('Detalle del Reporte')
                    ->components([
                        ViewEntry::make('payload')
                            ->view('filament.financial-reports.details'),
                    ]),
            ]);
    }
}