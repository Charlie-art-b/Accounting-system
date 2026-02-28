<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.welcome-widget';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $hour = (int) now()->format('H');

        $greeting = match (true) {
            $hour < 12 => 'Buenos dias',
            $hour < 19 => 'Buenas tardes',
            default => 'Buenas noches',
        };

        return [
            'greeting' => $greeting,
            'name' => auth()->user()?->name ?? 'Admin',
        ];
    }
}

