@extends('pdf._layout')

@section('title', 'Balance de Comprobación')
@section('report_name', 'Balance de Comprobación')

@php
    use Carbon\Carbon;

    $fecha = Carbon::parse($data['fecha'] ?? now())
        ->locale('es')
        ->translatedFormat('d \d\e F \d\e Y');
@endphp

@section('report_period')
    Al {{ strtolower($fecha) }}
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th class="left" style="width:90px;">Código</th>
                <th class="left">Cuenta</th>
                <th class="right" style="width:120px;">Débito (₡)</th>
                <th class="right" style="width:120px;">Crédito (₡)</th>
            </tr>
        </thead>

        <tbody>
            @forelse($data['cuentas'] as $cuenta)
                <tr class="data-row">
                    <td class="left">{{ $cuenta['codigo'] ?? '' }}</td>
                    <td class="left">{{ $cuenta['nombre'] ?? '' }}</td>
                    <td class="right">{{ number_format($cuenta['debe'] ?? 0, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($cuenta['haber'] ?? 0, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr class="data-row">
                    <td colspan="4" class="center muted" style="padding: 14px;">
                        No hay datos para mostrar.
                    </td>
                </tr>
            @endforelse

            <tr class="total">
                <td colspan="2" class="left">Totales</td>
                <td class="right">₡ {{ number_format($data['total_debe'] ?? 0, 2, ',', '.') }}</td>
                <td class="right">₡ {{ number_format($data['total_haber'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @php
        $totalDebe = (float) ($data['total_debe'] ?? 0);
        $totalHaber = (float) ($data['total_haber'] ?? 0);
        $diferencia = abs($totalDebe - $totalHaber);
        $balanceOk = round($totalDebe, 2) === round($totalHaber, 2);
    @endphp

    <div style="margin-top: 14px; font-size: 10px;">
        @if($balanceOk)
            <div style="padding: 10px; border: 1px solid #cfe9d6; background: #f3fbf6;">
                <div style="font-weight: 800; color: #1f7a3a;">✓ Balance correcto</div>
                <div style="color:#2b6b3d;">Débitos = Créditos</div>
            </div>
        @else
            <div style="padding: 10px; border: 1px solid #f2c9c9; background: #fff5f5;">
                <div style="font-weight: 800; color: #b42318;">✗ Balance incorrecto</div>
                <div style="color:#8a1a1a;">
                    Diferencia: ₡ {{ number_format($diferencia, 2, ',', '.') }}
                </div>
            </div>
        @endif
    </div>
@endsection