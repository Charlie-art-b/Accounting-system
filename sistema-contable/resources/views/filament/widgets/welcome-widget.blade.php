<x-filament-widgets::widget>
    <div class="dashboard-card dashboard-welcome">
        <div class="dashboard-welcome__left">
            <div class="dashboard-welcome__avatar">
                {{ \Illuminate\Support\Str::substr($name, 0, 1) }}
            </div>
            <div>
                <p class="dashboard-welcome__title">{{ $greeting }}</p>
                <p class="dashboard-welcome__subtitle">Panel de administracion CAHEN</p>
            </div>
        </div>

        <div class="dashboard-welcome__actions">
            <a class="dashboard-btn dashboard-btn--primary" href="{{ \App\Filament\Resources\AccountReceivables\AccountReceivableResource::getUrl('create') }}">
                Nueva factura
            </a>
            <a class="dashboard-btn dashboard-btn--ghost" href="{{ \App\Filament\Pages\FinancialReports::getUrl() }}">
                Reportes
            </a>
        </div>
    </div>
</x-filament-widgets::widget>

