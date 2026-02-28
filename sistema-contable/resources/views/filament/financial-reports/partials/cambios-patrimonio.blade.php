@php
    $capitalInicial = $p['capital_inicial'] ?? 0;
    $aportes = $p['aportes'] ?? 0;
    $retiros = $p['retiros'] ?? 0;
    $utilidad = $p['utilidad_periodo'] ?? 0;
    $patrimonioFinal = $p['patrimonio_final'] ?? 0;
    $cambioNeto = $p['cambio_neto'] ?? 0;

    $colorCapital = $capitalInicial < 0 ? '#dc2626' : '#111827';
    $colorFinal = $patrimonioFinal < 0 ? '#dc2626' : '#111827';
    $colorCambio = $cambioNeto < 0 ? '#dc2626' : '#16a34a';
@endphp

{{-- RESUMEN --}}
<div style="display:grid; grid-template-columns: 1fr; gap:14px; margin-bottom:18px;">
    <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:14px;">
        {{-- Capital inicial --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Capital Inicial</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorCapital }}; font-variant-numeric: tabular-nums;">
                {{ number_format($capitalInicial, 2) }}
            </div>
        </div>

        {{-- Patrimonio final --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Patrimonio Final</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorFinal }}; font-variant-numeric: tabular-nums;">
                {{ number_format($patrimonioFinal, 2) }}
            </div>
        </div>

        {{-- Cambio neto --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Cambio Neto</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorCambio }}; font-variant-numeric: tabular-nums;">
                {{ number_format($cambioNeto, 2) }}
            </div>
        </div>
    </div>
</div>

{{-- DETALLE --}}
<x-filament::section heading="Detalle de cambios en el patrimonio">

    <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:14px; background:white;">

        <table style="width:100%; border-collapse:collapse; font-size:14px;">

            <thead>
                <tr style="background:#f9fafb;">
                    <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">
                        Concepto
                    </th>
                    <th style="text-align:right; padding:14px 18px; font-weight:600; color:#374151;">
                        Monto
                    </th>
                </tr>
            </thead>

            <tbody>

                <tr style="border-top:1px solid #e5e7eb;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 18px;">Aportes</td>
                    <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $aportes < 0 ? '#dc2626' : '#111827' }};">
                        {{ number_format($aportes, 2) }}
                    </td>
                </tr>

                <tr style="border-top:1px solid #e5e7eb;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 18px;">Retiros</td>
                    <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $retiros < 0 ? '#dc2626' : '#111827' }};">
                        {{ number_format($retiros, 2) }}
                    </td>
                </tr>

                <tr style="border-top:1px solid #e5e7eb;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 18px;">Utilidad del período</td>
                    <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $utilidad < 0 ? '#dc2626' : '#111827' }};">
                        {{ number_format($utilidad, 2) }}
                    </td>
                </tr>

                <tr style="border-top:2px solid #d1d5db; background:#f9fafb; font-weight:700;">
                    <td style="padding:16px 18px;">Patrimonio final</td>
                    <td style="padding:16px 18px; text-align:right; font-variant-numeric: tabular-nums; font-size:15px; color: {{ $patrimonioFinal < 0 ? '#dc2626' : '#16a34a' }};">
                        {{ number_format($patrimonioFinal, 2) }}
                    </td>
                </tr>

            </tbody>

        </table>
    </div>

</x-filament::section>