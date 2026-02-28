@php
    $capitalInicial = $p['capital_inicial'] ?? 0;
    $aportes = $p['aportes'] ?? 0;
    $retiros = $p['retiros'] ?? 0;
    $utilidad = $p['utilidad_periodo'] ?? 0;
    $patrimonioFinal = $p['patrimonio_final'] ?? 0;
    $cambioNeto = $p['cambio_neto'] ?? 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-filament::card>
        <div class="text-sm text-gray-500">Capital Inicial</div>
        <div class="text-2xl font-bold">{{ number_format($capitalInicial, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Patrimonio Final</div>
        <div class="text-2xl font-bold">{{ number_format($patrimonioFinal, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Cambio Neto</div>
        <div class="text-2xl font-bold">{{ number_format($cambioNeto, 2) }}</div>
    </x-filament::card>
</div>

<x-filament::section heading="Detalle de cambios en el patrimonio">
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
                    <td class="py-2 pr-4">Aportes</td>
                    <td class="py-2 pr-4">{{ number_format($aportes, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Retiros</td>
                    <td class="py-2 pr-4">{{ number_format($retiros, 2) }}</td>
                </tr>
                <tr class="border-t">
                    <td class="py-2 pr-4">Utilidad del período</td>
                    <td class="py-2 pr-4">{{ number_format($utilidad, 2) }}</td>
                </tr>
                <tr class="border-t font-semibold">
                    <td class="py-2 pr-4">Patrimonio final</td>
                    <td class="py-2 pr-4">{{ number_format($patrimonioFinal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</x-filament::section>