@php
    $record = $getRecord();
    $type = $record->report_type;
    $p = $record->payload ?? [];
@endphp

<div class="space-y-6">

    @switch($type)

        @case('balance_general')
            @include('filament.financial-reports.partials.balance-general', ['p' => $p])
            @break

        @case('estado_resultados')
            @include('filament.financial-reports.partials.estado-resultados', ['p' => $p])
            @break

        @case('balance_comprobacion')
            @include('filament.financial-reports.partials.balance-comprobacion', ['p' => $p])
            @break

        @case('flujo_efectivo')
            @include('filament.financial-reports.partials.flujo-efectivo', ['p' => $p])
            @break

        @case('cambios_patrimonio')
            @include('filament.financial-reports.partials.cambios-patrimonio', ['p' => $p])
            @break

        @case('estado_resultados_integral')
            @include('filament.financial-reports.partials.estado-resultados-integral', ['p' => $p])
            @break

        @default
            <x-filament::section heading="Detalle">
                <div class="text-sm text-gray-500">Tipo de reporte no soportado: {{ $type }}</div>
            </x-filament::section>

    @endswitch

</div>