@php
    $ingresos        = $p['ingresos'] ?? 0;
    $gastosOperativos= $p['gastos_operativos'] ?? 0;
    $depreciacion    = $p['depreciacion'] ?? 0;
    $otrosGastos     = $p['otros_gastos'] ?? 0;

    $uAntesDep = $p['utilidad_antes_depreciacion'] ?? 0;
    $uAntesImp = $p['utilidad_antes_impuestos'] ?? 0;
    $impuestos = $p['impuestos'] ?? 0;
    $uNeta     = $p['utilidad_neta'] ?? 0;

    $colorIngresos = $ingresos < 0 ? '#dc2626' : '#111827';
    $colorGastosOp = $gastosOperativos < 0 ? '#dc2626' : '#111827';
    $colorAntesImp = $uAntesImp < 0 ? '#dc2626' : '#111827';
    $colorNeta     = $uNeta < 0 ? '#dc2626' : '#16a34a';
@endphp

{{-- RESUMEN --}}
<div style="display:grid; grid-template-columns: 1fr; gap:14px; margin-bottom:18px;">
    <div style="display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:14px;">

        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Ingresos</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorIngresos }}; font-variant-numeric: tabular-nums;">
                {{ number_format($ingresos, 2) }}
            </div>
        </div>

        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Gastos Operativos</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorGastosOp }}; font-variant-numeric: tabular-nums;">
                {{ number_format($gastosOperativos, 2) }}
            </div>
        </div>

        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Utilidad antes Impuestos</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorAntesImp }}; font-variant-numeric: tabular-nums;">
                {{ number_format($uAntesImp, 2) }}
            </div>
        </div>

        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Utilidad Neta</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorNeta }}; font-variant-numeric: tabular-nums;">
                {{ number_format($uNeta, 2) }}
            </div>
        </div>

    </div>
</div>

{{-- DETALLE --}}
<x-filament::section heading="Detalle">

    <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:14px; background:white;">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">

            <thead>
                <tr style="background:#f9fafb;">
                    <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">Concepto</th>
                    <th style="text-align:right; padding:14px 18px; font-weight:600; color:#374151;">Monto</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $rows = [
                        ['Ingresos', $ingresos],
                        ['Gastos operativos', $gastosOperativos],
                        ['Utilidad antes depreciación', $uAntesDep],
                        ['Depreciación', $depreciacion],
                        ['Otros gastos', $otrosGastos],
                        ['Utilidad antes impuestos', $uAntesImp, 'bold'],
                        ['Impuestos', $impuestos],
                        ['Utilidad neta', $uNeta, 'bold_total'],
                    ];
                @endphp

                @foreach ($rows as $row)
                    @php
                        $label = $row[0];
                        $value = $row[1] ?? 0;
                        $style = $row[2] ?? null;
                        $valueColor = $value < 0 ? '#dc2626' : '#111827';

                        $trStyle = 'border-top:1px solid #e5e7eb;';
                        $tdStyle = 'padding:14px 18px;';
                        $tdRight = $tdStyle . ' text-align:right; font-variant-numeric: tabular-nums; color:' . $valueColor . ';';

                        if ($style === 'bold') {
                            $trStyle = 'border-top:2px solid #d1d5db; background:#f9fafb; font-weight:700;';
                        }
                        if ($style === 'bold_total') {
                            $trStyle = 'border-top:2px solid #d1d5db; font-weight:800;';
                            $tdRight = $tdStyle . ' text-align:right; font-variant-numeric: tabular-nums; font-size:15px; color:' . ($uNeta < 0 ? '#dc2626' : '#16a34a') . ';';
                        }
                    @endphp

                    <tr style="{{ $trStyle }}"
                        onmouseover="this.style.background='#f9fafb'"
                        onmouseout="this.style.background='transparent'">
                        <td style="{{ $tdStyle }}">{{ $label }}</td>
                        <td style="{{ $tdRight }}">{{ number_format($value, 2) }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

</x-filament::section>

{{-- Responsive: 4 cards a 2 o 1 en pantallas pequeñas --}}
<style>
@media (max-width: 1100px) {
    .eri-grid-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}
@media (max-width: 700px) {
    .eri-grid-4 {
        grid-template-columns: 1fr !important;
    }
}
</style>