<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\UserSummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            UserSummaryWidget::class,
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
            ->label ('Crear usuario')
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->keyBindings(['mod+n'])
            ->visible(fn () => auth()->user()?->can('users.create'))            
        ];
    }
        
    
      protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('id', '!=', auth()->id());
    }
}
