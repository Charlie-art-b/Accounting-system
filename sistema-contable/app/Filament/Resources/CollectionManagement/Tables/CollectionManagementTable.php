<?php

namespace App\Filament\Resources\CollectionManagement\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\CollectionManagement;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Services\PaymentService;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CollectionManagementTable
{
    public static function configure(Table $table): Table
    {
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
                    ->label('Días atraso')
                    ->alignCenter()
                    ->state(fn (CollectionManagement $record) => $record->days_late)
                    ->sortable(),

                TextColumn::make('pending_amount')
                    ->label('Monto pendiente')
                    ->money('CRC')
                    ->state(fn (CollectionManagement $record) => $record->pending_amount)
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->state(fn (CollectionManagement $record) => $record->status)
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'overdue' => 'Plazo Vencido',
                        'due_soon' => 'Próximo a vencer',
                        default => 'Pendiente',
                    })
                    ->colors([
                        'danger' => 'overdue',
                        'warning' => 'due_soon',
                        'success' => 'pending',
                    ]),

                TextColumn::make('last_action')
                    ->label('Última acción')
                    ->searchable()
                    ->placeholder('—'),

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
                        DatePicker::make('from')->label('Desde (vencimiento)'),
                        DatePicker::make('until')->label('Hasta (vencimiento)'),
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
              
                Action::make('pay')
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn () => Auth::user()?->can('collection_management.update'))
                    ->requiresConfirmation()
                    ->modalHeading('Registrar pago')
                    ->modalDescription('Este pago actualizará el monto pagado de la cuenta por cobrar')
                    ->form(fn (CollectionManagement $record) => [
                        TextInput::make('amount')
                            ->label('Monto a pagar')
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
                            ->label('Nota (opcional)')
                            ->rows(3),
                    ])
                    ->hidden(fn (CollectionManagement $record) => !$record->accountReceivable)
                    ->action(function (CollectionManagement $record, array $data) {
                        if (!Auth::user()?->can('collection_management.update')) {
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

               
            ])

            ->toolbarActions([
                ...CrudImportExportActions::make(
                    modelClass: CollectionManagement::class,
                    module: 'collection_management',
                    title: 'Gestion de Cobro',
                    filePrefix: 'gestion-cobro',
                    fields: [
                        'account_receivable_id',
                        'customer_id',
                        'next_reminder_at',
                        'reminder_attempts',
                        'last_action',
                        'notes',
                    ],
                    uniqueBy: ['account_receivable_id'],
                )
     ])

            ->defaultSort('next_reminder_at', 'asc');
    }
}