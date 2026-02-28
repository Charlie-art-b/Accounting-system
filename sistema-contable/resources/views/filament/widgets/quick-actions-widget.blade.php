<x-filament-widgets::widget>
    <div class="dashboard-card">
        <div class="dashboard-card__header">
            <h3>Acciones rapidas</h3>
        </div>

        <div class="dashboard-actions-grid">
            @foreach ($actions as $action)
                <a href="{{ $action['url'] }}" class="{{ $action['class'] }}">
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>

