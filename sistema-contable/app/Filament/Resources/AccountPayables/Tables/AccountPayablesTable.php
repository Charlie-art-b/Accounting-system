<?php

namespace App\Filament\Resources\AccountPayables\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\AccountPayable;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class AccountPayablesTable
{
    public static function configure(Table $table): Table
    {
        $canView = auth()->user()?->can('account_payables.view') ?? false;
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
                    ->label('Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fechas')
                    ->label('Fechas')
                    ->state(fn (AccountPayable $record): string => ($record->issue_date?->format('d/m/Y') ?? '-') . "\n" . ($record->due_date?->format('d/m/Y') ?? '-'))
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$issue, $due] = array_pad(explode("\n", $state, 2), 2, '-');

                        return new HtmlString(
                            "<div class='leading-tight'><span class='text-xs'>Emision: " . e($issue) . "</span><br><span class='text-xs fi-text-color-400'>Vence: " . e($due) . '</span></div>'
                        );
                    })
                    ->html(),

                TextColumn::make('montos')
                    ->label('Montos')
                    ->state(fn (AccountPayable $record): string => number_format((float) $record->total_amount, 2, ',', '.') . "\n" . number_format((float) $record->pending_amount, 2, ',', '.'))
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$total, $pending] = array_pad(explode("\n", $state, 2), 2, '0,00');

                        return new HtmlString(
                            "<div class='leading-tight'><span class='text-xs'>Total: CRC " . e($total) . "</span><br><span class='text-xs fi-text-color-400'>Pendiente: CRC " . e($pending) . '</span></div>'
                        );
                    })
                    ->html(),

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

                Filter::make('dates')
                    ->label('Fechas')
                    ->form([
                        DatePicker::make('issue_from')->label('Desde emision'),
                        DatePicker::make('issue_until')->label('Hasta emision'),
                        DatePicker::make('due_from')->label('Desde vencimiento'),
                        DatePicker::make('due_until')->label('Hasta vencimiento'),
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
                ViewAction::make()->visible(fn () => $canView),

                EditAction::make()
                    ->visible(fn ($record) => $canUpdate && $record->status !== 'paid'),

                DeleteAction::make()
                    ->visible(fn ($record) => $canDelete && in_array($record->status, ['voided', 'paid'], true))
                    ->before(function (AccountPayable $record, DeleteAction $action) {
                        if (! in_array($record->status, ['voided', 'paid'], true)) {
                            Notification::make()
                                ->danger()
                                ->title('No se puede eliminar')
                                ->body('Solo cuentas pagadas o anuladas pueden eliminarse.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('¡Cuenta eliminada!')
                            ->body('La cuenta por pagar se eliminó correctamente.')
                    ),

                Action::make('pay')
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $canUpdate && $record->status !== 'paid' && $record->status !== 'voided')
                    ->requiresConfirmation()
                    ->modalHeading('Registrar pago')
                    ->modalDescription('Este pago actualiza el monto pagado de la cuenta por pagar.')
                    ->form([
                        TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue(fn (AccountPayable $record) => $record->getPendingAmountAttribute())
                            ->required(),
                        DatePicker::make('paid_at')
                            ->label('Fecha de pago')
                            ->default(now())
                            ->maxDate(now())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required(),
                        Textarea::make('note')
                            ->label('Nota')
                            ->rows(3),
                    ])
                    ->action(function (AccountPayable $record, array $data) {
                        if (! Auth::user()?->can('account_payables.update')) {
                            abort(403);
                        }

                        try {
                            $service = new PaymentService();
                            $service->createPayment($record, (float) $data['amount'], $data['paid_at'], $data['note'] ?? null);

                            Notification::make()
                                ->title('Pago registrado exitosamente')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al registrar el pago')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('reverse_payment')
                    ->label('Revertir pago')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (AccountPayable $record) => $canUpdate && $record->status !== 'paid' && $record->status !== 'voided' && $record->payments()->where('is_reversal', false)->whereDoesntHave('reversal')->exists())
                    ->requiresConfirmation()
                    ->modalHeading('Revertir pago')
                    ->modalDescription('Selecciona el pago que deseas revertir. Esta acción creará un registro de reverso y actualizará el monto pagado.')
                    ->modalSubmitActionLabel('Revertir')
                    ->form(fn (AccountPayable $record) => [
                        Select::make('payment_id')
                            ->label('Pago a revertir')
                            ->options(function () use ($record) {
                                return $record->payments()
                                    ->where('is_reversal', false)
                                    ->whereDoesntHave('reversal')
                                    ->orderBy('paid_at', 'desc')
                                    ->get()
                                    ->mapWithKeys(function ($payment) {
                                        return [
                                            $payment->id => sprintf(
                                                '%s - ₡%s - %s',
                                                $payment->paid_at->format('d/m/Y H:i'),
                                                number_format($payment->amount, 2),
                                                $payment->note ?? 'Sin nota'
                                            ),
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->placeholder('Selecciona un pago'),
                    ])
                    ->action(function (AccountPayable $record, array $data) {
                        if (! Auth::user()?->can('account_payables.update')) {
                            abort(403);
                        }

                        try {
                            $payment = Payment::findOrFail($data['payment_id']);
                            
                            if ($payment->payable_id !== $record->id) {
                                throw new \Exception('El pago seleccionado no pertenece a esta cuenta por pagar.');
                            }

                            $service = new PaymentService();
                            $reversal = $service->reversePayment($payment, Auth::id());

                            Notification::make()
                                ->title('Pago revertido exitosamente')
                                ->body(sprintf(
                                    'Se revirtió el pago de ₡%s del %s',
                                    number_format($payment->amount, 2),
                                    $payment->paid_at->format('d/m/Y')
                                ))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al revertir el pago')
                                ->body($e->getMessage())
                                ->danger()
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
                            'document_number' => 'No Documento',
                            'issue_date' => 'Fecha de Emision',
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
                                if (! in_array($account->status, ['voided', 'paid'])) {
                                    Notification::make()
                                        ->danger()
                                        ->title('No se puede eliminar')
                                        ->body('Solo cuentas pagadas o anuladas pueden eliminarse.')
                                        ->send();

                                    $action->halt();

                                    return;
                                }
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('¡Cuentas eliminadas!')
                                ->body('Las cuentas por pagar se eliminaron correctamente.')
                        ),
                ]),
            ])
            ->defaultSort('due_date', 'asc')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100]);
    }
}
