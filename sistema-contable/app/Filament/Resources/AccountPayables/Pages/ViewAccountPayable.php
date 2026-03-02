<?php

namespace App\Filament\Resources\AccountPayables\Pages;

use App\Filament\Resources\AccountPayables\AccountPayableResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewAccountPayable extends ViewRecord
{
    protected static string $resource = AccountPayableResource::class;

    public function getTitle(): string
    {
        return "Cuenta por Pagar #{$this->record->document_number}";
    }

    protected function getHeaderActions(): array
    {
        $canUpdate = Auth::user()?->can('account_payables.update') ?? false;

        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
            
            EditAction::make()
                ->visible(fn () => $canUpdate && $this->record->status !== 'paid'),

            Action::make('pay')
                ->label('Registrar pago')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => $canUpdate && $this->record->status !== 'paid' && $this->record->status !== 'voided')
                ->requiresConfirmation()
                ->modalHeading('Registrar pago')
                ->modalDescription('Este pago actualiza el monto pagado de la cuenta por pagar.')
                ->form([
                    TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()
                        ->minValue(0.01)
                        ->maxValue($this->record->getPendingAmountAttribute())
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
                ->action(function (array $data) {
                    try {
                        $service = new PaymentService();
                        $service->createPayment($this->record, (float) $data['amount'], $data['paid_at'], $data['note'] ?? null);

                        Notification::make()
                            ->title('Pago registrado exitosamente')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
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
                ->visible(fn () => $canUpdate && $this->record->status !== 'paid' && $this->record->status !== 'voided' && $this->record->payments()->where('is_reversal', false)->whereDoesntHave('reversal')->exists())
                ->requiresConfirmation()
                ->modalHeading('Revertir pago')
                ->modalDescription('Selecciona el pago que deseas revertir. Esta acción creará un registro de reverso y actualizará el monto pagado.')
                ->modalSubmitActionLabel('Revertir')
                ->form([
                    Select::make('payment_id')
                        ->label('Pago a revertir')
                        ->options(function () {
                            return $this->record->payments()
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
                ->action(function (array $data) {
                    try {
                        $payment = Payment::findOrFail($data['payment_id']);
                        
                        if ($payment->payable_id !== $this->record->id) {
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

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al revertir el pago')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
