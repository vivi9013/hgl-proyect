@extends('layouts.app')

@section('title', 'Devoluciones Terminadas')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">
                <i class="fa fa-check-circle text-success me-2"></i>Devoluciones Terminadas
            </h1>
            <p class="text-muted mb-0">Historial de devoluciones finalizadas</p>
        </div>
    </div>

    {{-- Subnavegación del Módulo --}}
    @include('inventario.devoluciones.partials.subnav', ['activo' => 'terminadas'])

    <hr class="my-4" style="border-top: 1.5px solid #e2e8f0; opacity: 1;">

    {{-- ── Buscador + Filtros + Botón Excel ── --}}
    <div class="row mb-4 align-items-end g-3">
        <div class="col-12">
            <form method="GET" action="{{ route('devoluciones.terminadas') }}" id="formBuscarTerminadas">
                <div class="row g-2 align-items-end">
                    {{-- Buscador por folio / área --}}
                    <div class="col-12 col-md-3 position-relative">
                        <label for="inputBuscarTerm" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-search me-1"></i>Buscar:
                        </label>
                        <div class="input-group" style="border: 1.5px solid #000; border-radius: 10px; overflow: hidden;">
                            <input type="text" name="buscar" id="inputBuscarTerm"
                                   class="form-control bg-light border-0"
                                   placeholder="Buscar por folio..."
                                   value="{{ $buscar }}"
                                   autocomplete="off"
                                   style="font-size: 0.9rem; box-shadow: none;">
                            @if($buscar)
                                <a href="{{ route('devoluciones.terminadas') }}" class="input-group-text bg-light border-0 text-decoration-none">
                                    <i class="fa fa-times text-danger"></i>
                                </a>
                            @endif
                            <button class="input-group-text bg-light border-0" type="submit">
                                <i class="fa fa-search text-dark"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Filtro por Área --}}
                    <div class="col-6 col-md-2">
                        <label for="filtro_area_term" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-building me-1"></i>Área:
                        </label>
                        <select name="id_area_abastecimiento" id="filtro_area_term"
                                class="form-select bg-light border-0"
                                style="font-size: 0.9rem;"
                                onchange="this.form.submit()">
                            <option value="">Todas las Áreas</option>
                            @foreach($areasAbastecimiento as $area)
                                <option value="{{ $area->id_area_abastecimiento }}"
                                    {{ $filtroArea == $area->id_area_abastecimiento ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro por Motivo --}}
                    <div class="col-6 col-md-2">
                        <label for="filtro_motivo_term" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-tag me-1"></i>Motivo:
                        </label>
                        <select name="id_motivo" id="filtro_motivo_term"
                                class="form-select bg-light border-0"
                                style="font-size: 0.9rem;"
                                onchange="this.form.submit()">
                            <option value="">Todos los Motivos</option>
                            @foreach($motivos as $motivo)
                                <option value="{{ $motivo->id_motivo }}"
                                    {{ $filtroMotivo == $motivo->id_motivo ? 'selected' : '' }}>
                                    {{ $motivo->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fecha Inicio --}}
                    <div class="col-6 col-md-2">
                        <label for="fecha_inicio_term" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Inicio:
                        </label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio_term"
                               class="form-control bg-light" value="{{ $fechaInit }}">
                    </div>

                    {{-- Fecha Fin --}}
                    <div class="col-6 col-md-2">
                        <label for="fecha_fin_term" class="form-label small fw-bold mb-1 text-dark">
                            <i class="fa fa-calendar me-1"></i>Fecha Fin:
                        </label>
                        <input type="date" name="fecha_fin" id="fecha_fin_term"
                               class="form-control bg-light" value="{{ $fechaFin }}">
                    </div>

                    {{-- Botones Aplicar / Limpiar + Excel --}}
                    <div class="col-12 col-md-1 d-flex gap-1 align-items-end justify-content-md-end">
                        <button type="submit" class="btn btn-dark btn-sm" title="Aplicar filtros">
                            <i class="fa fa-filter"></i>
                        </button>
                        @if($buscar || $fechaInit || $fechaFin || $filtroMotivo || $filtroArea)
                            <a href="{{ route('devoluciones.terminadas') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar todos los filtros">
                                <i class="fa fa-times"></i>
                            </a>
                        @endif
                        <button type="button"
                                class="btn btn-success btn-sm fw-bold px-3"
                                style="background: linear-gradient(135deg, #1a7a3c, #28a745); border: none; box-shadow: 0 3px 8px rgba(40,167,69,0.4); letter-spacing: 0.3px;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalExportarExcelTerminadas"
                                title="Exportar a Excel">
                            <i class="fa fa-file-excel-o me-1"></i> Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Tabla de Terminadas ── --}}
    <div class="row">
        <div class="col-xs-12">
            <div class="card shadow-sm border-0 bg-transparent">
                <div class="card-header bg-white border-0 pt-4 px-0 pb-2 d-flex align-items-center gap-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fa fa-list text-secondary me-2"></i>Historial de devoluciones terminadas
                    </h5>
                    <span class="rounded-pill px-3 py-1 fw-bold" style="background-color: #e9ecef; font-size: 0.78rem;">
                        <span style="color: #000;">{{ $devoluciones->total() }}</span>
                        <span style="color: #495057;">{{ $devoluciones->total() === 1 ? 'Registro' : 'Registros' }}</span>
                    </span>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                             <thead class="table-light text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Folio</th>
                                    <th>Área de Almacén</th>
                                    <th>Área Abastecimiento</th>
                                    <th>Motivo</th>
                                    <th>Fecha</th>
                                    <th class="text-center pe-4" style="width: 80px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devoluciones as $devolucion)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            {{ ($devoluciones->currentPage() - 1) * $devoluciones->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <span class="folio-badge" style="color: #15803d; background-color: #f0fdf4; border-color: #bbf7d0;">
                                                DEV-{{ str_pad($devolucion->id_devolucion, 5, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </td>
                                        <td>{{ $devolucion->areaAlmacen->nombre ?? '—' }}</td>
                                        <td>{{ $devolucion->areaAbastecimiento->nombre ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-white" style="font-size: 0.8rem;">
                                                {{ $devolucion->motivo->descripcion ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : '—' }}</td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('devoluciones.comprobante', $devolucion->id_devolucion) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary rounded-circle"
                                               title="Imprimir comprobante"
                                               style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0;">
                                                <i class="fa fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay devoluciones terminadas en el período seleccionado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($devoluciones->total() > 0)
                    <div class="card-footer bg-white border-0 py-3 px-0 d-flex justify-content-between align-items-center border-top mt-2">
                        <div class="text-muted small">
                            Mostrando {{ $devoluciones->firstItem() ?? 0 }} a {{ $devoluciones->lastItem() ?? 0 }} de {{ $devoluciones->total() }} devoluciones
                        </div>
                        <nav aria-label="Paginación de devoluciones terminadas">
                            {{ $devoluciones->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Modal: Exportar a Excel (reutiliza filtros activos de la vista terminadas) ── --}}
<div class="modal fade" id="modalExportarExcelTerminadas" tabindex="-1" aria-labelledby="modalExportarExcelTerminadasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-success text-white border-0 py-3 px-4 rounded-top-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-file-excel-o fs-5"></i>
                    <h5 class="modal-title fw-bold mb-0" id="modalExportarExcelTerminadasLabel">
                        Exportar Formato de Devolución
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('devoluciones.exportar_excel') }}" method="GET">
                {{-- Propaga el filtro de búsqueda activo --}}
                @if($buscar)
                    <input type="hidden" name="buscar" value="{{ $buscar }}">
                @endif
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Se exportarán las devoluciones terminadas con los filtros aplicados actualmente.
                        Las <strong>fechas son obligatorias</strong>.
                    </p>
                    <div class="row g-3">
                        {{-- Área / Departamento individual --}}
                        <div class="col-12">
                            <label for="excel_id_area_term" class="form-label fw-bold small">
                                Área / Departamento: <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <select name="id_area_abastecimiento" id="excel_id_area_term" class="form-select bg-light">
                                <option value="">Todas las Áreas</option>
                                @foreach($areasAbastecimiento as $area)
                                    <option value="{{ $area->id_area_abastecimiento }}"
                                        {{ $filtroArea == $area->id_area_abastecimiento ? 'selected' : '' }}>
                                        {{ $area->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Motivos con selección múltiple --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small mb-0">
                                    Motivos de Devolución: <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none me-2" style="font-size: 0.78rem;" id="btnMarcarTodosMotivos">
                                        Seleccionar todos
                                    </button>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" style="font-size: 0.78rem;" id="btnDesmarcarTodosMotivos">
                                        Desmarcar todos
                                    </button>
                                </div>
                            </div>
                            <div class="border rounded p-2 bg-white" style="max-height: 150px; overflow-y: auto;">
                                @foreach($motivos as $motivo)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input check-motivo-excel"
                                               type="checkbox"
                                               name="motivos[]"
                                               value="{{ $motivo->id_motivo }}"
                                               id="check_motivo_{{ $motivo->id_motivo }}"
                                               {{ empty($filtroMotivo) || $filtroMotivo == $motivo->id_motivo ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="check_motivo_{{ $motivo->id_motivo }}" style="cursor: pointer;">
                                            {{ $motivo->descripcion }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted" style="font-size: 0.74rem;">
                                Marca las razones que deseas incluir en el reporte de Excel.
                            </small>
                        </div>
                        {{-- Fechas --}}
                        <div class="col-6">
                            <label for="excel_fecha_inicio_term" class="form-label fw-bold small">
                                Fecha Inicio: <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="fecha_inicio"
                                   id="excel_fecha_inicio_term"
                                   class="form-control bg-light"
                                   value="{{ $fechaInit }}"
                                   required>
                        </div>
                        <div class="col-6">
                            <label for="excel_fecha_fin_term" class="form-label fw-bold small">
                                Fecha Fin: <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="fecha_fin"
                                   id="excel_fecha_fin_term"
                                   class="form-control bg-light"
                                   value="{{ $fechaFin }}"
                                   required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success text-white">
                        <i class="fa fa-download me-1"></i>Descargar Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/css/inventario/devoluciones/devoluciones.css', 'resources/js/inventario/devoluciones/devoluciones.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnMarcarMotivos = document.getElementById('btnMarcarTodosMotivos');
            const btnDesmarcarMotivos = document.getElementById('btnDesmarcarTodosMotivos');
            if (btnMarcarMotivos) {
                btnMarcarMotivos.addEventListener('click', function () {
                    document.querySelectorAll('.check-motivo-excel').forEach(cb => cb.checked = true);
                });
            }
            if (btnDesmarcarMotivos) {
                btnDesmarcarMotivos.addEventListener('click', function () {
                    document.querySelectorAll('.check-motivo-excel').forEach(cb => cb.checked = false);
                });
            }
        });
    </script>
@endpush
