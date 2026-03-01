<?php

namespace App\Filament\Resources\AccountingAccounts\Tables;

use App\Exports\AccountingAccountsExport;
use App\Exports\AccountingAccountsPDF;
use App\Models\AccountingAccount;
use App\Models\Customer;
use App\Services\CsvExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccountingAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                TextColumn::make('name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('classification')
                    ->label('Clasificacion')
                    ->badge()
                    ->formatStateUsing(fn ($state) => AccountingAccount::CLASSIFICATIONS[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->getStateUsing(fn ($record) => $record->getSaldo())
                    ->money('CRC', true),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $state === 'Activa' ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('normal_balance')
                    ->label('Naturaleza')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'debit' ? 'Deudora' : 'Acreedora')
                    ->color(fn ($state) => $state === 'debit' ? 'info' : 'warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('level')
                    ->label('Nivel')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('parent.name')
                    ->label('Cuenta Padre')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->options(Customer::orderBy('name')->pluck('name', 'id')->toArray()),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'Activo' => 'Activo',
                        'Pasivo' => 'Pasivo',
                        'Patrimonio' => 'Patrimonio',
                        'Ingreso' => 'Ingreso',
                        'Gasto' => 'Gasto',
                    ]),

                SelectFilter::make('classification')
                    ->label('Clasificacion')
                    ->options(AccountingAccount::CLASSIFICATIONS),

                SelectFilter::make('normal_balance')
                    ->label('Naturaleza')
                    ->options([
                        'debit' => 'Deudora',
                        'credit' => 'Acreedora',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Activa' => 'Activa',
                        'Inactiva' => 'Inactiva',
                    ]),

                Filter::make('created_at')
                    ->label('Fecha de creacion')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make()->visible(fn () => Auth::user()?->can('accounting_accounts.view')),
                EditAction::make()->visible(fn () => Auth::user()?->can('accounting_accounts.update')),
                DeleteAction::make()->visible(fn () => Auth::user()?->can('accounting_accounts.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn () => Auth::user()?->can('accounting_accounts.delete')),
                ]),

                Action::make('export_excel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $excelFacade = '\Maatwebsite\Excel\Facades\Excel';

                        if (class_exists($excelFacade)) {
                            return $excelFacade::download(
                                new AccountingAccountsExport(),
                                'Plan_Cuentas_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
                            );
                        }

                        return app(CsvExportService::class)->downloadFromModel(
                            AccountingAccount::class,
                            [
                                'customer_id',
                                'code',
                                'name',
                                'type',
                                'classification',
                                'report_section',
                                'normal_balance',
                                'parent_id',
                                'level',
                                'status',
                            ],
                            'Plan_Cuentas'
                        );
                    }),

                Action::make('export_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('warning')
                    ->action(fn () => app(AccountingAccountsPDF::class)->download()),
            ]);
    }
}
