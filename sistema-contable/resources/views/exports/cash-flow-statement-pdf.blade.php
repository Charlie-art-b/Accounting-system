@extends('pdf._layout')

@section('title', 'Estado de Flujos de Efectivo')
@section('report_name', 'Estado de Flujos de Efectivo')

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
                <th class="right" style="width:160px;">Monto</th>
            </tr>
        </thead>

        <tbody>
            <tr class="section-row"><td colspan="2">Actividades de operación</td></tr>

            <tr class="data-row">
                <td class="left">Utilidad neta</td>
                <td class="right">{{ number_format($data['utilidad_neta'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="data-row">
                <td class="left">Variación capital de trabajo</td>
                <td class="right">{{ number_format($data['variacion_capital_trabajo'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="total">
                <td class="left">Flujo neto de actividades de operación</td>
                <td class="right">{{ number_format($data['flujo_operativo'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="section-row"><td colspan="2">Actividades de inversión</td></tr>

            <tr class="data-row">
                <td class="left">Flujo de inversión</td>
                <td class="right">{{ number_format($data['flujo_inversion'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="section-row"><td colspan="2">Actividades de financiamiento</td></tr>

            <tr class="data-row">
                <td class="left">Flujo de financiamiento</td>
                <td class="right">{{ number_format($data['flujo_financiamiento'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="total">
                <td class="left">Flujo neto del período</td>
                <td class="right">{{ number_format($data['flujo_neto'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="total">
                <td class="left">Efectivo final</td>
                <td class="right">{{ number_format($data['efectivo_final'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection