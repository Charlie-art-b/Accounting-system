<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\FinancialReports;
use App\Filament\Resources\AccountReceivables\AccountReceivableResource;
use App\Filament\Resources\CollectionManagement\CollectionManagementResource;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.quick-actions-widget';

    protected int | string | array $columnSpan = 1;

    protected function getViewData(): array
    {
        return [
            'actions' => [
                [
                    'label' => 'Nuevo Cliente',
                    'url' => CustomerResource::getUrl('create'),
                    'class' => 'dashboard-action dashboard-action--pink',
                ],
                [
                    'label' => 'Nueva Factura',
                    'url' => AccountReceivableResource::getUrl('create'),
                    'class' => 'dashboard-action dashboard-action--indigo',
                ],
                [
                    'label' => 'Registrar Pago',
                    'url' => CollectionManagementResource::getUrl('index'),
                    'class' => 'dashboard-action dashboard-action--sky',
                ],
                [
                    'label' => 'Ver Reportes',
                    'url' => FinancialReports::getUrl(),
                    'class' => 'dashboard-action dashboard-action--violet',
                ],
            ],
        ];
    }
}

