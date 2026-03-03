<x-filament::page>

    {{-- ESTILOS SOLO PARA ESTA VISTA --}}
    <style>
        .report-wrap{
            max-width: 1200px;
            /*margin: 0 auto;*/
        }

        .report-panel{
            background: rgb(var(--primary-50));
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(2, 6, 23, .06);
        }
        
        @media (prefers-color-scheme: dark) {
            .report-panel{
                background: rgb(var(--gray-900));
            }
        }

        .report-panel__header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .report-title{
            display:flex;
            flex-direction:column;
            gap: 2px;
        }
        .report-title h2{
            margin:0;
            font-size: 16px;
            font-weight: 700;
            color: rgb(var(--gray-950));
        }
        @media (prefers-color-scheme: dark) {
            .report-title h2{
                color: rgb(var(--gray-50));
            }
        }
        .report-title p{
            margin:0;
            font-size: 12px;
            color: rgba(15, 23, 42, .60);
        }
        @media (prefers-color-scheme: dark) {
            .report-title p{
                color: rgb(var(--gray-400));
            }
        }

        .actions-bar{
            display:flex;
            flex-wrap: wrap;
            gap: 10px;
            padding-top: 12px;
            margin-top: 12px;
            border-top: 1px dashed rgba(15, 23, 42, .12);
        }
        @media (prefers-color-scheme: dark) {
            .actions-bar{
                border-color: rgb(var(--gray-700));
            }
        }

        .results-section{
            margin-top: 18px;
        }

        .cards-grid{
            display:grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        @media (max-width: 1024px){
            .cards-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px){
            .cards-grid{ grid-template-columns: 1fr; }
        }

        .stat-card{
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, .08);
            background: linear-gradient(180deg, rgba(248, 250, 252, 1) 0%, rgba(255, 255, 255, 1) 100%);
            padding: 14px 14px 12px;
            box-shadow: 0 10px 25px rgba(2, 6, 23, .05);
            position: relative;
            overflow: hidden;
        }
        @media (prefers-color-scheme: dark) {
            .stat-card{
                background: linear-gradient(180deg, rgb(var(--gray-800)) 0%, rgb(var(--gray-900)) 100%);
                border-color: rgb(var(--gray-700));
            }
        }
        .stat-card:before{
            content:"";
            position:absolute;
            inset: -40% -40% auto auto;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle at 30% 30%, rgba(59, 130, 246, .22), transparent 60%);
            transform: rotate(12deg);
            pointer-events:none;
        }

        .stat-top{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap: 10px;
            position: relative;
            z-index: 1;
        }

        .stat-label{
            font-size: 12px;
            font-weight: 600;
            color: rgba(15, 23, 42, .70);
            margin: 0;
        }
        @media (prefers-color-scheme: dark) {
            .stat-label{
                color: rgb(var(--gray-400));
            }
        }

        .stat-value{
            margin-top: 6px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
        }
        @media (prefers-color-scheme: dark) {
            .stat-value{
                color: rgb(var(--gray-50));
            }
        }

        .stat-sub{
            margin-top: 6px;
            font-size: 12px;
            color: rgba(15, 23, 42, .60);
        }
        @media (prefers-color-scheme: dark) {
            .stat-sub{
                color: rgb(var(--gray-400));
            }
        }

        .badge{
            display:inline-flex;
            align-items:center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid rgba(15, 23, 42, .10);
            background: rgba(255,255,255,.75);
            backdrop-filter: blur(6px);
            user-select:none;
            white-space: nowrap;
        }
        @media (prefers-color-scheme: dark) {
            .badge{
                background: rgba(0, 0, 0, .3);
                border-color: rgb(var(--gray-700));
                color: rgb(var(--gray-300));
            }
        }
        .badge--ok{ color: #166534; border-color: rgba(22, 101, 52, .25); background: rgba(34, 197, 94, .10); }
        .badge--bad{ color: #991b1b; border-color: rgba(153, 27, 27, .25); background: rgba(239, 68, 68, .10); }
        @media (prefers-color-scheme: dark) {
            .badge--ok{ color: #86efac; border-color: rgba(34, 197, 94, .4); background: rgba(34, 197, 94, .15); }
            .badge--bad{ color: #fca5a5; border-color: rgba(239, 68, 68, .4); background: rgba(239, 68, 68, .15); }
        }

        .table-wrap{
            margin-top: 16px;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 25px rgba(2, 6, 23, .05);
        }
        @media (prefers-color-scheme: dark) {
            .table-wrap{
                background: rgb(var(--gray-900));
                border-color: rgb(var(--gray-800));
            }
        }

        .nice-table{
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .nice-table thead th{
            position: sticky;
            top: 0;
            background: #f8fafc;
            color: rgba(15, 23, 42, .75);
            text-align: left;
            font-weight: 800;
            padding: 12px 12px;
            border-bottom: 1px solid rgba(15, 23, 42, .08);
        }
        @media (prefers-color-scheme: dark) {
            .nice-table thead th{
                background: rgb(var(--gray-800));
                color: rgb(var(--gray-300));
                border-color: rgb(var(--gray-700));
            }
        }
        .nice-table tbody td{
            padding: 10px 12px;
            border-bottom: 1px solid rgba(15, 23, 42, .06);
            color: rgba(15, 23, 42, .86);
            vertical-align: top;
        }
        @media (prefers-color-scheme: dark) {
            .nice-table tbody td{
                color: rgb(var(--gray-300));
                border-color: rgb(var(--gray-800));
            }
        }
        .nice-table tbody tr:nth-child(even){
            background: rgba(248, 250, 252, .55);
        }
        @media (prefers-color-scheme: dark) {
            .nice-table tbody tr:nth-child(even){
                background: rgba(0, 0, 0, .2);
            }
        }
        .nice-table tbody tr:hover{
            background: rgba(59, 130, 246, .06);
        }
        @media (prefers-color-scheme: dark) {
            .nice-table tbody tr:hover{
                background: rgba(59, 130, 246, .15);
            }
        }

        .td-right{ text-align:right; font-variant-numeric: tabular-nums; }
        .code-pill{
            display:inline-block;
            padding: 4px 8px;
            border-radius: 10px;
            background: rgba(15, 23, 42, .06);
            border: 1px solid rgba(15, 23, 42, .08);
            font-weight: 700;
            font-size: 12px;
        }
        @media (prefers-color-scheme: dark) {
            .code-pill{
                background: rgb(var(--gray-800));
                border-color: rgb(var(--gray-700));
                color: rgb(var(--gray-300));
            }
        }
    </style>

    <div class="report-wrap">

        {{-- PANEL FORM --}}
        <div class="report-panel">
            

            {{ $this->form }}

            <div class="actions-bar">
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
                    
                    icon="heroicon-o-arrow-down-tray"
                    color="gray"
                    :disabled="! $generated || ! $pdfUrl"
                >
                    Exportar PDF
                </x-filament::button>

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
            <div class="results-section">
                <x-filament::section heading="Resumen del Reporte">
                    <div class="cards-grid">

                        @if ($report_type === 'balance_general')
                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Total Activos</p>
                                    <span class="badge">📌 Balance</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['total_activos'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Suma de activos corrientes y no corrientes.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Total Pasivo + Patrimonio</p>
                                    <span class="badge">📌 Balance</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['total_pasivos_patrimonio'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Obligaciones + capital contable.</div>
                            </div>

                            @php($ok = (bool) ($result['ecuacion_balanceada'] ?? false))
                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Ecuación contable</p>
                                    <span class="badge {{ $ok ? 'badge--ok' : 'badge--bad' }}">
                                        {{ $ok ? '✅ Balanceado' : '⚠️ No balancea' }}
                                    </span>
                                </div>
                                <div class="stat-value">{{ $ok ? 'Sí' : 'No' }}</div>
                                <div class="stat-sub">Diferencia: <strong>{{ number_format($result['diferencia'] ?? 0, 2) }}</strong></div>
                            </div>
                        @endif

                        @if ($report_type === 'estado_resultados')
                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Total Ingresos</p>
                                    <span class="badge">💰 ER</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['ingresos']['total'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Ingresos del período.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Total Gastos</p>
                                    <span class="badge">🧾 ER</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['gastos']['total'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Gastos y costos del período.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Utilidad Neta</p>
                                    <span class="badge">📈 ER</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['utilidad_neta'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Margen: <strong>{{ number_format($result['margen_neto'] ?? 0, 2) }}%</strong></div>
                            </div>
                        @endif

                        @if ($report_type === 'balance_comprobacion')
                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Total Debe</p>
                                    <span class="badge">📚 BC</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['total_debe'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Sumatoria de débitos.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Total Haber</p>
                                    <span class="badge">📚 BC</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['total_haber'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Sumatoria de créditos.</div>
                            </div>

                            @php($ok = (bool) ($result['balanceado'] ?? false))
                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Cuadre</p>
                                    <span class="badge {{ $ok ? 'badge--ok' : 'badge--bad' }}">
                                        {{ $ok ? '✅ Balanceado' : '⚠️ No balancea' }}
                                    </span>
                                </div>
                                <div class="stat-value">{{ $ok ? 'Sí' : 'No' }}</div>
                                <div class="stat-sub">Diferencia: <strong>{{ number_format($result['diferencia'] ?? 0, 2) }}</strong></div>
                            </div>
                        @endif

                        @if ($report_type === 'flujo_efectivo')
                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Utilidad Neta</p>
                                    <span class="badge">💧 FE</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['utilidad_neta'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Base del flujo (según método).</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Flujo Operativo</p>
                                    <span class="badge">💧 FE</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['flujo_operativo'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Operación del período.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Efectivo Final</p>
                                    <span class="badge">💧 FE</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['efectivo_final'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Saldo final de caja/bancos.</div>
                            </div>
                        @endif

                        @if ($report_type === 'cambios_patrimonio')
                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Capital Inicial</p>
                                    <span class="badge">🏛️ CP</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['capital_inicial'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Patrimonio al inicio.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Utilidad del Período</p>
                                    <span class="badge">🏛️ CP</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['utilidad_periodo'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Resultado del período.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Patrimonio Final</p>
                                    <span class="badge">🏛️ CP</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['patrimonio_final'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Patrimonio al cierre.</div>
                            </div>
                        @endif

                        @if ($report_type === 'estado_resultados_integral')
                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Ingresos</p>
                                    <span class="badge">🧩 ERI</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['ingresos'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Ingresos del período.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Gastos Operativos</p>
                                    <span class="badge">🧩 ERI</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['gastos_operativos'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Gastos operativos.</div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-top">
                                    <p class="stat-label">Utilidad Neta</p>
                                    <span class="badge">🧩 ERI</span>
                                </div>
                                <div class="stat-value">{{ number_format($result['utilidad_neta'] ?? 0, 2) }}</div>
                                <div class="stat-sub">Resultado neto final.</div>
                            </div>
                        @endif

                    </div>

                    {{-- Tabla de detalles --}}
                    @if (!empty($result['detalles']))
                        <div class="table-wrap">
                            <div style="overflow:auto; max-height: 520px;">
                                <table class="nice-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 140px;">Código</th>
                                            <th>Cuenta</th>
                                            <th style="width: 220px;">Clasificación</th>
                                            <th style="width: 170px; text-align:right;">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($result['detalles'] as $row)
                                            <tr>
                                                <td><span class="code-pill">{{ $row['codigo'] ?? '' }}</span></td>
                                                <td>{{ $row['nombre'] ?? '' }}</td>
                                                <td>{{ $row['clasificacion'] ?? '' }}</td>
                                                <td class="td-right">{{ number_format($row['saldo'] ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                </x-filament::section>
            </div>
        @endif

    </div>
</x-filament::page>