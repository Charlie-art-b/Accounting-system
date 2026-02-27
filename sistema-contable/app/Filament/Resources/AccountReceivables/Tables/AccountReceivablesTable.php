<?php

namespace App\Filament\Resources\AccountReceivables\Tables;

use App\Filament\Support\CrudImportExportActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use App\Models\AccountReceivable;
use Illuminate\Support\Facades\Auth;

class AccountReceivablesTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        $canView   = $user?->can('account_receivables.view') ?? false;
        $canUpdate = $user?->can('account_receivables.update') ?? false;
        $canDelete = $user?->can('account_receivables.delete') ?? false;

        return $table
            ->columns([

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),

                TextColumn::make('invoice_number')
                    ->label('Número de factura')
                    ->searchable(),

                TextColumn::make('issue_date')
                    ->label('Fecha de emisión')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Fecha de vencimiento')
                    ->date()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Monto total')
                    ->money('CRC')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Monto pagado')
                    ->money('CRC')
                    ->sortable(),

                TextColumn::make('pending_amount')
                    ->label('Monto pendiente')
                    ->money('CRC')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'pending',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ])
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid'    => 'Pagado',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('dates')
                    ->label('Fechas')
                    ->form([
                        DatePicker::make('issue_from')->label('Desde (emisión)'),
                        DatePicker::make('issue_until')->label('Hasta (emisión)'),
                        DatePicker::make('due_from')->label('Desde (vencimiento)'),
                        DatePicker::make('due_until')->label('Hasta (vencimiento)'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['issue_from'] ?? null, fn ($q, $d) => $q->whereDate('issue_date', '>=', $d))
                            ->when($data['issue_until'] ?? null, fn ($q, $d) => $q->whereDate('issue_date', '<=', $d))
                            ->when($data['due_from'] ?? null, fn ($q, $d) => $q->whereDate('due_date', '>=', $d))
                            ->when($data['due_until'] ?? null, fn ($q, $d) => $q->whereDate('due_date', '<=', $d));
                    }),
            ])

            ->recordActions([

                ViewAction::make()
                    ->visible(fn () => $canView),

                EditAction::make()
                    ->visible(fn ($record) =>
                        $canUpdate && $record->status !== 'paid'
                    ),
            ])

            ->toolbarActions([

                ...($canView
                    ? CrudImportExportActions::make(
                        modelClass: AccountReceivable::class,
                        module: 'account_receivables',
                        title: 'Cuentas por Cobrar',
                        filePrefix: 'cuentas-por-cobrar',
                        fields: [
                            'customer_id',
                            'invoice_number',
                            'issue_date',
                            'due_date',
                            'description',
                            'total_amount',
                            'paid_amount',
                            'status',
                        ],
                        uniqueBy: ['invoice_number'],
                        defaults: [
                            'paid_amount' => 0,
                            'status' => 'pending',
                        ],
                        fieldLabels: [
                            'customer.name' => 'Cliente',
                            'invoice_number' => 'Número de Factura',
                            'issue_date' => 'Fecha de Emisión',
                            'due_date' => 'Fecha de Vencimiento',
                            'description' => 'Descripción',
                            'total_amount' => 'Monto Total',
                            'paid_amount' => 'Monto Pagado',
                            'status' => 'Estado',
                        ],
                        exportFields: [
                            'customer.name',
                            'invoice_number',
                            'issue_date',
                            'due_date',
                            'description',
                            'total_amount',
                            'paid_amount',
                            'status',
                        ],
                    )
                    : []),

                BulkActionGroup::make([

                    DeleteBulkAction::make()
                        ->visible(fn () => $canDelete)
                        ->before(function ($records, DeleteBulkAction $action) {

                            foreach ($records as $account) {
                                if (in_array($account->status, ['pending', 'partial'], true)) {

                                    Notification::make()
                                        ->danger()
                                        ->title('NO SE PUEDE ELIMINAR')
                                        ->body('Solo se pueden eliminar cuentas ya Pagadas.')
                                        ->send();

                                    $action->halt();
                                    return;
                                }
                            }
                        }),
                ]),
            ]);
    }
}