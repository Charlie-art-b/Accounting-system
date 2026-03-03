@extends('pdf._layout')

@section('title', 'Estado de Resultados Integral')
@section('report_name', 'Estado de Resultados Integral')

@php
    use Carbon\Carbon;

    $inicio = Carbon::parse($fechaInicio ?? now())
        ->locale('es')
        ->translatedFormat('d \d\e F Y');

    $fin = Carbon::parse($fechaFin ?? now())
        ->locale('es')
        ->translatedFormat('d \d\e F Y');
@endphp

@section('report_period')
    Del {{ strtolower($inicio) }} al {{ strtolower($fin) }}
@endsection

@section('content')
    <table>
        <thead>
            <tr>
                <th class="left">Concepto</th>
                <th class="right" style="width:170px;">Monto (₡)</th>
            </tr>
        </thead>

        <tbody>

            {{-- INGRESOS --}}
            <tr class="section-row"><td colspan="2">Ingresos</td></tr>

            <tr class="data-row">
                <td class="left">Total ingresos</td>
                <td class="right">{{ number_format($data['ingresos'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            {{-- GASTOS OPERATIVOS --}}
            <tr class="section-row"><td colspan="2">Gastos operativos</td></tr>

            <tr class="data-row">
                <td class="left">Gastos operativos</td>
                <td class="right">({{ number_format($data['gastos_operativos'] ?? 0, 2, ',', '.') }})</td>
            </tr>

            <tr class="total">
                <td class="left">Utilidad operativa</td>
                <td class="right">{{ number_format($data['utilidad_antes_depreciacion'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            {{-- AJUSTES --}}
            <tr class="section-row"><td colspan="2">Ajustes</td></tr>

            <tr class="data-row">
                <td class="left">Depreciación y amortización</td>
                <td class="right">({{ number_format($data['depreciacion'] ?? 0, 2, ',', '.') }})</td>
            </tr>

            <tr class="data-row">
                <td class="left">Otros gastos</td>
                <td class="right">({{ number_format($data['otros_gastos'] ?? 0, 2, ',', '.') }})</td>
            </tr>

            <tr class="total">
                <td class="left">Utilidad antes de impuestos</td>
                <td class="right">{{ number_format($data['utilidad_antes_impuestos'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="data-row">
                <td class="left">Impuesto sobre la renta</td>
                <td class="right">({{ number_format($data['impuestos'] ?? 0, 2, ',', '.') }})</td>
            </tr>

            <tr class="total">
                <td class="left">Utilidad neta del período</td>
                <td class="right">{{ number_format($data['utilidad_neta'] ?? 0, 2, ',', '.') }}</td>
            </tr>

        </tbody>
    </table>
@endsection