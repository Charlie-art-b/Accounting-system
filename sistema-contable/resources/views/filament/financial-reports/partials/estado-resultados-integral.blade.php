@php
    $ingresos = $p['ingresos'] ?? 0;
    $gastosOperativos = $p['gastos_operativos'] ?? 0;
    $depreciacion = $p['depreciacion'] ?? 0;
    $otrosGastos = $p['otros_gastos'] ?? 0;

    $uAntesDep = $p['utilidad_antes_depreciacion'] ?? 0;
    $uAntesImp = $p['utilidad_antes_impuestos'] ?? 0;
    $impuestos = $p['impuestos'] ?? 0;
    $uNeta = $p['utilidad_neta'] ?? 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <x-filament::card>
        <div class="text-sm text-gray-500">Ingresos</div>
        <div class="text-2xl font-bold">{{ number_format($ingresos, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Gastos Operativos</div>
        <div class="text-2xl font-bold">{{ number_format($gastosOperativos, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Utilidad antes Impuestos</div>
        <div class="text-2xl font-bold">{{ number_format($uAntesImp, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Utilidad Neta</div>
        <div class="text-2xl font-bold">{{ number_format($uNeta, 2) }}</div>
    </x-filament::card>
</div>

<x-filament::section heading="Detalle">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-gray-500">
                <tr>
                    <th class="py-2 pr-4">Concepto</th>
                    <th class="py-2 pr-4">Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t">
                    <td class="py-2 pr-4">Ingresos</td>
                    <td class="py-2 pr-4">{{ number_format($ingresos, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Gastos operativos</td>
                    <td class="py-2 pr-4">{{ number_format($gastosOperativos, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Utilidad antes depreciación</td>
                    <td class="py-2 pr-4">{{ number_format($uAntesDep, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Depreciación</td>
                    <td class="py-2 pr-4">{{ number_format($depreciacion, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Otros gastos</td>
                    <td class="py-2 pr-4">{{ number_format($otrosGastos, 2) }}</td>
                </tr>
                <tr class="border-t font-semibold">
                    <td class="py-2 pr-4">Utilidad antes impuestos</td>
                    <td class="py-2 pr-4">{{ number_format($uAntesImp, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Impuestos</td>
                    <td class="py-2 pr-4">{{ number_format($impuestos, 2) }}</td>
                </tr>
                <tr class="border-t font-semibold">
                    <td class="py-2 pr-4">Utilidad neta</td>
                    <td class="py-2 pr-4">{{ number_format($uNeta, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</x-filament::section>