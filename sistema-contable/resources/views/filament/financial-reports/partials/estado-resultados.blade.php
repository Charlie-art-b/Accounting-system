@php
    $totalIngresos = data_get($p, 'ingresos.total', 0);
    $totalGastos = data_get($p, 'gastos.total', 0);
    $utilidad = $p['utilidad_neta'] ?? 0;
    $margen = $p['margen_neto'] ?? 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-filament::card>
        <div class="text-sm text-gray-500">Total Ingresos</div>
        <div class="text-2xl font-bold">{{ number_format($totalIngresos, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Total Gastos</div>
        <div class="text-2xl font-bold">{{ number_format($totalGastos, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Utilidad Neta</div>
        <div class="text-2xl font-bold">{{ number_format($utilidad, 2) }}</div>
        <div class="text-xs text-gray-500 mt-1">Margen: {{ number_format($margen, 2) }}%</div>
    </x-filament::card>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-filament::section heading="Ingresos">
        @php($rows = data_get($p, 'ingresos.detalles', []))
        @if (!empty($rows))
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2 pr-4">Código</th>
                            <th class="py-2 pr-4">Cuenta</th>
                            <th class="py-2 pr-4">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr class="border-t">
                                <td class="py-2 pr-4">{{ $r['codigo'] ?? '' }}</td>
                                <td class="py-2 pr-4">{{ $r['nombre'] ?? '' }}</td>
                                <td class="py-2 pr-4">{{ number_format($r['monto'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-sm text-gray-500">Sin ingresos para el período.</div>
        @endif
    </x-filament::section>

    <x-filament::section heading="Gastos">
        @php($rows = data_get($p, 'gastos.detalles', []))
        @if (!empty($rows))
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500">
                        <tr>
                            <th class="py-2 pr-4">Código</th>
                            <th class="py-2 pr-4">Cuenta</th>
                            <th class="py-2 pr-4">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr class="border-t">
                                <td class="py-2 pr-4">{{ $r['codigo'] ?? '' }}</td>
                                <td class="py-2 pr-4">{{ $r['nombre'] ?? '' }}</td>
                                <td class="py-2 pr-4">{{ number_format($r['monto'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-sm text-gray-500">Sin gastos para el período.</div>
        @endif
    </x-filament::section>
</div>