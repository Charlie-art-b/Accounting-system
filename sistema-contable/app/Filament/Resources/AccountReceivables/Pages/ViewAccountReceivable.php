<?php

namespace App\Filament\Resources\AccountReceivables\Pages;

use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use App\Services\PdfFallbackService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewAccountReceivable extends ViewRecord
{
    protected static string $resource = AccountReceivableResource::class;
    protected static ?string $title = 'Detalles de la cuenta por cobrar';
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a la lista')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->visible(fn () => auth()->user()?->can('account_receivables.view'))
                ->action(fn () => app(PdfFallbackService::class)->download(
                    view: 'exports.generic-model-pdf',
                    data: [
                        'title' => 'Cuenta por Cobrar',
                        'fields' => ['customer.name', 'invoice_number', 'issue_date', 'due_date', 'description', 'total_amount', 'paid_amount', 'status'],
                        'displayFields' => ['Cliente', 'Número de Factura', 'Fecha de Emisión', 'Fecha de Vencimiento', 'Descripción', 'Monto Total', 'Monto Pagado', 'Estado'],
                        'records' => collect([$this->record]),
                    ],
                    baseFileName: 'cuenta_por_cobrar_' . $this->record->id . '_' . now()->format('Y-m-d_H-i-s'),
                    paper: 'a4',
                    orientation: 'landscape',
                )),
                
            EditAction::make()
                ->label('Editar')
                ->color('primary')
                ->keyBindings(['mod+e'])
                ->visible(fn () => auth()->user()?->can('account_receivables.update') && $this->record->status !== 'paid'),
        ];
    }
}