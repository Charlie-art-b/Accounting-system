<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estado de Flujos de Efectivo</title>

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
    font-size: 38px;
    font-weight: bold;
    color: #1B1464;
}

.subtitulo {
    font-size: 22px;
    color: #2E3192;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td { padding: 5px; }

.left { text-align: left; }
.right { text-align: right; }

.section {
    font-weight: bold;
    padding-top: 8px;
}

.total {
    border-top: 2px solid #000;
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
    <div><strong>ESTADO DE FLUJOS DE EFECTIVO</strong></div>
    <div><strong>AL {{ strtolower($fechaActual) }}</strong></div>
</div>

<table>

<tr>
    <td colspan="2" class="section">ACTIVIDADES DE OPERACIÓN</td>
</tr>

<tr>
    <td class="left">Utilidad Neta</td>
    <td class="right">{{ number_format($data['utilidad_neta'],2,',','.') }}</td>
</tr>

<tr>
    <td class="left">Variación Capital de Trabajo</td>
    <td class="right">{{ number_format($data['variacion_capital_trabajo'],2,',','.') }}</td>
</tr>

<tr class="total">
    <td class="left">Flujo Neto de Actividades de Operación</td>
    <td class="right">{{ number_format($data['flujo_operativo'],2,',','.') }}</td>
</tr>


<tr>
    <td colspan="2" class="section">ACTIVIDADES DE INVERSIÓN</td>
</tr>

<tr>
    <td class="left">Flujo de Inversión</td>
    <td class="right">{{ number_format($data['flujo_inversion'],2,',','.') }}</td>
</tr>


<tr>
    <td colspan="2" class="section">ACTIVIDADES DE FINANCIAMIENTO</td>
</tr>

<tr>
    <td class="left">Flujo de Financiamiento</td>
    <td class="right">{{ number_format($data['flujo_financiamiento'],2,',','.') }}</td>
</tr>


<tr class="total">
    <td class="left">FLUJO NETO DEL PERÍODO</td>
    <td class="right">{{ number_format($data['flujo_neto'],2,',','.') }}</td>
</tr>

<tr class="total">
    <td class="left">EFECTIVO FINAL</td>
    <td class="right">{{ number_format($data['efectivo_final'],2,',','.') }}</td>
</tr>

</table>

</div>
</body>
</html>