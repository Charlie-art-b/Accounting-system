<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Situación Financiera</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }

        .header strong {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 5px;
        }

        thead th {
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .left {
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .section {
            font-weight: bold;
            padding-top: 8px;
        }

        .subtotal {
            border-top: 1px solid #000;
            font-weight: bold;
        }

        .total {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
        }

        .indent {
            padding-left: 20px;
        }
    </style>
</head>

<body>
<div class="container">

    <!-- ENCABEZADO -->
    <div class="header">
        <h1>TRANSPORTES Y SERVICIOS PEREZ Y ORTIZ DEL ATLANTICO S.A.</h1>
        <strong>Cédula 3-101-752653</strong>
        <strong>ESTADO DE SITUACIÓN FINANCIERA</strong>
        <strong>AL 31 DE ENERO DEL 2026</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th class="left">Descripción</th>
                <th class="center">Notas</th>
                <th class="right">31-ene-26</th>
                <th class="right">31-dic-25</th>
            </tr>
        </thead>

        <tbody>

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

        <!-- ACTIVOS -->
        <tr><td colspan="4" class="section">ACTIVOS</td></tr>
        <tr><td colspan="4" class="section">ACTIVOS CORRIENTES</td></tr>

        @foreach($activosCorrientesList as $detalle)
            <tr>
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'],2,',','.') }}</td>
                <td class="right">{{ number_format($detalle['saldo_anterior'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $activosCorrientes += $detalle['saldo']; @endphp
        @endforeach

        <tr class="subtotal">
            <td class="left">TOTAL ACTIVOS CORRIENTES</td>
            <td></td>
            <td class="right">{{ number_format($activosCorrientes,2,',','.') }}</td>
            <td></td>
        </tr>

        <tr><td colspan="4" class="section">ACTIVOS NO CORRIENTES</td></tr>

        @foreach($activosNoCorrientesList as $detalle)
            <tr>
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'],2,',','.') }}</td>
                <td class="right">{{ number_format($detalle['saldo_anterior'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $activosNoCorrientes += $detalle['saldo']; @endphp
        @endforeach

        <tr class="subtotal">
            <td class="left">TOTAL ACTIVOS NO CORRIENTES</td>
            <td></td>
            <td class="right">{{ number_format($activosNoCorrientes,2,',','.') }}</td>
            <td></td>
        </tr>

        <tr class="total">
            <td class="left">TOTAL ACTIVOS</td>
            <td></td>
            <td class="right">{{ number_format($data['total_activos'],2,',','.') }}</td>
            <td></td>
        </tr>

        <!-- PASIVOS -->
        <tr><td colspan="4" class="section">PASIVOS</td></tr>
        <tr><td colspan="4" class="section">PASIVOS CORRIENTES</td></tr>

        @foreach($pasivosCorrientesList as $detalle)
            <tr>
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'],2,',','.') }}</td>
                <td class="right">{{ number_format($detalle['saldo_anterior'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $pasivosCorrientes += $detalle['saldo']; @endphp
        @endforeach

        <tr class="subtotal">
            <td class="left">TOTAL PASIVOS CORRIENTES</td>
            <td></td>
            <td class="right">{{ number_format($pasivosCorrientes,2,',','.') }}</td>
            <td></td>
        </tr>

        <tr><td colspan="4" class="section">PASIVOS NO CORRIENTES</td></tr>

        @foreach($pasivosNoCorrientesList as $detalle)
            <tr>
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'],2,',','.') }}</td>
                <td class="right">{{ number_format($detalle['saldo_anterior'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $pasivosNoCorrientes += $detalle['saldo']; @endphp
        @endforeach

        <tr class="subtotal">
            <td class="left">TOTAL PASIVOS NO CORRIENTES</td>
            <td></td>
            <td class="right">{{ number_format($pasivosNoCorrientes,2,',','.') }}</td>
            <td></td>
        </tr>

        <tr class="total">
            <td class="left">TOTAL PASIVOS</td>
            <td></td>
            <td class="right">{{ number_format($data['pasivos']['total'],2,',','.') }}</td>
            <td></td>
        </tr>

        <!-- PATRIMONIO -->
        <tr><td colspan="4" class="section">PATRIMONIO</td></tr>

        @foreach($patrimonioList as $detalle)
            <tr>
                <td class="left indent">{{ strtoupper($detalle['nombre']) }}</td>
                <td class="center">{{ $detalle['nota'] ?? '' }}</td>
                <td class="right">{{ number_format($detalle['saldo'],2,',','.') }}</td>
                <td class="right">{{ number_format($detalle['saldo_anterior'] ?? 0,2,',','.') }}</td>
            </tr>
            @php $totalPatrimonio += $detalle['saldo']; @endphp
        @endforeach

        <tr class="total">
            <td class="left">TOTAL PASIVO + PATRIMONIO</td>
            <td></td>
            <td class="right">{{ number_format($data['total_pasivos_patrimonio'],2,',','.') }}</td>
            <td></td>
        </tr>

        </tbody>
    </table>

</div>
</body>
</html>