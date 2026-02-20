<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estado de Resultados</title>
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
        .period {
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
        .section-header {
            background-color: #e8e8e8;
            font-weight: bold;
        }
        .subtotal {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .total {
            background-color: #d4d4d4;
            font-weight: bold;
        }
        .amount {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>ESTADO DE RESULTADOS</h1>
    <div class="period">
        Del {{ \Carbon\Carbon::parse($data['start_date'] ?? now())->format('d/m/Y') }} 
        al {{ \Carbon\Carbon::parse($data['end_date'] ?? now())->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>CONCEPTO</th>
                <th class="amount">MONTO (₡)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header">
                <td>INGRESOS</td>
                <td></td>
            </tr>
            @if(isset($data['revenues']['details']) && is_array($data['revenues']['details']))
                @foreach($data['revenues']['details'] as $revenue)
                    <tr>
                        <td>&nbsp;&nbsp;{{ $revenue['name'] }}</td>
                        <td class="amount">{{ number_format($revenue['amount'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            <tr class="subtotal">
                <td>Total Ingresos</td>
                <td class="amount">{{ number_format($data['revenues']['total'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="section-header">
                <td>GASTOS</td>
                <td></td>
            </tr>
            @if(isset($data['expenses']['details']) && is_array($data['expenses']['details']))
                @foreach($data['expenses']['details'] as $expense)
                    <tr>
                        <td>&nbsp;&nbsp;{{ $expense['name'] }}</td>
                        <td class="amount">{{ number_format($expense['amount'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            <tr class="subtotal">
                <td>Total Gastos</td>
                <td class="amount">{{ number_format($data['expenses']['total'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr class="total">
                <td>UTILIDAD BRUTA</td>
                <td class="amount">{{ number_format($data['gross_profit'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr>
                <td>Impuestos (22%)</td>
                <td class="amount">({{ number_format($data['taxes'] ?? 0, 2, ',', '.') }})</td>
            </tr>

            <tr class="total">
                <td>UTILIDAD NETA</td>
                <td class="amount">{{ number_format($data['net_income'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            <tr>
                <td>Margen Neto (%)</td>
                <td class="amount">{{ number_format($data['net_margin'] ?? 0, 2, ',', '.') }}%</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
