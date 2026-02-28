<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AccountsStatusChartWidget;
use App\Filament\Widgets\AccountsTrendChartWidget;
use App\Filament\Widgets\BusinessOverviewWidget;
use App\Filament\Pages\Dashboard;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Enums\ThemeMode;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->globalSearch(false)
            ->path('admin')
            ->brandName('CAHEN Servicios Contables')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('images/logo.png'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->defaultThemeMode(ThemeMode::Dark)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full)
            ->login()
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->userMenuItems([
                'logout' => fn (Action $action): Action => $action
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar salida')
                    ->modalDescription('¿Deseas cerrar sesión ahora?')
                    ->modalSubmitActionLabel('Sí, salir')
                    ->modalCancelActionLabel('Cancelar'),
            ])
            ->colors([
                'primary' => Color::hex('#991FA6'),
                'info' => Color::hex('#6F85FE'),
                'success' => Color::hex('#2441E1'),
                'warning' => Color::hex('#EA6DC9'),
                'danger' => Color::hex('#E70D98'),
                'gray' => Color::hex('#130552'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                BusinessOverviewWidget::class,
                AccountsTrendChartWidget::class,
                AccountsStatusChartWidget::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
