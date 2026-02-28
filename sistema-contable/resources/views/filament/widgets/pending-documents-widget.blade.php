<x-filament-widgets::widget>
    <div class="dashboard-card">
        <div class="dashboard-card__header">
            <h3>Documentos pendientes</h3>
        </div>

        <div class="dashboard-pending-list">
            @foreach ($rows as $row)
                <div class="dashboard-pending-row">
                    <div class="dashboard-pending-row__left">
                        <span class="dashboard-dot {{ $row['class'] }}"></span>
                        <span>{{ $row['count'] }} {{ $row['label'] }}</span>
                    </div>
                    <strong class="tabular-nums">{{ $row['amount'] }}</strong>
                </div>
            @endforeach
        </div>

        <div class="dashboard-net">
            <span>Balance neto</span>
            <span class="dashboard-net-badge {{ $netBalanceClass }}">
                {{ $netBalancePrefix }}{{ $netBalance }}
            </span>
        </div>
    </div>
</x-filament-widgets::widget>

