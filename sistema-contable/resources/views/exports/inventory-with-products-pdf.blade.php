@extends('pdf._layout')

@section('title', 'Inventario')
@section('report_name', 'Detalle de Inventario')

@section('report_period')
    Inventario: {{ $inventory->name }}
@endsection

@push('styles')
<style>
    .info-section {
        margin-bottom: 20px;
        padding: 12px;
        background: #f8f9fc;
        border: 1px solid #d9dbe7;
    }

    .info-section .label {
        font-weight: 700;
        color: #4b5563;
        display: inline-block;
        width: 120px;
    }

    .info-section .value {
        color: #111;
    }

    .section-title {
        font-size: 12px;
        font-weight: 700;
        color: #1B1464;
        margin: 20px 0 10px 0;
        padding-bottom: 5px;
        border-bottom: 2px solid #1B1464;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .products-table th,
    .products-table td {
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

    .stock-positive {
        color: #059669;
        font-weight: 600;
    }

    .stock-zero {
        color: #9ca3af;
    }
</style>
@endpush

@section('content')
    {{-- Información del Inventario --}}
    <div class="info-section">
        <div style="margin-bottom: 8px;">
            <span class="label">Cliente:</span>
            <span class="value">{{ $inventory->customer->name ?? '—' }}</span>
        </div>
        <div style="margin-bottom: 8px;">
            <span class="label">Nombre del Inventario:</span>
            <span class="value">{{ $inventory->name }}</span>
        </div>
        <div>
            <span class="label">Fecha de creación:</span>
            <span class="value">{{ $inventory->created_at ? $inventory->created_at->format('d/m/Y H:i') : '—' }}</span>
        </div>
    </div>

    {{-- Productos del Inventario --}}
    <div class="section-title">Productos</div>

    @if($inventory->inventoryProducts->count() === 0)
        <div class="empty-state">Este inventario no tiene productos registrados.</div>
    @else
        <table class="products-table">
            <thead>
                <tr>
                    <th class="left">Producto</th>
                    <th class="right">Stock Inicial</th>
                    <th class="right">Entradas</th>
                    <th class="right">Salidas</th>
                    <th class="right">Existencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventory->inventoryProducts as $inventoryProduct)
                    @php
                        $existence = $inventoryProduct->stock_initial + $inventoryProduct->entries - $inventoryProduct->exits;
                    @endphp
                    <tr class="data-row">
                        <td class="left">{{ $inventoryProduct->product->name ?? '—' }}</td>
                        <td class="right">{{ number_format($inventoryProduct->stock_initial, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($inventoryProduct->entries, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($inventoryProduct->exits, 0, ',', '.') }}</td>
                        <td class="right">
                            <span class="{{ $existence > 0 ? 'stock-positive' : 'stock-zero' }}">
                                {{ number_format($existence, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid #1B1464;">
                    <td class="right" style="font-weight: 700; padding-top: 8px;">TOTALES:</td>
                    <td class="right" style="font-weight: 700; padding-top: 8px;">
                        {{ number_format($inventory->inventoryProducts->sum('stock_initial'), 0, ',', '.') }}
                    </td>
                    <td class="right" style="font-weight: 700; padding-top: 8px;">
                        {{ number_format($inventory->inventoryProducts->sum('entries'), 0, ',', '.') }}
                    </td>
                    <td class="right" style="font-weight: 700; padding-top: 8px;">
                        {{ number_format($inventory->inventoryProducts->sum('exits'), 0, ',', '.') }}
                    </td>
                    <td class="right" style="font-weight: 700; padding-top: 8px;">
                        @php
                            $totalExistence = $inventory->inventoryProducts->sum(function($item) {
                                return $item->stock_initial + $item->entries - $item->exits;
                            });
                        @endphp
                        <span class="{{ $totalExistence > 0 ? 'stock-positive' : 'stock-zero' }}">
                            {{ number_format($totalExistence, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif
@endsection
