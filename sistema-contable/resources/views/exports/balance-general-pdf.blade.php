<!-- filepath: resources/views/exports/balance-general-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance General</title>
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
        .section-header {
            background-color: #e8e8e8;
            font-weight: bold;
            padding: 10px;
        }
        .subtotal {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .total {
            background-color: #d4d4d4;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .monto {
            text-align: right;
        }
        .validation {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>BALANCE GENERAL</h1>
    <div class="fecha">Al {{ \Carbon\Carbon::parse($fecha)->format('d de F de Y') }}</div>

    <table>
        <thead>
            <tr>
                <th>CÓDIGO</th>
                <th>CUENTA</th>
                <th class="monto">SALDO (₡)</th>
            </tr>
        </thead>
        <tbody>
            {{-- Activos Circulantes --}}
            <tr class="section-header">
                <td colspan="3">ACTIVOS CIRCULANTES</td>
            </tr>
            @forelse($data['detalles'] as $detalle)
                @if($detalle['tipo'] === 'Activo' && isset($detalle['es_circulante']) && $detalle['es_circulante'])
                    <tr>
                        <td>{{ $detalle['codigo'] }}</td>
                        <td>{{ $detalle['nombre'] }}</td>
                        <td class="monto">{{ number_format($detalle['saldo'], 2, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
            @endforelse
            <tr class="subtotal">
                <td colspan="2">Subtotal Activos Circulantes</td>
                <td class="monto">₡ {{ number_format($data['activos']['activos_circulantes'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            {{-- Activos No Circulantes --}}
            <tr class="section-header">
                <td colspan="3">ACTIVOS NO CIRCULANTES</td>
            </tr>
            @forelse($data['detalles'] as $detalle)
                @if($detalle['tipo'] === 'Activo' && isset($detalle['es_circulante']) && !$detalle['es_circulante'])
                    <tr>
                        <td>{{ $detalle['codigo'] }}</td>
                        <td>{{ $detalle['nombre'] }}</td>
                        <td class="monto">{{ number_format($detalle['saldo'], 2, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
            @endforelse
            <tr class="subtotal">
                <td colspan="2">Subtotal Activos No Circulantes</td>
                <td class="monto">₡ {{ number_format($data['activos']['activos_no_circulantes'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            {{-- Total Activos --}}
            <tr class="total">
                <td colspan="2">TOTAL ACTIVOS</td>
                <td class="monto">₡ {{ number_format($data['total_activos'], 2, ',', '.') }}</td>
            </tr>

            {{-- Pasivos Circulantes --}}
            <tr class="section-header">
                <td colspan="3">PASIVOS CIRCULANTES</td>
            </tr>
            @forelse($data['detalles'] as $detalle)
                @if($detalle['tipo'] === 'Pasivo' && isset($detalle['es_circulante']) && $detalle['es_circulante'])
                    <tr>
                        <td>{{ $detalle['codigo'] }}</td>
                        <td>{{ $detalle['nombre'] }}</td>
                        <td class="monto">{{ number_format($detalle['saldo'], 2, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
            @endforelse
            <tr class="subtotal">
                <td colspan="2">Subtotal Pasivos Circulantes</td>
                <td class="monto">₡ {{ number_format($data['pasivos']['pasivos_circulantes'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            {{-- Pasivos No Circulantes --}}
            <tr class="section-header">
                <td colspan="3">PASIVOS NO CIRCULANTES</td>
            </tr>
            @forelse($data['detalles'] as $detalle)
                @if($detalle['tipo'] === 'Pasivo' && isset($detalle['es_circulante']) && !$detalle['es_circulante'])
                    <tr>
                        <td>{{ $detalle['codigo'] }}</td>
                        <td>{{ $detalle['nombre'] }}</td>
                        <td class="monto">{{ number_format($detalle['saldo'], 2, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
            @endforelse
            <tr class="subtotal">
                <td colspan="2">Subtotal Pasivos No Circulantes</td>
                <td class="monto">₡ {{ number_format($data['pasivos']['pasivos_no_circulantes'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            {{-- Total Pasivos --}}
            <tr class="total">
                <td colspan="2">TOTAL PASIVOS</td>
                <td class="monto">₡ {{ number_format($data['pasivos']['total'], 2, ',', '.') }}</td>
            </tr>

            {{-- Patrimonio --}}
            <tr class="section-header">
                <td colspan="3">PATRIMONIO</td>
            </tr>
            @forelse($data['detalles'] as $detalle)
                @if($detalle['tipo'] === 'Patrimonio')
                    <tr>
                        <td>{{ $detalle['codigo'] }}</td>
                        <td>{{ $detalle['nombre'] }}</td>
                        <td class="monto">{{ number_format($detalle['saldo'], 2, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
            @endforelse
            <tr class="total">
                <td colspan="2">TOTAL PATRIMONIO</td>
                <td class="monto">₡ {{ number_format($data['patrimonio']['total'] ?? 0, 2, ',', '.') }}</td>
            </tr>

            {{-- Total Pasivos + Patrimonio --}}
            <tr class="total">
                <td colspan="2">TOTAL PASIVOS + PATRIMONIO</td>
                <td class="monto">₡ {{ number_format($data['total_pasivos_patrimonio'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="validation">
        @if($data['ecuacion_balanceada'] ?? false)
            <p class="success">✓ ECUACIÓN BALANCEADA</p>
            <p class="success">La ecuación contable se cumple: Activos = Pasivos + Patrimonio</p>
        @else
            <p class="error">✗ NO BALANCEADO</p>
            <p class="error">Diferencia: ₡{{ number_format($data['diferencia'] ?? 0, 2, ',', '.') }}</p>
        @endif
    </div>
</body>
</html>