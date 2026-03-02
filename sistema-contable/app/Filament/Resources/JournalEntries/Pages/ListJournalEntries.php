<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Filament\Widgets\JournalEntrySummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            JournalEntrySummaryWidget::class,
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
                ->label('Crear asiento')
                ->color('primary')
                ->icon('heroicon-o-plus')
                ->visible(fn () => auth()->user()?->can('journal_entries.create') ?? false),
        ];
    }
}
