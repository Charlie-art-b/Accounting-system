<?php

namespace App\Filament\Resources\AccountingAccounts\Pages;

use App\Filament\Resources\AccountingAccounts\AccountingAccountResource;
use App\Services\PdfFallbackService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountingAccount extends ViewRecord
{
    protected static string $resource = AccountingAccountResource::class;

    protected static ?string $title = 'Detalles de la Cuenta Contable';

    public function getTitle(): string
    {
        return $this->record->name ?? 'Detalles';
    }

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
                ->visible(fn () => auth()->user()?->can('accounting_accounts.view'))
                ->action(fn () => app(PdfFallbackService::class)->download(
                    view: 'exports.generic-model-pdf',
                    data: [
                        'title' => 'Cuenta Contable',
                        'fields' => ['customer.name', 'code', 'name', 'type', 'classification', 'report_section', 'normal_balance', 'parent.code', 'level', 'status'],
                        'displayFields' => ['Cliente', 'Código', 'Nombre', 'Tipo', 'Clasificación', 'Sección Reporte', 'Naturaleza', 'Código Padre', 'Nivel', 'Estado'],
                        'records' => collect([$this->record]),
                    ],
                    baseFileName: 'cuenta_contable_' . $this->record->id . '_' . now()->format('Y-m-d_H-i-s'),
                    paper: 'a4',
                    orientation: 'landscape',
                )),

            EditAction::make()
                ->label('Editar')
                ->visible(fn () => auth()->user()?->can('accounting_accounts.update')),
        ];
    }
}
