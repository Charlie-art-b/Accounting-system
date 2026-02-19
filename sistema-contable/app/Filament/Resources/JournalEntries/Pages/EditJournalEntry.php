<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    /*protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }*/

     protected function mutateFormDataBeforeFill(array $data): array
    {
        //bloquear si esta posteado
        if ($this->record->posted_at !== null) {
            throw new HttpException(403, 'No se puede editar un asiento posteado.');
        }

        return $data;
    }
}
