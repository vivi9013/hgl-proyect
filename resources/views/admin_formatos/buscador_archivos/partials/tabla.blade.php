@forelse ($archivos as $index => $archivo)
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
            @if ($archivo->existe_fisico)
                <a href="{{ route('busca_archivos.descargar', $archivo->id_archivo) }}" class="btn btn-sm btn-primary-gradient px-3.5 py-1.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-1.5" title="Descargar {{ strtoupper($archivo->extension) }}">
                    @if(in_array($archivo->extension, ['doc', 'docx']))
                        <i class="fa fa-file-word-o"></i>
                    @else
                        <i class="fa fa-file-pdf-o"></i>
                    @endif
                    <span>Descargar</span>
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