@php
    $utilidad = $p['utilidad_neta'] ?? 0;
    $variacion = $p['variacion_capital_trabajo'] ?? 0;
    $operativo = $p['flujo_operativo'] ?? 0;
    $inversion = $p['flujo_inversion'] ?? 0;
    $financiamiento = $p['flujo_financiamiento'] ?? 0;
    $neto = $p['flujo_neto'] ?? 0;
    $efectivoFinal = $p['efectivo_final'] ?? 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    <x-filament::card>
        <div class="text-sm text-gray-500">Utilidad Neta</div>
        <div class="text-2xl font-bold">{{ number_format($utilidad, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Flujo Operativo</div>
        <div class="text-2xl font-bold">{{ number_format($operativo, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Flujo Neto</div>
        <div class="text-2xl font-bold">{{ number_format($neto, 2) }}</div>
    </x-filament::card>

</div>

<x-filament::section heading="Detalle del flujo">

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
                    <td class="py-2 pr-4">Variación Capital de Trabajo</td>
                    <td class="py-2 pr-4">{{ number_format($variacion, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Flujo de Inversión</td>
                    <td class="py-2 pr-4">{{ number_format($inversion, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Flujo de Financiamiento</td>
                    <td class="py-2 pr-4">{{ number_format($financiamiento, 2) }}</td>
                </tr>
                <tr class="border-t font-semibold">
                    <td class="py-2 pr-4">Efectivo Final</td>
                    <td class="py-2 pr-4">{{ number_format($efectivoFinal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</x-filament::section>