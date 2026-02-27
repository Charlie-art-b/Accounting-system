<?php

namespace App\Filament\Resources\AccountPayables\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\AccountPayable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentService;

class AccountPayablesTable
{
    public static function configure(Table $table): Table
    {
        $canView   = auth()->user()?->can('account_payables.view') ?? false;
        $canCreate = auth()->user()?->can('account_payables.create') ?? false;
        $canUpdate = auth()->user()?->can('account_payables.update') ?? false;
        $canDelete = auth()->user()?->can('account_payables.delete') ?? false;

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('supplier'))

            ->columns([
                TextColumn::make('supplier.nombre_razon_social')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_number')
                    ->label('N° Documento')
                    ->searchable(),

                TextColumn::make('issue_date')
                    ->label('Fecha de Emisión')
                    ->date()
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Fecha de Vencimiento')
                    ->date()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Monto Total')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Monto Pagado')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('pending_amount')
                    ->label('Saldo Pendiente')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) =>
                        $record->pending_amount > 0 ? 'warning' : 'success'
                    ),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                        'voided' => 'Anulado',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'pending',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'gray' => 'voided',
                    ]),
            ])

            ->filters([
                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'nombre_razon_social')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'partial' => 'Parcial',
                        'paid' => 'Pagado',
                    ]),
            ])

            ->recordActions([
                ViewAction::make()
                    ->visible(fn () => $canView),

                EditAction::make()
                    ->visible(fn ($record) =>
                        $canUpdate && $record->status !== 'paid'
                    ),

                Action::make('pay')
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) =>
                        $canCreate &&
                        $record->status !== 'paid' &&
                        $record->status !== 'voided'
                    )
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->required(),

                        DatePicker::make('paid_at')
                            ->label('Fecha de pago')
                            ->default(now())
                            ->required(),

                        Textarea::make('note')
                            ->label('Nota'),
                    ])
                    ->action(function (AccountPayable $record, array $data) {

                        if (!$record) return;

                        try {
                            $service = new PaymentService();
                            $service->createPayment(
                                $record,
                                (float) $data['amount'],
                                $data['paid_at'],
                                $data['note'] ?? null
                            );

                            Notification::make()
                                ->success()
                                ->title('Pago registrado')
                                ->send();

                        } catch (\Throwable $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])

            ->toolbarActions([
                ...($canView
                    ? CrudImportExportActions::make(
                        modelClass: AccountPayable::class,
                        module: 'account_payables',
                        title: 'Cuentas por Pagar',
                        filePrefix: 'cuentas-por-pagar',
                        fields: [
                            'supplier_id',
                            'document_number',
                            'issue_date',
                            'due_date',
                            'total_amount',
                            'paid_amount',
                            'status',
                        ],
                        uniqueBy: ['document_number', 'supplier_id'],
                        fieldLabels: [
                            'supplier.nombre_razon_social' => 'Proveedor',
                            'document_number' => 'N° Documento',
                            'issue_date' => 'Fecha de Emisión',
                            'due_date' => 'Fecha de Vencimiento',
                            'total_amount' => 'Monto Total',
                            'paid_amount' => 'Monto Pagado',
                            'status' => 'Estado',
                        ],
                        exportFields: [
                            'supplier.nombre_razon_social',
                            'document_number',
                            'issue_date',
                            'due_date',
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
                                if (!in_array($account->status, ['voided', 'paid'])) {

                                    Notification::make()
                                        ->danger()
                                        ->title('No se puede eliminar')
                                        ->body('Solo cuentas Pagadas o Anuladas pueden eliminarse.')
                                        ->send();

                                    $action->halt();
                                    return;
                                }
                            }
                        })
                ]),
            ])

            ->defaultSort('due_date', 'asc')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100]);
    }
}