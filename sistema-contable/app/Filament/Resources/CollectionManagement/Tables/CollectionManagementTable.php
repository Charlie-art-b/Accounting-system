<?php

namespace App\Filament\Resources\CollectionManagement\Tables;

use App\Models\CollectionManagement;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CollectionManagementTable
{
    public static function configure(Table $table): Table
    {
        $canView = Auth::user()?->can('collection_management.view') ?? false;
        $canUpdate = Auth::user()?->can('collection_management.update') ?? false;

        return $table
            ->columns([
                TextColumn::make('accountReceivable.invoice_number')
                    ->label('Factura')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('accountReceivable.due_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable(),

                TextColumn::make('days_late')
                    ->label('Dias atraso')
                    ->alignCenter()
                    ->state(fn (CollectionManagement $record) => $record->days_late)
                    ->sortable(),

                TextColumn::make('pending_amount')
                    ->label('Pendiente')
                    ->money('CRC')
                    ->state(fn (CollectionManagement $record) => $record->pending_amount)
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->state(fn (CollectionManagement $record) => $record->status)
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'overdue' => 'Vencido',
                        'due_soon' => 'Proximo',
                        default => 'Pendiente',
                    })
                    ->colors([
                        'danger' => 'overdue',
                        'warning' => 'due_soon',
                        'success' => 'pending',
                    ]),

                TextColumn::make('last_action')
                    ->label('Ultima accion')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('due_date_range')
                    ->label('Vencimiento')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->whereHas('accountReceivable', function (Builder $q) use ($data) {
                            return $q
                                ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('due_date', '>=', $date))
                                ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('due_date', '<=', $date));
                        });
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn () => $canView),

                Action::make('pay')
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn () => $canUpdate)
                    ->requiresConfirmation()
                    ->modalHeading('Registrar pago')
                    ->modalDescription('Este pago actualiza el monto pagado de la cuenta por cobrar.')
                    ->form(fn (CollectionManagement $record) => [
                        TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue($record->pending_amount ?? 0)
                            ->required(),
                        DatePicker::make('paid_at')
                            ->label('Fecha de pago')
                            ->default(now())
                            ->maxDate(now())
                            ->required(),
                        Textarea::make('note')
                            ->label('Nota')
                            ->rows(3),
                    ])
                    ->hidden(fn (CollectionManagement $record) => ! $record->accountReceivable)
                    ->action(function (CollectionManagement $record, array $data) {
                        if (! Auth::user()?->can('collection_management.update')) {
                            abort(403);
                        }

                        try {
                            $service = new PaymentService();
                            $ar = $record->accountReceivable;

                            if (! $ar) {
                                throw new \Exception('No existe la cuenta por cobrar asociada.');
                            }

                            $service->createPayment($ar, (float) $data['amount'], $data['paid_at'], $data['note'] ?? null);

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
                    ->visible(fn (CollectionManagement $record) => $canUpdate && $record->accountReceivable && $record->accountReceivable->payments()->where('is_reversal', false)->whereDoesntHave('reversal')->exists())
                    ->requiresConfirmation()
                    ->modalHeading('Revertir pago')
                    ->modalDescription('Selecciona el pago que deseas revertir. Esta acción creará un registro de reverso y actualizará el monto pagado.')
                    ->modalSubmitActionLabel('Revertir')
                    ->form(fn (CollectionManagement $record) => [
                        Select::make('payment_id')
                            ->label('Pago a revertir')
                            ->options(function () use ($record) {
                                if (!$record->accountReceivable) return [];
                                
                                return $record->accountReceivable->payments()
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
                    ->action(function (CollectionManagement $record, array $data) {
                        if (! Auth::user()?->can('collection_management.update')) {
                            abort(403);
                        }

                        try {
                            $payment = Payment::findOrFail($data['payment_id']);
                            
                            // Verificar que el pago pertenece a esta cuenta por cobrar
                            if ($payment->payable_id !== $record->account_receivable_id) {
                                throw new \Exception('El pago seleccionado no pertenece a esta cuenta por cobrar.');
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
            ->defaultSort('next_reminder_at', 'asc');
    }
}
