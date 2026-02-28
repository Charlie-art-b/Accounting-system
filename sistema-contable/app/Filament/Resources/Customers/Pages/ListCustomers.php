<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Widgets\CustomerSummaryWidget;
use App\Models\Supplier;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerSummaryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear cliente')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->keyBindings(['mod+n'])
               ->visible(fn () => auth()->user()?->can('customers.create'))            
,
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            BulkAction::make('assign_supplier')
                ->label('Asignar Proveedor')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->form([
                    Select::make('supplier_id')
                        ->label('Proveedor')
                        ->options(Supplier::pluck('nombre_razon_social', 'id'))
                        ->required()
                        ->placeholder('Selecciona un proveedor'),
                ])
                ->action(function (array $data, $records) {
                    $supplier = Supplier::find($data['supplier_id']);
                    foreach ($records as $customer) {
                        $customer->suppliers()->attach($supplier);
                    }
                    Notification::make()
                        ->title('Proveedores asignados')
                        ->body('Los proveedores han sido asignados a los clientes seleccionados.')
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation()
                ->modalHeading('Asignar Proveedor')
                ->modalDescription('Selecciona un proveedor para asignar a los clientes seleccionados.')
                ->modalSubmitActionLabel('Asignar'),
        ];
    }
}
