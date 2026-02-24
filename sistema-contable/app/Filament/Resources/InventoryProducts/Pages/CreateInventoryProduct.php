<?php

namespace App\Filament\Resources\InventoryProducts\Pages;

use App\Filament\Resources\InventoryProducts\InventoryProductResource;
use App\Filament\Resources\Inventories\InventoryResource;
use App\Models\Inventory;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class CreateInventoryProduct extends CreateRecord
{
    protected static string $resource = InventoryProductResource::class;
    protected static ?string $title = 'Agregar existencias';

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('¡Producto agregado!')
            ->body('El producto fue agregado al inventario correctamente.');
    }

    public function mount(): void
    {
        parent::mount();
        
        // Pre-fill inventory_id if provided in query parameters
        $inventoryId = request()->query('inventory_id');
        if ($inventoryId) {
            $this->form->fill(['inventory_id' => $inventoryId]);
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        $inventoryId = request()->query('inventory_id');
        if ($inventoryId) {
            return InventoryProductResource::getUrl('index') . '?inventory=' . $inventoryId;
        }
        
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        $inventoryId = request()->query('inventory_id');
        $cancelUrl = $inventoryId 
            ? InventoryProductResource::getUrl('index') . '?inventory=' . $inventoryId
            : $this->getResource()::getUrl('index');

        return [
            Action::make('create')
                ->label('Crear')
                ->keyBindings(['mod+s'])
                ->requiresConfirmation()
                ->modalHeading('Confirmar creación')
                ->modalDescription('¿Deseas registrar este producto de inventario? Revisa los datos antes de confirmar.')
                ->modalSubmitActionLabel('Sí, crear')
                ->modalCancelActionLabel('No, cancelar')
                ->action(fn () => $this->create()),

            Action::make('cancel')
                ->label('Cancelar')
                ->color('gray')
                ->url($cancelUrl),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url(InventoryResource::getUrl('index'))
                ->tooltip('Volver a inventarios'),
        ];
    }
}
