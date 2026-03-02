@extends('pdf._layout')

@section('title', 'Balance de Comprobación')
@section('report_name', 'Balance de Comprobación')

@php
    use Carbon\Carbon;

    $fechaCorte = Carbon::parse($fechaFin)
        ->locale('es')
        ->translatedFormat('d \d\e F Y');
@endphp

@section('report_period')
    Al {{ strtolower($fechaCorte) }}
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th class="left" style="width:90px;">Código</th>
                <th class="left">Cuenta</th>
                <th class="left" style="width:130px;">Clasificación</th>
                <th class="right" style="width:120px;">Débito</th>
                <th class="right" style="width:120px;">Crédito</th>
            </tr>
        </thead>

        <tbody>
            @php
                $totalDebe = 0;
                $totalHaber = 0;
            @endphp

            @forelse(($data['cuentas'] ?? []) as $cuenta)
                @php
                    $debe = (float)($cuenta['debe'] ?? 0);
                    $haber = (float)($cuenta['haber'] ?? 0);
                    $totalDebe += $debe;
                    $totalHaber += $haber;
                @endphp

                <tr class="data-row">
                    <td class="left">{{ $cuenta['codigo'] ?? '' }}</td>
                    <td class="left">{{ $cuenta['nombre'] ?? '' }}</td>
                    <td class="left muted">{{ $cuenta['clasificacion'] ?? '' }}</td>
                    <td class="right">{{ number_format($debe, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($haber, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr class="data-row">
                    <td colspan="5" class="center muted" style="padding: 14px;">
                        No hay datos para mostrar.
                    </td>
                </tr>
            @endforelse

            <tr class="total">
                <td colspan="3" class="left">Totales</td>
                <td class="right">{{ number_format($totalDebe, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($totalHaber, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection