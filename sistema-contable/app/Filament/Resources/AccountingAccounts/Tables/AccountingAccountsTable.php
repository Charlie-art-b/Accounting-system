<?php

namespace App\Filament\Resources\AccountingAccounts\Tables;

use App\Exports\AccountingAccountsExport;
use App\Exports\AccountingAccountsPDF;
use App\Models\AccountingAccount;
use App\Models\Customer;
use App\Services\AccountingAccountsImportService;
use App\Services\CsvExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AccountingAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('classification')
                    ->label('Clasificacion')
                    ->badge()
                    ->formatStateUsing(fn ($state) => AccountingAccount::CLASSIFICATIONS[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('normal_balance')
                    ->label('Naturaleza')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'debit' ? 'Deudora' : 'Acreedora')
                    ->color(fn ($state) => $state === 'debit' ? 'info' : 'warning'),

                TextColumn::make('level')
                    ->label('Nivel')
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('Cuenta Padre')
                    ->toggleable(),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->getStateUsing(fn ($record) => $record->getSaldo())
                    ->money('CRC', true),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $state === 'Activa' ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
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
            ])

            ->recordActions([
                ViewAction::make()
                    ->visible(fn () => Auth::user()?->can('accounting_accounts.view')),

                EditAction::make()
                    ->visible(fn () => Auth::user()?->can('accounting_accounts.update')),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('accounting_accounts.delete')),
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