@extends('pdf._layout')

@section('title', 'Estado de Cambios en el Patrimonio')
@section('report_name', 'Estado de Cambios en el Patrimonio')

@php
    use Carbon\Carbon;

    $fecha = Carbon::parse($data['fecha'] ?? now())
        ->locale('es')
        ->translatedFormat('d \d\e F Y');
@endphp

@section('report_period')
    Al {{ strtolower($fecha) }}
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th class="left">Concepto</th>
                <th class="right" style="width:160px;">Monto (₡)</th>
            </tr>
        </thead>

        <tbody>
            <tr class="section-row"><td colspan="2">Detalle</td></tr>

            <tr class="data-row">
                <td class="left"><strong>Capital inicial</strong></td>
                <td class="right"><strong>{{ number_format($data['capital_inicial'] ?? 0, 2, ',', '.') }}</strong></td>
            </tr>

            <tr class="data-row">
                <td class="left">Aportes del período</td>
                <td class="right">{{ number_format($data['aportes'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="data-row">
                <td class="left">Retiros del período</td>
                <td class="right">({{ number_format($data['retiros'] ?? 0, 2, ',', '.') }})</td>
            </tr>

            <tr class="subtotal">
                <td class="left">Resultado del período</td>
                <td class="right">{{ number_format($data['utilidad_periodo'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="total">
                <td class="left">Patrimonio final</td>
                <td class="right">{{ number_format($data['patrimonio_final'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="subtotal">
                <td class="left">Cambio neto del patrimonio</td>
                <td class="right">{{ number_format($data['cambio_neto'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection