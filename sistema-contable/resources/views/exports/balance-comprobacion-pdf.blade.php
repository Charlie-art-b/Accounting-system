<!-- filepath: resources/views/exports/balance-comprobacion-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance de Comprobación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .fecha {
            text-align: center;
            font-size: 12px;
            margin-bottom: 20px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        .total {
            background-color: #d4d4d4;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .monto {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>BALANCE DE COMPROBACIÓN</h1>
    <div class="fecha">Al {{ \Carbon\Carbon::parse($data['fecha'] ?? now())->format('d de F de Y') }}</div>

    <table>
        <thead>
            <tr>
                <th>CÓDIGO</th>
                <th>CUENTA</th>
                <th class="monto">DÉBITO (₡)</th>
                <th class="monto">CRÉDITO (₡)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['cuentas'] as $cuenta)
                <tr>
                    <td>{{ $cuenta['codigo'] }}</td>
                    <td>{{ $cuenta['nombre'] }}</td>
                    <td class="monto">{{ number_format($cuenta['debe'] ?? 0, 2, ',', '.') }}</td>
                    <td class="monto">{{ number_format($cuenta['haber'] ?? 0, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">No hay datos</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="2">TOTALES</td>
                <td class="monto">₡ {{ number_format($data['total_debe'] ?? 0, 2, ',', '.') }}</td>
                <td class="monto">₡ {{ number_format($data['total_haber'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: center; font-size: 12px;">
        @if(($data['total_debe'] ?? 0) === ($data['total_haber'] ?? 0))
            <p style="color: green;">✓ BALANCE CORRECTO</p>
            <p style="color: green;">Débitos = Créditos</p>
        @else
            <p style="color: red;">✗ BALANCE INCORRECTO</p>
            <p style="color: red;">Diferencia: ₡{{ number_format(abs(($data['total_debe'] ?? 0) - ($data['total_haber'] ?? 0)), 2, ',', '.') }}</p>
        @endif
    </div>
</body>
</html>