<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plan de Cuentas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .meta { color: #4b5563; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; font-size: 10px; }
        .small { font-size: 9px; color: #374151; }
        .import-box { border: 1px solid #d1d5db; padding: 8px; background: #f9fafb; }
    </style>
</head>
<body>
    <h1>Plan de Cuentas</h1>
    <div class="meta">Generado: {{ now()->format('Y-m-d H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Codigo</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Clasificacion</th>
                <th>Naturaleza</th>
                <th>Estado</th>
                <th>Seccion</th>
                <th>Codigo Padre</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->customer?->name }}</td>
                    <td>{{ $account->code }}</td>
                    <td>{{ $account->name }}</td>
                    <td>{{ $account->type }}</td>
                    <td>{{ $account->classification }}</td>
                    <td>{{ $account->normal_balance }}</td>
                    <td>{{ $account->status }}</td>
                    <td>{{ $account->report_section }}</td>
                    <td>{{ $account->parent?->code }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="import-box">
        <div class="small">Lineas reutilizables para importacion PDF (formato delimitado por "|"):</div>
        <div class="small">customer_id|code|name|type|classification|report_section|normal_balance|parent_code|level|status</div>
        @foreach($accounts as $account)
            <div class="small">
                {{ $account->customer_id }}|{{ $account->code }}|{{ $account->name }}|{{ $account->type }}|{{ $account->classification }}|{{ $account->report_section }}|{{ $account->normal_balance }}|{{ $account->parent?->code }}|{{ $account->level }}|{{ $account->status }}
            </div>
        @endforeach
    </div>
</body>
</html>