<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte - Categoría de Módulos</title>
    <!-- Bootstrap CSS for print structures -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- FontAwesome for print-specific icons if any -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        body {
            background-color: #ffffff;
            font-family: 'Inter', sans-serif;
            color: #2d3748;
            padding: 0;
            margin: 0;
        }
        
        .report-header {
            border-bottom: 3px double #2b6cb0;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .logo-text {
            color: #2b6cb0;
            font-weight: 800;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .table th {
            background-color: #f7fafc !important;
            color: #2b6cb0 !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e0 !important;
        }
        
        .table td {
            font-size: 0.85rem;
            color: #4a5568;
        }

        .badge-status {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
        }
        
        .badge-active {
            background-color: #f6fedb;
            color: #48bb78;
            border: 1px solid #c6f6d5;
        }
        
        .badge-inactive {
            background-color: #fed7d7;
            color: #e53e3e;
            border: 1px solid #fed7d7;
        }

        .footer-info {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .table th {
                background-color: #f7fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container py-5">
        <!-- Barra de Acciones Superior (No se imprime) -->
        <div class="no-print d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded shadow-sm border">
            <span class="text-secondary fw-medium"><i class="fa fa-info-circle me-1 text-primary"></i> Presione Ctrl+P o Cmd+P si el diálogo de impresión no se abrió automáticamente.</span>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary btn-sm px-3 rounded-pill"><i class="fa fa-print me-1"></i> Imprimir</button>
                <button onclick="window.close()" class="btn btn-secondary btn-sm px-3 rounded-pill">Cerrar Ventana</button>
            </div>
        </div>

        <!-- Encabezado Oficial -->
        <div class="report-header d-flex justify-content-between align-items-end">
            <div>
                <h2 class="logo-text mb-1"><i class="fa fa-heartbeat me-2"></i>Hospital General de Linares</h2>
                <h6 class="text-secondary fw-semibold mb-0">Sistema de Gestión Hospitalaria (Estadías)</h6>
            </div>
            <div class="text-end">
                <div class="mb-1"><small class="text-secondary"><b>Fecha de emisión:</b> {{ date('d/m/Y') }}</small></div>
                <div><small class="text-secondary"><b>Hora de emisión:</b> {{ date('H:i:s') }}</small></div>
            </div>
        </div>

        <h4 class="fw-bold mb-4 text-center text-dark">LISTA COMPLETA DE CATEGORÍAS DE MÓDULOS</h4>

        <!-- Tabla Principal -->
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;" class="text-center">No</th>
                    <th>Categoría</th>
                    <th>Proyecto</th>
                    <th style="width: 150px;" class="text-center">Panel Abierto</th>
                    <th style="width: 120px;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categorias as $index => $cat)
                    <tr>
                        <td class="text-center fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td class="fw-bold text-dark">{{ $cat->categoria }}</td>
                        <td>{{ $cat->proyecto }}</td>
                        <td class="text-center text-uppercase fw-semibold" style="font-size: 0.8rem;">{{ $cat->colapsado }}</td>
                        <td class="text-center">
                            @if ($cat->activo == 1)
                                <span class="badge-status badge-active">Activo</span>
                            @else
                                <span class="badge-status badge-inactive">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No se encontraron categorías de módulos registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Firma e Información de Pie de Página -->
        <div class="footer-info d-flex justify-content-between align-items-center">
            <span>Reporte de categorías generado automáticamente por el sistema de estadías.</span>
            <span class="fw-medium text-dark">Página 1 de 1</span>
        </div>
    </div>
</body>
</html>
