<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\Customer;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            BulkAction::make('assign_customer')
                ->label('Asignar Cliente')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->form([
                    Select::make('customer_id')
                        ->label('Cliente')
                        ->options(Customer::pluck('name', 'id'))
                        ->required()
                        ->placeholder('Selecciona un cliente'),
                ])
                ->action(function (array $data, $records) {
                    $customer = Customer::find($data['customer_id']);
                    foreach ($records as $supplier) {
                        $supplier->customers()->attach($customer);
                    }
                    Notification::make()
                        ->title('Clientes asignados')
                        ->body('Los clientes han sido asignados a los proveedores seleccionados.')
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->modalHeading('Asignar Cliente')
                ->modalDescription('Selecciona un cliente para asignar a los proveedores seleccionados.')
                ->modalSubmitActionLabel('Asignar'),
        ];
    }
}
