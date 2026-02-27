<x-filament::page>

    {{-- FORM --}}
    <div class="space-y-4">
        {{ $this->form }}

        <div class="flex flex-wrap gap-2">
            <x-filament::button
                wire:click="generateReport"
                icon="heroicon-o-play"
            >
                Generar reporte
            </x-filament::button>

            @php($pdfUrl = $this->getPdfUrl())

            <x-filament::button
                tag="a"
                :href="$pdfUrl"
                target="_blank"
                icon="heroicon-o-arrow-down-tray"
                color="gray"
                :disabled="! $generated || ! $pdfUrl"
            >
                Exportar PDF
            </x-filament::button>

            {{-- Excel lo conectamos en el paso 4 --}}
            @php($excelUrl = $this->getExcelUrl())
            <x-filament::button
                tag="a"
                :href="$excelUrl"
                icon="heroicon-o-table-cells"
                color="gray"
                :disabled="! $generated || ! $excelUrl"
            >
                Exportar Excel
            </x-filament::button>
        </div>
    </div>

    {{-- RESULTADOS --}}
    @if ($generated)

        <x-filament::section class="mt-6" heading="Resultado">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                @if ($report_type === 'balance_general')
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Total Activos</div>
                        <div class="text-2xl font-bold">{{ number_format($result['total_activos'] ?? 0, 2) }}</div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-sm text-gray-500">Total Pasivo + Patrimonio</div>
                        <div class="text-2xl font-bold">{{ number_format($result['total_pasivos_patrimonio'] ?? 0, 2) }}</div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-sm text-gray-500">Balanceado</div>
                        <div class="text-2xl font-bold">
                            {{ ($result['ecuacion_balanceada'] ?? false) ? 'Sí' : 'No' }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Diferencia: {{ number_format($result['diferencia'] ?? 0, 2) }}
                        </div>
                    </x-filament::card>
                @endif

                @if ($report_type === 'estado_resultados')
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Total Ingresos</div>
                        <div class="text-2xl font-bold">{{ number_format($result['ingresos']['total'] ?? 0, 2) }}</div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-sm text-gray-500">Total Gastos</div>
                        <div class="text-2xl font-bold">{{ number_format($result['gastos']['total'] ?? 0, 2) }}</div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="text-sm text-gray-500">Utilidad Neta</div>
                        <div class="text-2xl font-bold">{{ number_format($result['utilidad_neta'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Margen: {{ number_format($result['margen_neto'] ?? 0, 2) }}%
                        </div>
                    </x-filament::card>
                @endif

                @if ($report_type === 'balance_comprobacion')
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Total Debe</div>
                        <div class="text-2xl font-bold">{{ number_format($result['total_debe'] ?? 0, 2) }}</div>
                    </x-filament::card>
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Total Haber</div>
                        <div class="text-2xl font-bold">{{ number_format($result['total_haber'] ?? 0, 2) }}</div>
                    </x-filament::card>
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Balanceado</div>
                        <div class="text-2xl font-bold">{{ ($result['balanceado'] ?? false) ? 'Sí' : 'No' }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Diferencia: {{ number_format($result['diferencia'] ?? 0, 2) }}
                        </div>
                    </x-filament::card>
                @endif

                @if ($report_type === 'flujo_efectivo')
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Utilidad Neta</div>
                        <div class="text-2xl font-bold">{{ number_format($result['utilidad_neta'] ?? 0, 2) }}</div>
                    </x-filament::card>
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Flujo Operativo</div>
                        <div class="text-2xl font-bold">{{ number_format($result['flujo_operativo'] ?? 0, 2) }}</div>
                    </x-filament::card>
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Efectivo Final</div>
                        <div class="text-2xl font-bold">{{ number_format($result['efectivo_final'] ?? 0, 2) }}</div>
                    </x-filament::card>
                @endif

                @if ($report_type === 'cambios_patrimonio')
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Capital Inicial</div>
                        <div class="text-2xl font-bold">{{ number_format($result['capital_inicial'] ?? 0, 2) }}</div>
                    </x-filament::card>
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Utilidad del Período</div>
                        <div class="text-2xl font-bold">{{ number_format($result['utilidad_periodo'] ?? 0, 2) }}</div>
                    </x-filament::card>
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Patrimonio Final</div>
                        <div class="text-2xl font-bold">{{ number_format($result['patrimonio_final'] ?? 0, 2) }}</div>
                    </x-filament::card>
                @endif

                @if ($report_type === 'estado_resultados_integral')
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Ingresos</div>
                        <div class="text-2xl font-bold">{{ number_format($result['ingresos'] ?? 0, 2) }}</div>
                    </x-filament::card>
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Gastos Operativos</div>
                        <div class="text-2xl font-bold">{{ number_format($result['gastos_operativos'] ?? 0, 2) }}</div>
                    </x-filament::card>
                    <x-filament::card>
                        <div class="text-sm text-gray-500">Utilidad Neta</div>
                        <div class="text-2xl font-bold">{{ number_format($result['utilidad_neta'] ?? 0, 2) }}</div>
                    </x-filament::card>
                @endif

            </div>

            {{-- Tabla simple de detalles (si existe) --}}
            @if (!empty($result['detalles']))
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Código</th>
                                <th class="py-2 pr-4">Cuenta</th>
                                <th class="py-2 pr-4">Clasificación</th>
                                <th class="py-2 pr-4">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result['detalles'] as $row)
                                <tr class="border-t">
                                    <td class="py-2 pr-4">{{ $row['codigo'] ?? '' }}</td>
                                    <td class="py-2 pr-4">{{ $row['nombre'] ?? '' }}</td>
                                    <td class="py-2 pr-4">{{ $row['clasificacion'] ?? '' }}</td>
                                    <td class="py-2 pr-4">{{ number_format($row['saldo'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </x-filament::section>

    @endif

</x-filament::page>