<?php

namespace App\Filament\Resources\FinancialReports;

use App\Filament\Resources\FinancialReports\Pages;
use App\Filament\Resources\FinancialReports\Schemas\FinancialReportInfolist;
use App\Models\FinancialReport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
            ->defaultSort('generated_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50, 100])
            ->columns([
                TextColumn::make('id')
                    ->label('N. de reporte')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                TextColumn::make('report_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'balance_general' => 'Balance General',
                        'estado_resultados' => 'Estado de Resultados',
                        'balance_comprobacion' => 'Balance de Comprobacion',
                        'flujo_efectivo' => 'Flujo de Efectivo',
                        'cambios_patrimonio' => 'Cambios Patrimonio',
                        'estado_resultados_integral' => 'Estado Resultados Integral',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('periodo')
                    ->label('Periodo')
                    ->state(
                        fn (FinancialReport $record): string => ($record->fecha_inicio?->format('d/m/Y') ?? '-') . "\n" . ($record->fecha_fin?->format('d/m/Y') ?? '-')
                    )
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$from, $to] = array_pad(explode("\n", $state, 2), 2, '-');

                        return new HtmlString(
                            "<div class='leading-tight'><span class='text-xs'>Desde: " . e($from) . "</span><br><span class='text-xs fi-text-color-400'>Hasta: " . e($to) . '</span></div>'
                        );
                    })
                    ->html(),

                TextColumn::make('generated_at')
                    ->label('Generado')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('tasa_impuestos')
                    ->label('Impuestos')
                    ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ''), '0'), '.'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'balance_comprobacion' => 'Balance de Comprobacion',
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

                /*Action::make('edit')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('report_type')
                            ->label('Tipo')
                            ->options([
                                'balance_general' => 'Balance General',
                                'estado_resultados' => 'Estado de Resultados',
                                'balance_comprobacion' => 'Balance de Comprobacion',
                                'flujo_efectivo' => 'Flujo de Efectivo',
                                'cambios_patrimonio' => 'Cambios Patrimonio',
                                'estado_resultados_integral' => 'Estado Resultados Integral',
                            ])
                            ->required(),
                        DatePicker::make('fecha_inicio')
                            ->label('Desde')
                            ->required(),
                        DatePicker::make('fecha_fin')
                            ->label('Hasta')
                            ->required(),
                        TextInput::make('tasa_impuestos')
                            ->label('Impuestos')
                            ->numeric()
                            ->required(),
                    ])
                    ->fillForm(fn (FinancialReport $record): array => [
                        'customer_id' => $record->customer_id,
                        'report_type' => $record->report_type,
                        'fecha_inicio' => $record->fecha_inicio,
                        'fecha_fin' => $record->fecha_fin,
                        'tasa_impuestos' => $record->tasa_impuestos,
                    ])
                    ->action(function (FinancialReport $record, array $data): void {
                        $record->update([
                            'customer_id' => $data['customer_id'],
                            'report_type' => $data['report_type'],
                            'fecha_inicio' => $data['fecha_inicio'],
                            'fecha_fin' => $data['fecha_fin'],
                            'tasa_impuestos' => $data['tasa_impuestos'],
                        ]);
                    }),*/

                DeleteAction::make(),

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
            ->bulkActions([]);
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
