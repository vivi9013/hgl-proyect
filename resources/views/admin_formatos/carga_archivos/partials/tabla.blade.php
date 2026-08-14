@forelse($archivos as $archivo)
    <tr class="{{ $archivo->activo == 0 ? 'text-muted opacity-75' : '' }}">
        <td class="ps-4 fw-bold">
            {{ ($archivos->currentPage() - 1) * $archivos->perPage() + $loop->iteration }}
        </td>
        <td>
            <span class="fw-semibold text-dark">{{ $archivo->nombre }}</span>
        </td>
        <td>
            <span class="badge bg-light text-secondary border px-2 py-1">
                {{ $archivo->categoria->categoria ?? 'Sin Categoría' }}
            </span>
        </td>
        <td>
            <small class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $archivo->descripcion_archivo }}">
                {{ $archivo->descripcion_archivo }}
            </small>
        </td>
        <td class="text-center fw-bold">{{ $archivo->version_archivo }}</td>
        <td class="text-center">
            @if($archivo->existe_fisico)
                <a href="{{ route('busca_archivos.descargar', $archivo->id_archivo) }}" 
                   class="btn btn-sm btn-light border shadow-sm rounded-circle" 
                   title="Descargar archivo ({{ strtoupper($archivo->extension) }})" 
                   target="_blank">
                    @if(in_array($archivo->extension, ['doc', 'docx']))
                        <i class="fa fa-file-word-o text-primary"></i>
                    @else
                        <i class="fa fa-file-pdf-o text-danger"></i>
                    @endif
                </a>
            @else
                <span class="fa-stack text-muted" title="Archivo físico no subido" style="font-size: 0.8rem; width: 1.5em; height: 1.5em; line-height: 1.5em;">
                    <i class="fa fa-download fa-stack-1x"></i>
                    <i class="fa fa-ban fa-stack-2x text-danger"></i>
                </span>
            @endif
        </td>
        <td class="text-center">
            <a href="{{ route('carga_archivos.status', $archivo->id_archivo) }}" 
               class="badge {{ $archivo->activo == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
               title="{{ $archivo->activo == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                <i class="fa {{ $archivo->activo == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i> 
                {{ $archivo->activo == 1 ? 'Activo' : 'Inactivo' }}
            </a>
        </td>
        <td class="text-center pe-4">
            <div class="d-flex justify-content-center gap-1">
                <a href="#" 
                   class="btn btn-sm btn-outline-secondary rounded-circle btn-editar-archivo" 
                   data-id="{{ $archivo->id_archivo }}"
                   title="Editar registro">
                    <i class="fa fa-pencil"></i>
                </a>
                <a href="{{ route('carga_archivos.cargar', $archivo->id_archivo) }}" 
                   class="btn btn-sm btn-outline-primary rounded-circle" 
                   title="Subir archivo (PDF/Word)">
                    <i class="fa fa-upload"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4 text-muted">
            <i class="fa fa-folder-open-o fs-3 mb-2 d-block"></i> No hay archivos registrados
        </td>
    </tr>
@endforelse