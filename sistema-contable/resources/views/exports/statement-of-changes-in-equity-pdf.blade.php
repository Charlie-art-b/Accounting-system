<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estado de Cambios en el Patrimonio</title>

<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
}

.container { padding: 20px; }

.header {
    text-align: center;
    border-bottom: 1px solid #000;
    margin-bottom: 15px;
    padding-bottom: 8px;
}

.titulo {
    font-size: 36px;
    font-weight: bold;
    color: #1B1464;
}

.subtitulo {
    font-size: 20px;
    color: #2E3192;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 6px;
}

.left { text-align: left; }
.right { text-align: right; }

.section {
    font-weight: bold;
    padding-top: 10px;
}

.total {
    border-top: 2px solid #000;
    font-weight: bold;
}

.subtotal {
    border-top: 1px solid #000;
    font-weight: bold;
}
</style>
</head>

@php
use Carbon\Carbon;

$fechaActual = Carbon::parse($data['fecha'])
    ->locale('es')
    ->translatedFormat('d \d\e F Y');
@endphp

<body>
<div class="container">

<div class="header">
    <h1 class="titulo">CAHEN</h1>
    <h2 class="subtitulo">Servicios Contables</h2>
    <div><strong>{{ strtoupper($cliente->nombre) }}</strong></div>
    <div><strong>Cédula {{ strtoupper($cliente->identification) }}</strong></div>
    <div><strong>ESTADO DE CAMBIOS EN EL PATRIMONIO</strong></div>
    <div><strong>AL {{ strtolower($fechaActual) }}</strong></div>
</div>

<table>

<tr>
    <td class="left section">Capital Inicial</td>
    <td class="right section">{{ number_format($data['capital_inicial'],2,',','.') }}</td>
</tr>

<tr>
    <td class="left">Aportes del período</td>
    <td class="right">{{ number_format($data['aportes'],2,',','.') }}</td>
</tr>

<tr>
    <td class="left">Retiros del período</td>
    <td class="right">({{ number_format($data['retiros'],2,',','.') }})</td>
</tr>

<tr class="subtotal">
    <td class="left">Resultado del período</td>
    <td class="right">{{ number_format($data['utilidad_periodo'],2,',','.') }}</td>
</tr>

<tr class="total">
    <td class="left">Patrimonio Final</td>
    <td class="right">{{ number_format($data['patrimonio_final'],2,',','.') }}</td>
</tr>

<tr class="subtotal">
    <td class="left">Cambio Neto del Patrimonio</td>
    <td class="right">{{ number_format($data['cambio_neto'],2,',','.') }}</td>
</tr>

</table>

</div>
</body>
</html>