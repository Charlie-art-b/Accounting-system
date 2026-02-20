<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ratios Financieros</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 14px;
            margin-top: 30px;
            margin-bottom: 10px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
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
        .category-header {
            background-color: #e8e8e8;
            font-weight: bold;
            padding: 10px;
        }
        .value-cell {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>ANÁLISIS DE RATIOS FINANCIEROS</h1>

    <h2>RATIOS DE LIQUIDEZ</h2>
    <table>
        <thead>
            <tr>
                <th>Ratio</th>
                <th class="value-cell">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Razón Corriente</td>
                <td class="value-cell">{{ number_format($data['liquidity']['current_ratio'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>RATIOS DE SOLVENCIA</h2>
    <table>
        <thead>
            <tr>
                <th>Ratio</th>
                <th class="value-cell">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Razón de Deuda</td>
                <td class="value-cell">{{ number_format($data['solvency']['debt_ratio'] ?? 0, 4, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>RATIOS DE RENTABILIDAD</h2>
    <table>
        <thead>
            <tr>
                <th>Ratio</th>
                <th class="value-cell">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ROA (Retorno sobre Activos) %</td>
                <td class="value-cell">{{ number_format($data['profitability']['roa'] ?? 0, 2, ',', '.') }}%</td>
            </tr>
            <tr>
                <td>ROE (Retorno sobre Patrimonio) %</td>
                <td class="value-cell">{{ number_format($data['profitability']['roe'] ?? 0, 2, ',', '.') }}%</td>
            </tr>
            <tr>
                <td>Margen Neto %</td>
                <td class="value-cell">{{ number_format($data['profitability']['net_margin'] ?? 0, 2, ',', '.') }}%</td>
            </tr>
        </tbody>
    </table>

    <h2>RATIOS DE EFICIENCIA</h2>
    <table>
        <thead>
            <tr>
                <th>Ratio</th>
                <th class="value-cell">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Rotación de Activos</td>
                <td class="value-cell">{{ number_format($data['efficiency']['asset_turnover'] ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
