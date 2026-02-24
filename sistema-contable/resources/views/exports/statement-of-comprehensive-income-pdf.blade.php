<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estado de Resultados Integral</title>

<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
}

.container { padding: 20px; }

.header {
    text-align: center;
    margin-bottom: 20px;
}

.title {
    font-size: 16px;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 4px;
}

.left { text-align: left; }
.right { text-align: right; }

.total {
    border-top: 1px solid #000;
    font-weight: bold;
}
</style>
</head>

<body>
<div class="container">

<div class="header">
    <div class="title">{{ strtoupper($cliente->name) }}</div>
    <div>Cédula {{ $cliente->identification }}</div>
    <div><strong>ESTADO DE RESULTADOS INTEGRAL</strong></div>
    <div>DEL {{ $fechaInicio }} AL {{ $fechaFin }}</div>
</div>

<table>

<tr>
    <td class="left">TOTAL INGRESOS</td>
    <td class="right">{{ number_format($data['ingresos'],2,',','.') }}</td>
</tr>

<tr>
    <td class="left">GASTOS OPERATIVOS</td>
    <td class="right">({{ number_format($data['gastos_operativos'],2,',','.') }})</td>
</tr>

<tr class="total">
    <td class="left">UTILIDAD OPERATIVA</td>
    <td class="right">{{ number_format($data['utilidad_antes_depreciacion'],2,',','.') }}</td>
</tr>

<tr>
    <td class="left">DEPRECIACIÓN Y AMORTIZACIÓN</td>
    <td class="right">({{ number_format($data['depreciacion'],2,',','.') }})</td>
</tr>

<tr>
    <td class="left">OTROS GASTOS</td>
    <td class="right">({{ number_format($data['otros_gastos'],2,',','.') }})</td>
</tr>

<tr class="total">
    <td class="left">UTILIDAD ANTES DE IMPUESTOS</td>
    <td class="right">{{ number_format($data['utilidad_antes_impuestos'],2,',','.') }}</td>
</tr>

<tr>
    <td class="left">IMPUESTO SOBRE LA RENTA</td>
    <td class="right">({{ number_format($data['impuestos'],2,',','.') }})</td>
</tr>

<tr class="total">
    <td class="left">UTILIDAD NETA DEL PERÍODO</td>
    <td class="right">{{ number_format($data['utilidad_neta'],2,',','.') }}</td>
</tr>

</table>

</div>
</body>
</html>