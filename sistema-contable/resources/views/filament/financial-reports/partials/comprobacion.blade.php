@php
    $debe = $p['total_debe'] ?? 0;
    $haber = $p['total_haber'] ?? 0;
    $balanceado = $p['balanceado'] ?? false;
    $diff = $p['diferencia'] ?? 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-filament::card>
        <div class="text-sm text-gray-500">Total Debe</div>
        <div class="text-2xl font-bold">{{ number_format($debe, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Total Haber</div>
        <div class="text-2xl font-bold">{{ number_format($haber, 2) }}</div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-sm text-gray-500">Balanceado</div>
        <div class="text-2xl font-bold">{{ $balanceado ? 'Sí' : 'No' }}</div>
        <div class="text-xs text-gray-500 mt-1">Diferencia: {{ number_format($diff, 2) }}</div>
    </x-filament::card>
</div>

<x-filament::section heading="Cuentas">
    @php($rows = $p['cuentas'] ?? [])
    @if (!empty($rows))
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr>
                        <th class="py-2 pr-4">Código</th>
                        <th class="py-2 pr-4">Cuenta</th>
                        <th class="py-2 pr-4">Debe</th>
                        <th class="py-2 pr-4">Haber</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr class="border-t">
                            <td class="py-2 pr-4">{{ $r['codigo'] ?? '' }}</td>
                            <td class="py-2 pr-4">{{ $r['nombre'] ?? '' }}</td>
                            <td class="py-2 pr-4">{{ number_format($r['debe'] ?? 0, 2) }}</td>
                            <td class="py-2 pr-4">{{ number_format($r['haber'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-sm text-gray-500">No hay movimientos para el período.</div>
    @endif
</x-filament::section>