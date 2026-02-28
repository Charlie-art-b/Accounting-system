<x-filament-widgets::widget>
    <div class="dashboard-card">
        <div class="dashboard-card__header">
            <h3>Actividad reciente</h3>
        </div>

        <div class="dashboard-activity-list">
            @forelse ($activities as $activity)
                <div class="dashboard-activity-item">
                    <span class="dashboard-activity-icon {{ $activity['iconClass'] }}"></span>
                    <div class="dashboard-activity-main">
                        <p class="dashboard-activity-title">{{ $activity['title'] }}</p>
                        <p class="dashboard-activity-time">{{ $activity['time'] }}</p>
                    </div>
                    <strong class="dashboard-activity-amount tabular-nums {{ $activity['amountClass'] }}">
                        {{ $activity['prefix'] }}{{ $activity['amount'] }}
                    </strong>
                </div>
            @empty
                <p class="dashboard-empty">Sin movimientos recientes.</p>
            @endforelse
        </div>
    </div>
</x-filament-widgets::widget>

