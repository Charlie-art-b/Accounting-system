<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { color: #4b5563; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 5px; text-align: left; }
        th { background: #f3f4f6; font-size: 9px; }
        .small { font-size: 8px; color: #374151; }
        .import-box { border: 1px solid #d1d5db; padding: 6px; background: #f9fafb; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Generado: {{ now()->format('Y-m-d H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                @foreach($displayFields ?? $fields as $fieldLabel)
                    <th>{{ $fieldLabel }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    @foreach($fields as $field)
                        @php $value = data_get($record, $field); @endphp
                        <td>
                            @if($value instanceof \DateTimeInterface)
                                {{ $value->format('Y-m-d') }}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="import-box">
        <div class="small">Plantilla para importacion PDF delimitada por "|"</div>
        <div class="small">{{ implode('|', $displayFields ?? $fields) }}</div>
        @foreach($records as $record)
            <div class="small">
                @php $line = []; @endphp
                @foreach($fields as $field)
                    @php
                        $value = data_get($record, $field);
                        if ($value instanceof \DateTimeInterface) {
                            $value = $value->format('Y-m-d');
                        }
                        $line[] = str_replace('|', ' ', (string) $value);
                    @endphp
                @endforeach
                {{ implode('|', $line) }}
            </div>
        @endforeach
    </div>
</body>
</html>

