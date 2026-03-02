@extends('pdf._layout')

@section('title', $title)
@section('report_name', $title)

@section('report_period')
    Total de registros: {{ $records->count() }}
@endsection

@push('styles')
<style>
    .generic-table {
        table-layout: fixed;
        word-wrap: break-word;
    }

    .generic-table th,
    .generic-table td {
        font-size: 9px;
    }

    .empty-state {
        padding: 16px;
        border: 1px solid #d9dbe7;
        background: #f8f9fc;
        color: #4b5563;
        text-align: center;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
    @if(($records?->count() ?? 0) === 0)
        <div class="empty-state">No hay registros para exportar.</div>
    @else
        <table class="generic-table">
            <thead>
                <tr>
                    @foreach($displayFields ?? $fields as $fieldLabel)
                        <th class="left">{{ $fieldLabel }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                    <tr class="data-row">
                        @foreach($fields as $field)
                            @php $value = data_get($record, $field); @endphp
                            <td class="left">
                                @if($value instanceof \DateTimeInterface)
                                    {{ $value->format('d/m/Y') }}
                                @else
                                    {{ $value !== null && $value !== '' ? $value : '—' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection

