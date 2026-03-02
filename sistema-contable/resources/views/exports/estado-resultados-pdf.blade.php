@extends('pdf._layout')

@section('title', 'Estado de Resultados')
@section('report_name', 'Estado de Resultados')

@php
    use Carbon\Carbon;

    // fechas vienen dentro del estadoResultados, pero también tienes $fechaInicio/$fechaFin
    $inicioRaw = $estadoResultados['fecha_inicio'] ?? $fechaInicio ?? now();
    $finRaw    = $estadoResultados['fecha_fin'] ?? $fechaFin ?? now();

    $inicio = Carbon::parse($inicioRaw)->locale('es')->translatedFormat('d \d\e F Y');
    $fin    = Carbon::parse($finRaw)->locale('es')->translatedFormat('d \d\e F Y');

    $ingresosDetalles = $estadoResultados['ingresos']['detalles'] ?? [];
    $ingresosTotal    = $estadoResultados['ingresos']['total'] ?? 0;

    $gastosDetalles = $estadoResultados['gastos']['detalles'] ?? [];
    $gastosTotal    = $estadoResultados['gastos']['total'] ?? 0;

    $utilidadBruta = $estadoResultados['utilidad_bruta'] ?? 0;
    $impuestos     = $estadoResultados['impuestos'] ?? 0;
    $utilidadNeta  = $estadoResultados['utilidad_neta'] ?? 0;
    $margenNeto    = $estadoResultados['margen_neto'] ?? 0;
@endphp

@section('report_period')
    Del {{ strtolower($inicio) }} al {{ strtolower($fin) }}
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
            {{-- INGRESOS --}}
            <tr class="section-row"><td colspan="2">Ingresos</td></tr>

            @foreach($ingresosDetalles as $row)
                <tr class="data-row">
                    <td class="left indent">{{ $row['nombre'] ?? '' }}</td>
                    <td class="right">{{ number_format($row['monto'] ?? 0, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="subtotal">
                <td class="left">Total ingresos</td>
                <td class="right">{{ number_format($ingresosTotal, 2, ',', '.') }}</td>
            </tr>

            {{-- GASTOS --}}
            <tr class="section-row"><td colspan="2">Gastos</td></tr>

            @foreach($gastosDetalles as $row)
                <tr class="data-row">
                    <td class="left indent">{{ $row['nombre'] ?? '' }}</td>
                    <td class="right">{{ number_format($row['monto'] ?? 0, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="subtotal">
                <td class="left">Total gastos</td>
                <td class="right">{{ number_format($gastosTotal, 2, ',', '.') }}</td>
            </tr>

            {{-- RESULTADOS --}}
            <tr class="total">
                <td class="left">Utilidad bruta</td>
                <td class="right">{{ number_format($utilidadBruta, 2, ',', '.') }}</td>
            </tr>

            <tr class="data-row">
                <td class="left">Impuestos</td>
                <td class="right">({{ number_format($impuestos, 2, ',', '.') }})</td>
            </tr>

            <tr class="total">
                <td class="left">Utilidad neta</td>
                <td class="right">{{ number_format($utilidadNeta, 2, ',', '.') }}</td>
            </tr>

            <tr class="data-row">
                <td class="left">Margen neto (%)</td>
                <td class="right">{{ number_format($margenNeto, 2, ',', '.') }}%</td>
            </tr>
        </tbody>
    </table>
@endsection