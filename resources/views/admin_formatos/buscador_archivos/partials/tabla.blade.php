@forelse ($archivos as $index => $archivo)
    @php
        // Sanitizar y verificar existencia física idéntico al sistema legacy
        $carpetaSanitizada = trim($archivo->categoria->categoria);
        $carpetaSanitizada = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'],
            $carpetaSanitizada
        );

        $nombreSanitizado = trim($archivo->nombre);
        $nombreSanitizado = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'],
            $nombreSanitizado
        );
        $nombreSanitizado .= '.pdf';

        $rutaCompleta1 = storage_path("app/formats/{$carpetaSanitizada}/{$nombreSanitizado}");
        
        $existe = file_exists($rutaCompleta1);
    @endphp
    <tr>
        <td class="ps-4 fw-medium text-secondary">
            {{ ($archivos->currentPage() - 1) * $archivos->perPage() + $loop->iteration }}
        </td>
        <td>
            <div class="fw-bold text-dark fs-6">{{ $archivo->nombre }}</div>
        </td>
        <td>
            <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill">{{ $archivo->categoria->categoria }}</span>
        </td>
        <td>
            <span class="text-secondary font-size-sm">{{ $archivo->descripcion_archivo ?: 'Sin descripción.' }}</span>
        </td>
        <td class="text-center">
            <span class="fw-bold text-dark">{{ $archivo->version_archivo ?: '1' }}</span>
        </td>
        <td class="text-center pe-4">
            @if ($existe)
                <a href="{{ route('busca_archivos.descargar', $archivo->id_archivo) }}" class="btn btn-sm btn-primary-gradient px-3.5 py-1.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-1.5">
                    <i class="fa fa-download"></i> <span>Descargar</span>
                </a>
            @else
                <span class="fa-stack text-secondary opacity-50" title="Archivo físico no disponible en el servidor">
                    <i class="fa fa-download fa-stack-1x"></i>
                    <i class="fa fa-ban fa-stack-2x text-danger"></i>
                </span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5">
            <div class="py-4">
                <i class="fa fa-folder-open-o fa-3x mb-3 text-secondary opacity-40"></i>
                <p class="mb-0 fw-medium text-secondary">No se encontraron formatos en esta categoría.</p>
            </div>
        </td>
    </tr>
@endforelse

@if ($archivos->count() > 0)
    <tr id="datosPaginacionTransporte" class="d-none" 
        data-total="{{ $archivos->total() }}"
        data-info="Mostrando {{ $archivos->firstItem() }} a {{ $archivos->lastItem() }} de {{ $archivos->total() }} registros">
        <td colspan="6">
            <div id="htmlLinksPaginacion">
                {{ $archivos->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
            </div>
        </td>
    </tr>
@endif