@extends('pdf._layout')

@section('title', 'Estado de Situación Financiera')
@section('report_name', 'Estado de Situación Financiera')

@php
    use Carbon\Carbon;
    $fechaCorte = Carbon::parse($fechaFin)->locale('es')->translatedFormat('d \d\e F Y');
@endphp

@section('report_period')
    Al {{ strtolower($fechaCorte) }}
@endsection

@section('content')
@php
    $activosCorrientes = 0;
    $activosNoCorrientes = 0;
    $pasivosCorrientes = 0;
    $pasivosNoCorrientes = 0;
    $totalPatrimonio = 0;

    $activosCorrientesList = collect($data['detalles'])->filter(fn($d) => $d['tipo']=='Activo' && $d['clasificacion']=='activo_corriente');
    $activosNoCorrientesList = collect($data['detalles'])->filter(fn($d) => $d['tipo']=='Activo' && $d['clasificacion']=='activo_no_corriente');
    $pasivosCorrientesList = collect($data['detalles'])->filter(fn($d) => $d['tipo']=='Pasivo' && $d['clasificacion']=='pasivo_corriente');
    $pasivosNoCorrientesList = collect($data['detalles'])->filter(fn($d) => $d['tipo']=='Pasivo' && $d['clasificacion']=='pasivo_no_corriente');
    $patrimonioList = collect($data['detalles'])->filter(fn($d) => $d['tipo']=='Patrimonio');
@endphp

<table>
    <thead>
        <tr>
            <th class="left">Descripción</th>
            <th class="center" style="width:70px;">Notas</th>
            <th class="right" style="width:140px;">Monto</th>
        </tr>
    </thead>

    <tbody>
        <tr class="section-row"><td colspan="3">Activos</td></tr>

        <tr class="subsection-row"><td colspan="3">Activos corrientes</td></tr>
        @foreach($activosCorrientesList as $detalle)
            <tr class="data-row">
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center muted">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $activosCorrientes += (float)($detalle['saldo'] ?? 0); @endphp
        @endforeach

        <tr class="subtotal">
            <td class="left">Total activos corrientes</td>
            <td></td>
            <td class="right">{{ number_format($activosCorrientes,2,',','.') }}</td>
        </tr>

        <tr class="subsection-row"><td colspan="3">Activos no corrientes</td></tr>
        @foreach($activosNoCorrientesList as $detalle)
            <tr class="data-row">
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center muted">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $activosNoCorrientes += (float)($detalle['saldo'] ?? 0); @endphp
        @endforeach

        <tr class="subtotal">
            <td class="left">Total activos no corrientes</td>
            <td></td>
            <td class="right">{{ number_format($activosNoCorrientes,2,',','.') }}</td>
        </tr>

        <tr class="total">
            <td class="left">Total activos</td>
            <td></td>
            <td class="right">{{ number_format($data['total_activos'] ?? ($activosCorrientes + $activosNoCorrientes),2,',','.') }}</td>
        </tr>

        <tr class="section-row"><td colspan="3">Pasivos</td></tr>

        <tr class="subsection-row"><td colspan="3">Pasivos corrientes</td></tr>
        @foreach($pasivosCorrientesList as $detalle)
            <tr class="data-row">
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center muted">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $pasivosCorrientes += (float)($detalle['saldo'] ?? 0); @endphp
        @endforeach

        <tr class="subtotal">
            <td class="left">Total pasivos corrientes</td>
            <td></td>
            <td class="right">{{ number_format($pasivosCorrientes,2,',','.') }}</td>
        </tr>

        <tr class="subsection-row"><td colspan="3">Pasivos no corrientes</td></tr>
        @foreach($pasivosNoCorrientesList as $detalle)
            <tr class="data-row">
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center muted">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $pasivosNoCorrientes += (float)($detalle['saldo'] ?? 0); @endphp
        @endforeach

        <tr class="subtotal">
            <td class="left">Total pasivos no corrientes</td>
            <td></td>
            <td class="right">{{ number_format($pasivosNoCorrientes,2,',','.') }}</td>
        </tr>

        <tr class="total">
            <td class="left">Total pasivos</td>
            <td></td>
            <td class="right">{{ number_format($data['pasivos']['total'] ?? ($pasivosCorrientes + $pasivosNoCorrientes),2,',','.') }}</td>
        </tr>

        <tr class="section-row"><td colspan="3">Patrimonio</td></tr>

        @foreach($patrimonioList as $detalle)
            <tr class="data-row">
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center muted">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $totalPatrimonio += (float)($detalle['saldo'] ?? 0); @endphp
        @endforeach

        <tr class="total">
            <td class="left">Total pasivo + patrimonio</td>
            <td></td>
            <td class="right">{{ number_format($data['total_pasivos_patrimonio'] ?? (($pasivosCorrientes + $pasivosNoCorrientes) + $totalPatrimonio),2,',','.') }}</td>
        </tr>

    </tbody>
</table>
@endsection