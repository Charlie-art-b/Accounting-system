<?php

namespace App\Filament\Resources\AccountReceivables\Tables;

use App\Filament\Support\CrudImportExportActions;
use App\Models\AccountReceivable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class AccountReceivablesTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        $canView = $user?->can('account_receivables.view') ?? false;
        $canUpdate = $user?->can('account_receivables.update') ?? false;
        $canDelete = $user?->can('account_receivables.delete') ?? false;

        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('invoice_number')
                    ->label('Factura')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fechas')
                    ->label('Fechas')
                    ->state(fn (AccountReceivable $record): string => ($record->issue_date?->format('d/m/Y') ?? '-') . "\n" . ($record->due_date?->format('d/m/Y') ?? '-'))
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$issue, $due] = array_pad(explode("\n", $state, 2), 2, '-');

                        return new HtmlString(
                            "<div class='leading-tight'><span class='text-xs'>Emision: " . e($issue) . "</span><br><span class='text-xs fi-text-color-400'>Vence: " . e($due) . '</span></div>'
                        );
                    })
                    ->html(),

                TextColumn::make('montos')
                    ->label('Montos')
                    ->state(fn (AccountReceivable $record): string => number_format((float) $record->total_amount, 2, ',', '.') . "\n" . number_format((float) $record->pending_amount, 2, ',', '.'))
                    ->formatStateUsing(function (string $state): HtmlString {
                        [$total, $pending] = array_pad(explode("\n", $state, 2), 2, '0,00');

                        return new HtmlString(
                            "<div class='leading-tight'><span class='text-xs'>Total: CRC " . e($total) . "</span><br><span class='text-xs fi-text-color-400'>Pendiente: CRC " . e($pending) . '</span></div>'
                        );
                    })
                    ->html(),

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

                TextColumn::make('description')
                    ->label('Descripcion')
                    ->limit(40)
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'paid' => 'Pagado',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

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
                EditAction::make()->visible(fn ($record) => $canUpdate && $record->status !== 'paid'),
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
                        defaults: ['paid_amount' => 0, 'status' => 'pending'],
                        fieldLabels: [
                            'customer.name' => 'Cliente',
                            'invoice_number' => 'Numero de Factura',
                            'issue_date' => 'Fecha de Emision',
                            'due_date' => 'Fecha de Vencimiento',
                            'description' => 'Descripcion',
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
                                        ->title('No se puede eliminar')
                                        ->body('Solo se pueden eliminar cuentas pagadas.')
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

