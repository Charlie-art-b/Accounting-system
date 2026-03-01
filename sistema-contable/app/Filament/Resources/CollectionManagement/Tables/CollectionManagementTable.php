<?php

namespace App\Filament\Resources\CollectionManagement\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\CollectionManagement;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
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
        $canDelete = Auth::user()?->can('collection_management.delete') ?? false;

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

                Action::make('edit_collection')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn () => $canUpdate)
                    ->form([
                        DateTimePicker::make('next_reminder_at')
                            ->label('Proximo recordatorio'),
                        TextInput::make('reminder_attempts')
                            ->label('Intentos')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('last_action')
                            ->label('Ultima accion')
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ])
                    ->fillForm(fn (CollectionManagement $record): array => [
                        'next_reminder_at' => $record->next_reminder_at,
                        'reminder_attempts' => $record->reminder_attempts,
                        'last_action' => $record->last_action,
                        'notes' => $record->notes,
                    ])
                    ->action(function (CollectionManagement $record, array $data): void {
                        $record->update([
                            'next_reminder_at' => $data['next_reminder_at'] ?? null,
                            'reminder_attempts' => $data['reminder_attempts'] ?? 0,
                            'last_action' => $data['last_action'] ?? null,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Gestion actualizada')
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn () => $canDelete),

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
                    fieldLabels: [
                        'accountReceivable.invoice_number' => 'Factura',
                        'customer.name' => 'Cliente',
                        'next_reminder_at' => 'Proximo Recordatorio',
                        'reminder_attempts' => 'Intentos',
                        'last_action' => 'Ultima Accion',
                        'notes' => 'Notas',
                    ],
                    exportFields: [
                        'accountReceivable.invoice_number',
                        'customer.name',
                        'next_reminder_at',
                        'reminder_attempts',
                        'last_action',
                        'notes',
                    ],
                ),
            ])
            ->defaultSort('next_reminder_at', 'asc');
    }
}
