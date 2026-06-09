<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Reporte - Sistema de Estadías')</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            font-size: 12px;
            line-height: 1.4;
        }

        /* ── Barra de acciones (no se imprime) ── */
        .no-print {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }

        .btn-print {
            background: #2b6cb0;
            color: #fff;
            border: 1px solid #1a365d;
            padding: 6px 16px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            transition: background 0.15s ease-in-out;
        }

        .btn-print:hover {
            background: #23527c;
        }

        .btn-close-win {
            background: #fff;
            color: #333;
            border: 1px solid #ccc;
            padding: 6px 16px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background 0.15s ease-in-out;
        }

        .btn-close-win:hover {
            background: #e6e6e6;
        }

        /* ── Contenedor de la página (ezpdf-like layout) ── */
        .page {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
        }

        /* Imagen del encabezado: full ancho */
        .header-img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Línea divisoria */
        .header-divider {
            border: 0;
            border-top: 1.5px solid #000;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        /* Título del reporte */
        .report-title {
            text-align: center;
            font-size: 12px;
            margin: 0 0 15px 0;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* ── Tablas estilo ezTable ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table thead tr {
            background-color: #808080;
            color: #fff;
        }

        table thead th {
            padding: 6px 8px;
            border: 1.5px solid #000;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        table thead th.center {
            text-align: center;
        }

        /* Filas alternas */
        table tbody tr:nth-child(even) {
            background-color: #e0e0e0;
        }

        table tbody td {
            padding: 5px 8px;
            border: 1.5px solid #000;
            vertical-align: top;
            color: #111;
        }

        table tbody td.num {
            text-align: center;
            font-weight: bold;
            width: 50px;
        }

        table tbody td.center {
            text-align: center;
        }

        /* ── Pie de página (ezpdf metadata) ── */
        .footer-info {
            margin-top: 15px;
            font-size: 11px;
            color: #111;
            line-height: 1.5;
        }

        .footer-info p {
            margin: 0;
        }

        /* ── Ocultar headers y footers del navegador ── */
        @page {
            margin: 0;
        }

        /* ── Medios de impresión ── */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .page {
                max-width: 100%;
                margin: 0;
                padding: 12mm 18mm 18mm 20mm;
            }

            table thead tr {
                background-color: #808080 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table tbody tr:nth-child(even) {
                background-color: #e0e0e0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @stack('styles')
    </style>
</head>
<body onload="window.print()">

    {{-- ── Barra de acciones (no visible al imprimir) ── --}}
    <div class="no-print">
        <span style="color:#555; font-weight: 500;">
            ℹ️ Presione <kbd>Ctrl+P</kbd> / <kbd>Cmd+P</kbd> si el diálogo no se abrió automáticamente.
        </span>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button class="btn-print" onclick="window.print()">🖨 Imprimir</button>
            @section('extra_actions')
                <button class="btn-close-win" onclick="window.close()">✕ Cerrar</button>
            @show
        </div>
    </div>

    {{-- ── Página ── --}}
    <div class="page">

        {{-- Encabezado oficial dual --}}
        <img class="header-img"
             src="{{ asset('images/encabezado.jpg') }}"
             alt="Hospital General de Linares — Secretaría de Salud Nuevo León">

        <hr class="header-divider">

        {{-- Título del reporte --}}
        <p class="report-title">
            <strong>@yield('report_title')</strong>
        </p>

        {{-- Alertas, filtros o advertencias --}}
        @yield('report_subheader')

        {{-- Contenido dinámico (generalmente la tabla) --}}
        @yield('content')

        {{-- Pie de página uniforme --}}
        <div class="footer-info">
            <p><strong>Fecha:</strong> {{ date('d/m/Y') }}</p>
            <p><strong>Hora:</strong> {{ date('H:i:s') }}</p>
        </div>

    </div>

</body>
</html>
