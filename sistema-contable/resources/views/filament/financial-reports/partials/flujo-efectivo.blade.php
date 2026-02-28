@php
    $utilidad = $p['utilidad_neta'] ?? 0;
    $operativo = $p['flujo_operativo'] ?? 0;
    $neto = $p['flujo_neto'] ?? 0;

    $variacion = $p['variacion_capital_trabajo'] ?? 0;
    $inversion = $p['flujo_inversion'] ?? 0;
    $financiamiento = $p['flujo_financiamiento'] ?? 0;
    $efectivoFinal = $p['efectivo_final'] ?? 0;

    $colorUtilidad = $utilidad < 0 ? '#dc2626' : '#111827';
    $colorOperativo = $operativo < 0 ? '#dc2626' : '#111827';
    $colorNeto = $neto < 0 ? '#dc2626' : '#16a34a';
@endphp

{{-- RESUMEN (cards inline) --}}
<div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:14px; margin-bottom:18px;">

    <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
        <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Utilidad Neta</div>
        <div style="font-size:24px; font-weight:800; color: {{ $colorUtilidad }}; font-variant-numeric: tabular-nums;">
            {{ number_format($utilidad, 2) }}
        </div>
    </div>

    <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
        <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Flujo Operativo</div>
        <div style="font-size:24px; font-weight:800; color: {{ $colorOperativo }}; font-variant-numeric: tabular-nums;">
            {{ number_format($operativo, 2) }}
        </div>
    </div>

    <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
        <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Flujo Neto</div>
        <div style="font-size:24px; font-weight:800; color: {{ $colorNeto }}; font-variant-numeric: tabular-nums;">
            {{ number_format($neto, 2) }}
        </div>
    </div>

</div>

{{-- DETALLE --}}
<x-filament::section heading="Detalle del flujo">

    <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:14px; background:white;">

        <table style="width:100%; border-collapse:collapse; font-size:14px;">

            <thead>
                <tr style="background:#f9fafb;">
                    <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">Concepto</th>
                    <th style="text-align:right; padding:14px 18px; font-weight:600; color:#374151;">Monto</th>
                </tr>
            </thead>

            <tbody>

                <tr style="border-top:1px solid #e5e7eb;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 18px;">Variación Capital de Trabajo</td>
                    <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $variacion < 0 ? '#dc2626' : '#111827' }};">
                        {{ number_format($variacion, 2) }}
                    </td>
                </tr>

                <tr style="border-top:1px solid #e5e7eb;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 18px;">Flujo de Inversión</td>
                    <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $inversion < 0 ? '#dc2626' : '#111827' }};">
                        {{ number_format($inversion, 2) }}
                    </td>
                </tr>

                <tr style="border-top:1px solid #e5e7eb;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 18px;">Flujo de Financiamiento</td>
                    <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $financiamiento < 0 ? '#dc2626' : '#111827' }};">
                        {{ number_format($financiamiento, 2) }}
                    </td>
                </tr>

                <tr style="border-top:2px solid #d1d5db; background:#f9fafb; font-weight:700;">
                    <td style="padding:14px 18px;">Flujo Neto</td>
                    <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $neto < 0 ? '#dc2626' : '#16a34a' }};">
                        {{ number_format($neto, 2) }}
                    </td>
                </tr>

                <tr style="border-top:2px solid #d1d5db; font-weight:800;">
                    <td style="padding:16px 18px;">Efectivo Final</td>
                    <td style="padding:16px 18px; text-align:right; font-variant-numeric: tabular-nums; font-size:15px; color: {{ $efectivoFinal < 0 ? '#dc2626' : '#16a34a' }};">
                        {{ number_format($efectivoFinal, 2) }}
                    </td>
                </tr>

            </tbody>

        </table>

    </div>

</x-filament::section>

{{-- Responsive para las 3 cards --}}
<style>
@media (max-width: 900px) {
    .flow-grid-3 {
        grid-template-columns: 1fr !important;
    }
}
</style>