@php
    $totalIngresos = data_get($p, 'ingresos.total', 0);
    $totalGastos   = data_get($p, 'gastos.total', 0);
    $utilidad      = $p['utilidad_neta'] ?? 0;
    $margen        = $p['margen_neto'] ?? 0;

    $ingRows = data_get($p, 'ingresos.detalles', []);
    $gasRows = data_get($p, 'gastos.detalles', []);

    $colorIngresos = $totalIngresos < 0 ? '#dc2626' : '#111827';
    $colorGastos   = $totalGastos < 0 ? '#dc2626' : '#111827';
    $colorUtilidad = $utilidad < 0 ? '#dc2626' : '#16a34a';
@endphp

{{-- RESUMEN --}}
<div style="display:grid; grid-template-columns: 1fr; gap:14px; margin-bottom:18px;">
    <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:14px;">

        {{-- Total ingresos --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Total Ingresos</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorIngresos }}; font-variant-numeric: tabular-nums;">
                {{ number_format($totalIngresos, 2) }}
            </div>
        </div>

        {{-- Total gastos --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Total Gastos</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorGastos }}; font-variant-numeric: tabular-nums;">
                {{ number_format($totalGastos, 2) }}
            </div>
        </div>

        {{-- Utilidad neta --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Utilidad Neta</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorUtilidad }}; font-variant-numeric: tabular-nums;">
                {{ number_format($utilidad, 2) }}
            </div>
            <div style="margin-top:6px; font-size:12px; color:#6b7280;">
                Margen: <span style="font-variant-numeric: tabular-nums;">{{ number_format($margen, 2) }}%</span>
            </div>
        </div>

    </div>
</div>

{{-- TABLAS --}}
<div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:14px;">

    {{-- INGRESOS --}}
    <x-filament::section heading="Ingresos">

        @if (!empty($ingRows))
            <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:14px; background:white;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">Código</th>
                            <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">Cuenta</th>
                            <th style="text-align:right; padding:14px 18px; font-weight:600; color:#374151;">Monto</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($ingRows as $r)
                            @php
                                $monto = $r['monto'] ?? 0;
                                $montoColor = $monto < 0 ? '#dc2626' : '#111827';
                            @endphp

                            <tr style="border-top:1px solid #e5e7eb;"
                                onmouseover="this.style.background='#f9fafb'"
                                onmouseout="this.style.background='transparent'">

                                <td style="padding:14px 18px; white-space:nowrap;">{{ $r['codigo'] ?? '' }}</td>
                                <td style="padding:14px 18px;">{{ $r['nombre'] ?? '' }}</td>
                                <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $montoColor }};">
                                    {{ number_format($monto, 2) }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- Total ingresos --}}
                        <tr style="border-top:2px solid #d1d5db; background:#f9fafb; font-weight:700;">
                            <td style="padding:16px 18px;" colspan="2">Total Ingresos</td>
                            <td style="padding:16px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $colorIngresos }};">
                                {{ number_format($totalIngresos, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div style="font-size:14px; color:#6b7280;">Sin ingresos para el período.</div>
        @endif

    </x-filament::section>

    {{-- GASTOS --}}
    <x-filament::section heading="Gastos">

        @if (!empty($gasRows))
            <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:14px; background:white;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">Código</th>
                            <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">Cuenta</th>
                            <th style="text-align:right; padding:14px 18px; font-weight:600; color:#374151;">Monto</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($gasRows as $r)
                            @php
                                $monto = $r['monto'] ?? 0;
                                $montoColor = $monto < 0 ? '#dc2626' : '#111827';
                            @endphp

                            <tr style="border-top:1px solid #e5e7eb;"
                                onmouseover="this.style.background='#f9fafb'"
                                onmouseout="this.style.background='transparent'">

                                <td style="padding:14px 18px; white-space:nowrap;">{{ $r['codigo'] ?? '' }}</td>
                                <td style="padding:14px 18px;">{{ $r['nombre'] ?? '' }}</td>
                                <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $montoColor }};">
                                    {{ number_format($monto, 2) }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- Total gastos --}}
                        <tr style="border-top:2px solid #d1d5db; background:#f9fafb; font-weight:700;">
                            <td style="padding:16px 18px;" colspan="2">Total Gastos</td>
                            <td style="padding:16px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $colorGastos }};">
                                {{ number_format($totalGastos, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div style="font-size:14px; color:#6b7280;">Sin gastos para el período.</div>
        @endif

    </x-filament::section>

</div>

{{-- Responsive: en pantallas pequeñas apilar --}}
<style>
@media (max-width: 900px) {
    .er-grid-2 {
        grid-template-columns: 1fr !important;
    }
}
</style>