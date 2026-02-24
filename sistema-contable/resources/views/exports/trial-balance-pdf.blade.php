<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Balance de Comprobación</title>

<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
}

.container { width: 100%; padding: 20px; }

.titulo {
    font-size: 38px;
    font-weight: bold;
    color: #1B1464;
    margin: 0;
    letter-spacing: 3px;
}

.subtitulo {
    font-size: 22px;
    color: #2E3192;
    margin: 0;
}

.header {
    text-align: center;
    border-bottom: 1px solid #000;
    margin-bottom: 15px;
    padding-bottom: 8px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td { padding: 5px; }

thead th {
    border-bottom: 1px solid #000;
}

.left { text-align: left; }
.right { text-align: right; }
.center { text-align: center; }

.total {
    border-top: 2px solid #000;
    font-weight: bold;
}
</style>
</head>

@php
use Carbon\Carbon;

$fechaActual = Carbon::parse($fechaFin)
    ->locale('es')
    ->translatedFormat('d \d\e F Y');
@endphp

<body>
<div class="container">

<div class="header">
    <h1 class="titulo">CAHEN</h1>
    <h2 class="subtitulo">Servicios Contables</h2>
    <strong>{{ strtoupper($cliente->nombre) }}</strong>
    <strong>Cédula {{ strtoupper($cliente->identification) }}</strong>
    <strong>BALANCE DE COMPROBACIÓN</strong>
    <strong>AL {{ strtolower($fechaActual) }}</strong>
</div>

<table>
<thead>
<tr>
    <th class="left">Cuenta</th>
    <th class="right">Débito</th>
    <th class="right">Crédito</th>
</tr>
</thead>
<tbody>
@foreach($data['cuentas'] ?? [] as $cuenta)
    <tr>
        <td>{{ $cuenta['codigo'] }}</td>
        <td>{{ $cuenta['nombre'] }}</td>
        <td>{{ $cuenta['clasificacion'] }}</td>
        <td style="text-align:right;">
            {{ number_format($cuenta['debe'], 2) }}
        </td>
        <td style="text-align:right;">
            {{ number_format($cuenta['haber'], 2) }}
        </td>
    </tr>
@endforeach
</tbody>
</table>

</div>
</body>
</html>