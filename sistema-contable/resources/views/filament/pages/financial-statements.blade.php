<x-filament::page>

    <div class="fs-report">

        {{ $this->form }}

        @if (!empty($debug))
            <x-filament::section heading="Debug (temporal)" class="mt-6">
                <pre class="text-xs bg-gray-100 p-4 rounded">
{{ json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                </pre>
            </x-filament::section>
        @endif

        @if ($generated)

            {{-- =========================
                BALANCE GENERAL
            ========================== --}}
            <x-filament::section heading="Balance General" class="mt-6">

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
                        <h3 class="font-semibold mb-3">Activos</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Activos Circulantes</span>
                                <span class="font-medium">
                                    {{ number_format($balance['activos']['activos_circulantes'] ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Activos No Circulantes</span>
                                <span class="font-medium">
                                    {{ number_format($balance['activos']['activos_no_circulantes'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <h3 class="font-semibold mb-3">Pasivos</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Pasivos Circulantes</span>
                                <span class="font-medium">
                                    {{ number_format($balance['pasivos']['pasivos_circulantes'] ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Pasivos No Circulantes</span>
                                <span class="font-medium">
                                    {{ number_format($balance['pasivos']['pasivos_no_circulantes'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </x-filament::card>
                </div>

                <div class="mt-6">
                    <x-filament::card>
                        <h3 class="font-semibold mb-3">Patrimonio</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Capital</span>
                                <span class="font-medium">
                                    {{ number_format($balance['patrimonio']['capital'] ?? 0, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Utilidad del Período</span>
                                <span class="font-medium">
                                    {{ number_format($balance['patrimonio']['utilidad_periodo'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>
                    </x-filament::card>
                </div>

            </x-filament::section>


            {{-- =========================
                ESTADO DE RESULTADOS
            ========================== --}}
            <x-filament::section heading="Estado de Resultados" class="mt-6">

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

                {{-- Detalle de Gastos (mejorado con inline style, igual que la otra tabla) --}}
                <x-filament::card>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">Detalle de Gastos</h3>

                        @if (!empty($estadoResultados['gastos']['detalles']))
                            <span style="font-size:12px; padding:6px 10px; border-radius:999px; background:#f3f4f6; color:#374151; font-weight:600;">
                                {{ count($estadoResultados['gastos']['detalles']) }} cuentas
                            </span>
                        @endif
                    </div>

                    @if (!empty($estadoResultados['gastos']['detalles']))
                        <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:12px;">
                            <table style="width:100%; border-collapse:separate !important; border-spacing:18px 10px !important; font-size:14px;">
                                <thead style="background:#f9fafb;">
                                    <tr>
                                        <th style="padding:10px 14px !important; text-align:left; white-space:nowrap; font-weight:700; color:#6b7280;">
                                            Código
                                        </th>
                                        <th style="padding:10px 14px !important; text-align:left; font-weight:700; color:#6b7280;">
                                            Cuenta
                                        </th>
                                        <th style="padding:10px 14px !important; text-align:right; white-space:nowrap; font-weight:700; color:#6b7280;">
                                            Monto
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($estadoResultados['gastos']['detalles'] as $i => $gasto)
                                        <tr style="{{ $i % 2 === 0 ? 'background:#ffffff;' : 'background:#fcfcfd;' }}">
                                            <td style="padding:10px 14px !important; white-space:nowrap; font-weight:600;">
                                                {{ $gasto['codigo'] }}
                                            </td>
                                            <td style="padding:10px 14px !important;">
                                                {{ $gasto['nombre'] }}
                                            </td>
                                            <td style="padding:10px 14px !important; text-align:right; white-space:nowrap; font-weight:700;">
                                                {{ number_format($gasto['monto'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-sm text-gray-600">
                            No hay gastos en el rango seleccionado.
                        </div>
                    @endif
                </x-filament::card>

            </x-filament::section>


            {{-- =========================
                DETALLE DE CUENTAS
            ========================== --}}
            @if (!empty($balance['detalles']))
                <x-filament::section heading="Detalle de Cuentas" class="mt-6">
                    <x-filament::card>
                        <div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:12px;">
                            <table style="width:100%; border-collapse:separate !important; border-spacing:24px 10px !important; font-size:14px;">
                                <thead style="background:#f9fafb;">
                                    <tr>
                                        <th style="padding:10px 18px !important; text-align:left; white-space:nowrap; font-weight:700; color:#6b7280;">
                                            Código
                                        </th>
                                        <th style="padding:10px 18px !important; text-align:left; font-weight:700; color:#6b7280;">
                                            Cuenta
                                        </th>
                                        <th style="padding:10px 18px !important; text-align:left; white-space:nowrap; font-weight:700; color:#6b7280;">
                                            Clasificación
                                        </th>
                                        <th style="padding:10px 18px !important; text-align:right; white-space:nowrap; font-weight:700; color:#6b7280;">
                                            Saldo
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($balance['detalles'] as $i => $cuenta)
                                        <tr style="{{ $i % 2 === 0 ? 'background:#ffffff;' : 'background:#fcfcfd;' }}">
                                            <td style="padding:10px 18px !important; white-space:nowrap; font-weight:600;">
                                                {{ $cuenta['codigo'] }}
                                            </td>
                                            <td style="padding:10px 18px !important;">
                                                {{ $cuenta['nombre'] }}
                                            </td>
                                            <td style="padding:10px 18px !important; white-space:nowrap; text-transform:capitalize;">
                                                {{ str_replace('_', ' ', $cuenta['clasificacion']) }}
                                            </td>
                                            <td style="padding:10px 18px !important; text-align:right; white-space:nowrap; font-weight:700;">
                                                {{ number_format($cuenta['saldo'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-filament::card>
                </x-filament::section>
            @endif

        @endif

    </div>

</x-filament::page>