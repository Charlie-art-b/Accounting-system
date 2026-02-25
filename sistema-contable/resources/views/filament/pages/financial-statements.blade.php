<x-filament::page>

    {{ $this->form }}

    {{-- Debug opcional --}}
    @if (!empty($debug))
        <x-filament::section heading="Debug (temporal)">
            <pre class="text-xs">{{ json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </x-filament::section>
    @endif 

    @if ($generated)

        <x-filament::section heading="Balance General">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <x-filament::card>
                    <div class="text-sm text-gray-500">Total Activos</div>
                    <div class="text-2xl font-bold">
                        {{ number_format($balance['total_activos'] ?? 0, 2) }}
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="text-sm text-gray-500">Total Pasivos</div>
                    <div class="text-2xl font-bold">
                        {{ number_format($balance['pasivos']['total'] ?? 0, 2) }}
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="text-sm text-gray-500">Total Patrimonio</div>
                    <div class="text-2xl font-bold">
                        {{ number_format($balance['patrimonio']['total'] ?? 0, 2) }}
                    </div>
                </x-filament::card>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-filament::card>
                    <h3 class="font-semibold mb-2">Activos</h3>
                    <p>Activos Circulantes: {{ number_format($balance['activos']['activos_circulantes'] ?? 0, 2) }}</p>
                    <p>Activos No Circulantes: {{ number_format($balance['activos']['activos_no_circulantes'] ?? 0, 2) }}</p>
                </x-filament::card>

                <x-filament::card>
                    <h3 class="font-semibold mb-2">Pasivos</h3>
                    <p>Pasivos Circulantes: {{ number_format($balance['pasivos']['pasivos_circulantes'] ?? 0, 2) }}</p>
                    <p>Pasivos No Circulantes: {{ number_format($balance['pasivos']['pasivos_no_circulantes'] ?? 0, 2) }}</p>
                </x-filament::card>
            </div>

            <div class="mt-6">
                <x-filament::card>
                    <h3 class="font-semibold mb-2">Patrimonio</h3>
                    <p>Capital: {{ number_format($balance['patrimonio']['capital'] ?? 0, 2) }}</p>
                    <p>Utilidad del Período: {{ number_format($balance['patrimonio']['utilidad_periodo'] ?? 0, 2) }}</p>
                </x-filament::card>
            </div>

        <x-filament::section heading="Estado de Resultados">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-filament::card>
            <div class="text-sm text-gray-500">Total Ingresos</div>
            <div class="text-2xl font-bold">
                {{ number_format($estadoResultados['ingresos']['total'] ?? 0, 2) }}
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Total Gastos</div>
            <div class="text-2xl font-bold">
                {{ number_format($estadoResultados['gastos']['total'] ?? 0, 2) }}
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Utilidad Neta</div>
            <div class="text-2xl font-bold">
                {{ number_format($estadoResultados['utilidad_neta'] ?? 0, 2) }}
            </div>
        </x-filament::card>
    </div>

    {{-- Detalle de gastos --}}
    @if (!empty($estadoResultados['gastos']['detalles']))
        <x-filament::card>
            <h3 class="font-semibold mb-3">Detalle de Gastos</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Código</th>
                            <th class="px-4 py-2 text-left">Cuenta</th>
                            <th class="px-4 py-2 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($estadoResultados['gastos']['detalles'] as $gasto)
                            <tr>
                                <td class="px-4 py-2">{{ $gasto['codigo'] }}</td>
                                <td class="px-4 py-2">{{ $gasto['nombre'] }}</td>
                                <td class="px-4 py-2 text-right font-medium">
                                    {{ number_format($gasto['monto'] ?? 0, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::card>
    @else
        <x-filament::card>
            <div class="text-sm text-gray-600">
                No hay gastos en el rango seleccionado.
            </div>
        </x-filament::card>
    @endif

</x-filament::section>

        </x-filament::section>

        @if (!empty($balance['detalles']))
            <x-filament::section heading="Detalle de Cuentas">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Código</th>
                                <th class="px-4 py-2 text-left">Cuenta</th>
                                <th class="px-4 py-2 text-left">Clasificación</th>
                                <th class="px-4 py-2 text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($balance['detalles'] as $cuenta)
                                <tr>
                                    <td class="px-4 py-2">{{ $cuenta['codigo'] }}</td>
                                    <td class="px-4 py-2">{{ $cuenta['nombre'] }}</td>
                                    <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $cuenta['clasificacion']) }}</td>
                                    <td class="px-4 py-2 text-right font-medium">{{ number_format($cuenta['saldo'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

    @endif

</x-filament::page>