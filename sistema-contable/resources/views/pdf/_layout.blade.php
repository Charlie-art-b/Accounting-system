<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Reporte')</title>

    <style>
        @page { margin: 28px 32px 42px 32px; }

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color:#111;
        }

        .container{ width:100%; }

        /* ===== Header ===== */
        .report-header{
            border-bottom: 2px solid #1B1464;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .brand-row{
            width:100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .brand-left{ width:55%; vertical-align: top; }
        .brand-right{ width:45%; vertical-align: top; text-align: right; }

        .titulo{
            font-size: 26px;
            font-weight: 800;
            color:#1B1464;
            margin:0;
            letter-spacing: 2px;
            line-height: 1.1;
        }

        .subtitulo{
            font-size: 12px;
            color:#2E3192;
            margin:2px 0 0 0;
        }

        .meta{
            font-size: 10px;
            line-height: 1.35;
            color:#333;
        }
        .meta strong{ color:#111; }

        .report-title{
            text-align:center;
            margin-top: 8px;
        }
        .report-title h2{
            margin:0;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
        }
        .report-title .period{
            margin-top: 2px;
            font-size: 10px;
            color:#444;
        }

        /* ===== Table ===== */
        table{
            width:100%;
            border-collapse: collapse;
        }

        thead th{
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
            background: #f2f3f5;
            border-top: 1px solid #cfcfd4;
            border-bottom: 1px solid #cfcfd4;
            padding: 7px 6px;
        }

        tbody td{
            padding: 5px 6px;
            border-bottom: 1px solid #ececf0;
        }

        .left{ text-align:left; }
        .center{ text-align:center; }
        .right{ text-align:right; }
        .muted{ color:#555; }

        tbody tr.data-row:nth-child(even) td{
            background: #fafafa;
        }

        .section-row td{
            background:#e9ecf7;
            border-top: 1px solid #c9d0f2;
            border-bottom: 1px solid #c9d0f2;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 7px 6px;
            color:#1B1464;
        }

        .subsection-row td{
            background:#f6f7fb;
            border-bottom: 1px solid #dde1f3;
            font-weight: 800;
            text-transform: uppercase;
            color:#333;
            padding: 6px 6px;
        }

        .indent{ padding-left: 18px; }

        .subtotal td{
            background:#fbfbff;
            font-weight: 800;
            border-top: 1px solid #b8b8c6;
            border-bottom: 1px solid #b8b8c6;
        }

        .total td{
            background:#f2f3f8;
            font-weight: 900;
            border-top: 2px solid #111;
            border-bottom: 2px solid #111;
        }

        /* ===== Footer ===== */
        .footer{
            position: fixed;
            bottom: 10px;
            left: 32px;
            right: 32px;
            font-size: 9px;
            color:#666;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }
        .footer .leftf{ float:left; }
        .footer .rightf{ float:right; }
        .clearfix{ clear:both; }

        .mb-8{ margin-bottom: 8px; }
        .mb-12{ margin-bottom: 12px; }
    </style>

    @stack('styles')
</head>

<body>
@php
    use Carbon\Carbon;

    // Defaults si no los pasan desde el view
    $empresa = $empresa ?? 'CAHEN';
    $slogan  = $slogan ?? 'Servicios Contables';
    $moneda  = $moneda ?? 'CRC';

    // Fecha/hora de generación
    $generatedAt = Carbon::now()->locale('es')->translatedFormat('d/m/Y H:i');
@endphp

<div class="container">

    <!-- HEADER -->
    <div class="report-header">
        <table class="brand-row">
            <tr>
                <td class="brand-left">
                   @php
                    $logoPath = public_path('images/logo.png');
                    $logoBase64 = null;

                    if (file_exists($logoPath)) {
                        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $dataImg = file_get_contents($logoPath);
                        $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
                    }
                    @endphp

                    @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="height:120px; width:120px; margin-bottom:6px;">
                    @endif
                </td>
                <td class="brand-right">
                    <div class="meta">
                        @isset($cliente)
                            <div><strong>Cliente:</strong> {{ strtoupper($cliente->nombre ?? $cliente->name ?? '—') }}</div>
                            <div><strong>Identificación:</strong> {{ strtoupper($cliente->identification) }}</div>
                        @endisset
                        <div><strong>Moneda:</strong> {{ $moneda }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="report-title">
            <h2>@yield('report_name', 'Reporte')</h2>
            <div class="period">
                @yield('report_period', '')
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    @yield('content')

    <!-- FOOTER -->
    <div class="footer">
        <div class="leftf">Generado el {{ $generatedAt }}</div>
        <div class="clearfix"></div>
    </div>

</div>
</body>
</html>