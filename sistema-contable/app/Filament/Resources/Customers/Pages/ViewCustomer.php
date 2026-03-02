<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Services\PdfFallbackService;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;
    protected static ?string $title = 'Detalles del cliente';
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
                ->visible(fn () => auth()->user()?->can('customers.view'))
                ->action(fn () => app(PdfFallbackService::class)->download(
                    view: 'exports.generic-model-pdf',
                    data: [
                        'title' => 'Cliente',
                        'fields' => [
                            'name',
                            'first_last_name',
                            'second_last_name',
                            'id_type',
                            'identification',
                            'email',
                            'phone',
                            'address',
                            'customer_type',
                            'status',
                            'notes',
                        ],
                        'displayFields' => [
                            'Nombre',
                            'Primer Apellido',
                            'Segundo Apellido',
                            'Tipo de Identificación',
                            'Identificación',
                            'Correo Electrónico',
                            'Teléfono',
                            'Dirección',
                            'Tipo de Cliente',
                            'Estado',
                            'Notas',
                        ],
                        'records' => collect([$this->record]),
                    ],
                    baseFileName: 'cliente_' . $this->record->id . '_' . now()->format('Y-m-d_H-i-s'),
                    paper: 'a4',
                    orientation: 'landscape',
                )),

            EditAction::make()
                ->visible(fn () => auth()->user()?->can('customers.update')),
        ];
    }
}
