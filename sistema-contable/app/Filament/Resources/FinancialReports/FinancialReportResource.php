<?php

namespace App\Filament\Resources\FinancialReports;

use BackedEnum;
use App\Filament\Resources\FinancialReports\Pages;
use App\Models\FinancialReport;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Panel;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Models\Customer;
use Filament\Schemas\Schema;

use App\Filament\Resources\FinancialReports\Pages\ViewFinancialReport;
use App\Filament\Resources\FinancialReports\Schemas\FinancialReportInfolist;

class FinancialReportResource extends Resource
{
    protected static ?string $model = FinancialReport::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Historial de reportes';
    protected static ?string $modelLabel = 'Historial de reportes';
    protected static ?string $pluralModelLabel = 'Historial de reportes';

    protected static bool $shouldRegisterNavigation = false;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'historial-reportes';
    }

    public static function infolist(Schema $schema): Schema
    {
        return FinancialReportInfolist::make($schema);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Nº de Reporte')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('report_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'balance_general' => 'Balance General',
                        'estado_resultados' => 'Estado de Resultados',
                        'balance_comprobacion' => 'Balance de Comprobación',
                        'flujo_efectivo' => 'Flujo de Efectivo',
                        'cambios_patrimonio' => 'Cambios Patrimonio',
                        'estado_resultados_integral' => 'Estado Resultados Integral',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date()
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date()
                    ->sortable(),

                TextColumn::make('tasa_impuestos')
                    ->label('Impuestos')
                    ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ''), '0'), '.'))
                    ->toggleable(),

                TextColumn::make('generated_at')
                    ->label('Generado')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->preload()
                    ->searchable(),

                SelectFilter::make('report_type')
                    ->label('Tipo de Reporte')
                    ->options([
                        'balance_general' => 'Balance General',
                        'estado_resultados' => 'Estado de Resultados',
                        'balance_comprobacion' => 'Balance de Comprobación',
                        'flujo_efectivo' => 'Flujo de Efectivo',
                        'cambios_patrimonio' => 'Cambios Patrimonio',
                        'estado_resultados_integral' => 'Estado Resultados Integral',
                    ]),


                Filter::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('to')->label('Hasta'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['from']) {
                            $query->whereDate('fecha_inicio', '>=', $data['from']);
                        }
                        if ($data['to']) {
                            $query->whereDate('fecha_inicio', '<=', $data['to']);
                        }
                    }),
            ])

            ->actions([
                ViewAction::make(),

                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (FinancialReport $record) => self::pdfUrl($record))
                    ->openUrlInNewTab()
                    ->disabled(fn (FinancialReport $record) => self::pdfUrl($record) === null),

                Action::make('excel')
                    ->label('Excel')
                    ->icon('heroicon-o-table-cells')
                    ->url(fn (FinancialReport $record) => url("/api/financial-reports/{$record->id}/excel"))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]); //sin acciones masivas
    }


    public static function pdfUrl(FinancialReport $record): ?string
    {
        $customerId = $record->customer_id;

        $qs = http_build_query([
            'fecha_inicio' => optional($record->fecha_inicio)->format('Y-m-d'),
            'fecha_fin' => optional($record->fecha_fin)->format('Y-m-d'),
            'tasa_impuestos' => (float) $record->tasa_impuestos,
        ]);

        return match ($record->report_type) {
            'balance_general' => url("/api/estados-financieros/{$customerId}/balance-general-pdf?{$qs}"),
            'estado_resultados' => url("/api/estados-financieros/{$customerId}/estado-resultados-pdf?{$qs}"),
            'balance_comprobacion' => url("/api/estados-financieros/{$customerId}/balance-comprobacion-pdf?{$qs}"),
            'flujo_efectivo' => url("/api/estados-financieros/{$customerId}/flujo-efectivo-pdf?{$qs}"),
            'cambios_patrimonio' => url("/api/estados-financieros/{$customerId}/cambios-patrimonio-pdf?{$qs}"),
            'estado_resultados_integral' => url("/api/estados-financieros/{$customerId}/estado-resultados-integral-pdf?{$qs}"),
            default => null,
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialReports::route('/'),
            'view' => Pages\ViewFinancialReport::route('/{record}'),
        ];
    }
}