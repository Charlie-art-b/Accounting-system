<?php

namespace App\Filament\Resources\CollectionManagement\Pages;

use App\Filament\Resources\CollectionManagement\CollectionManagementResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewCollectionManagement extends ViewRecord
{
    protected static string $resource = CollectionManagementResource::class;

    protected function getHeaderActions(): array
    {
        $canUpdate = Auth::user()?->can('collection_management.update') ?? false;
        $accountReceivable = $this->record->accountReceivable;

        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            Action::make('reverse_payment')
                ->label('Revertir pago')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn () => $canUpdate && $accountReceivable && $accountReceivable->payments()->where('is_reversal', false)->exists())
                ->requiresConfirmation()
                ->modalHeading('Revertir pago')
                ->modalDescription('Selecciona el pago que deseas revertir. Esta acción creará un registro de reverso y actualizará el monto pagado.')
                ->modalSubmitActionLabel('Revertir')
                ->form([
                    Select::make('payment_id')
                        ->label('Pago a revertir')
                        ->options(function () use ($accountReceivable) {
                            if (!$accountReceivable) return [];
                            
                            return $accountReceivable->payments()
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
                        
                        if ($payment->payable_id !== $this->record->account_receivable_id) {
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
