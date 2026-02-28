@php
    $debe = $p['total_debe'] ?? 0;
    $haber = $p['total_haber'] ?? 0;
    $balanceado = $p['balanceado'] ?? false;
    $diff = $p['diferencia'] ?? 0;

    $rows = $p['cuentas'] ?? [];

    $colorDebe = $debe < 0 ? '#dc2626' : '#111827';
    $colorHaber = $haber < 0 ? '#dc2626' : '#111827';
    $colorBalance = $balanceado ? '#16a34a' : '#dc2626';
@endphp

{{-- RESUMEN --}}
<div style="display:grid; grid-template-columns: 1fr; gap:14px; margin-bottom:18px;">
    <div style="display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:14px;">

        {{-- Total Debe --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Total Debe</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorDebe }}; font-variant-numeric: tabular-nums;">
                {{ number_format($debe, 2) }}
            </div>
        </div>

        {{-- Total Haber --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Total Haber</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorHaber }}; font-variant-numeric: tabular-nums;">
                {{ number_format($haber, 2) }}
            </div>
        </div>

        {{-- Balanceado --}}
        <div style="border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; background:white;">
            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Balanceado</div>
            <div style="font-size:24px; font-weight:800; color: {{ $colorBalance }};">
                {{ $balanceado ? 'Sí' : 'No' }}
            </div>
            <div style="margin-top:6px; font-size:12px; color:#6b7280;">
                Diferencia: <span style="font-variant-numeric: tabular-nums;">{{ number_format($diff, 2) }}</span>
            </div>
        </div>

    </div>
</div>

{{-- DETALLE --}}
<x-filament::section heading="Cuentas">

    @if (!empty($rows))

        <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:14px; background:white;">

            <table style="width:100%; border-collapse:collapse; font-size:14px;">

                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">Código</th>
                        <th style="text-align:left; padding:14px 18px; font-weight:600; color:#374151;">Cuenta</th>
                        <th style="text-align:right; padding:14px 18px; font-weight:600; color:#374151;">Debe</th>
                        <th style="text-align:right; padding:14px 18px; font-weight:600; color:#374151;">Haber</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($rows as $r)

                        @php
                            $rowDebe = $r['debe'] ?? 0;
                            $rowHaber = $r['haber'] ?? 0;
                        @endphp

                        <tr style="border-top:1px solid #e5e7eb;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='transparent'">

                            <td style="padding:14px 18px; white-space:nowrap;">
                                {{ $r['codigo'] ?? '' }}
                            </td>

                            <td style="padding:14px 18px;">
                                {{ $r['nombre'] ?? '' }}
                            </td>

                            <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $rowDebe < 0 ? '#dc2626' : '#111827' }};">
                                {{ number_format($rowDebe, 2) }}
                            </td>

                            <td style="padding:14px 18px; text-align:right; font-variant-numeric: tabular-nums; color: {{ $rowHaber < 0 ? '#dc2626' : '#111827' }};">
                                {{ number_format($rowHaber, 2) }}
                            </td>

                        </tr>

                    @endforeach
                </tbody>

            </table>

        </div>

    @else
        <div style="font-size:14px; color:#6b7280;">No hay movimientos para el período.</div>
    @endif

</x-filament::section>