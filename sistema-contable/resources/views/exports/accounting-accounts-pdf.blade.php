@extends('pdf._layout')

@section('title', 'Plan de Cuentas')
@section('report_name', 'Plan de Cuentas')

@section('report_period')
    Total de cuentas: {{ $accounts->count() }}
@endsection

@push('styles')
<style>
    .accounts-table {
        table-layout: fixed;
        word-wrap: break-word;
    }

    .accounts-table th,
    .accounts-table td {
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
    @if(($accounts?->count() ?? 0) === 0)
        <div class="empty-state">No hay cuentas contables para exportar.</div>
    @else
        <table class="accounts-table">
            <thead>
                <tr>
                    <th class="left">Cliente</th>
                    <th class="left">Código</th>
                    <th class="left">Nombre</th>
                    <th class="left">Tipo</th>
                    <th class="left">Clasificación</th>
                    <th class="left">Naturaleza</th>
                    <th class="left">Estado</th>
                    <th class="left">Sección</th>
                    <th class="left">Código Padre</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                    <tr class="data-row">
                        <td class="left">{{ $account->customer?->name ?? '—' }}</td>
                        <td class="left">{{ $account->code ?? '—' }}</td>
                        <td class="left">{{ $account->name ?? '—' }}</td>
                        <td class="left">{{ $account->type ?? '—' }}</td>
                        <td class="left">{{ $account->classification ?? '—' }}</td>
                        <td class="left">{{ $account->normal_balance ?? '—' }}</td>
                        <td class="left">{{ $account->status ?? '—' }}</td>
                        <td class="left">{{ $account->report_section ?? '—' }}</td>
                        <td class="left">{{ $account->parent?->code ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection