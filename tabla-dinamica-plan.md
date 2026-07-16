# Constructor de Tabla Dinámica — Contexto y Plan de Implementación (v2)

**Proyecto:** HGL Proyecto (Laravel 11)
**Fecha:** 2026-07-10 (actualizado)
**Estado:** Diseño aprobado, piloto definido en `insumos_impresoras`

> Nota de ubicación: `docs/plans/` en este repo pertenece al framework de skills vendored ("superpowers"), no a planes propios del equipo. Guarda este archivo en tu propia estructura de documentación.

---

## 1. Contexto / Problema

13 módulos de vista duplican la misma estructura de tabla (13 `partials/tabla.blade.php`, 779 líneas; ~140 líneas de `<thead>` repetidas). Objetivo: un solo lugar controle el comportamiento de columnas y de la interacción (búsqueda, filtros, paginación) en todos los módulos.

**Actualización clave de esta versión:** se crearon 2 módulos nuevos (`movimientos_insumos` e `insumos_impresoras`) que, sin saberlo, ya implementan a mano casi exactamente el patrón que este plan buscaba construir — y confirmaron que **12 de los 13 controladores ya duplican el mismo bloque AJAX/JSON** (`if ($request->ajax()...) return response()->json([...])`). Esto valida que el diseño no es especulativo: ya es el patrón real y probado del proyecto, solo que copy-pasteado.

## 2. Alternativas evaluadas y descartadas

| # | Enfoque | Por qué se descartó |
|---|---|---|
| 1 | Componente 100% genérico ("solo mandas nombre de columna") | Blade no tiene closures/render-props por celda |
| 2 | 6 tipos en archivos separados + 13 clases PHP de config | 20 archivos nuevos, demasiada superficie |
| 3 | Shell-only (solo `<thead>`, sin tocar `tbody`) | No cumple el objetivo real: un cambio no se propaga a las celdas |
| 4 | Constructor con sistema de tipos + escape hatch `personalizado` | ✅ Elegido para las celdas |
| 5 | JS/CSS de interacción duplicado por módulo (estado actual de `movimientos` e `insumos_impresoras`) | Funciona, pero es la misma duplicación que ya resolvimos para las celdas — se puede centralizar igual |

## 3. Arquitectura: 4 piezas, cada una independiente

| Pieza | Qué resuelve | Se toca una vez |
|---|---|---|
| **A. Trait `RespondeTablaAjax`** | El bloque `ajax()/wantsJson()` duplicado en 12 controladores | `app/Http/Controllers/Concerns/RespondeTablaAjax.php` |
| **B. JS compartido `tabla-interactiva.js`** | Búsqueda con debounce + paginación AJAX + filtros por checkbox, sin JS por módulo | `resources/js/components/tabla-interactiva.js`, importado una vez en `app.js` |
| **C. CSS compartido `tabla-interactiva.css`** | Estilos del shell/filtros/footer, sin `!important` defensivo | `resources/css/components/tabla-interactiva.css`, importado una vez en `app.css` |
| **D. Componentes Blade** | `<x-tabla-dinamica>` (celdas, 7 tipos) + `<x-tabla-contenedor>` (shell: card/búsqueda/footer) | `resources/views/components/` |

Como `app.js`/`app.css` ya se cargan globalmente en `layouts/app.blade.php`, las piezas B y C quedan disponibles en los 13 módulos **sin agregar ningún `@vite` nuevo por módulo**.

### 3.A — Trait

```php
// app/Http/Controllers/Concerns/RespondeTablaAjax.php
namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait RespondeTablaAjax
{
    protected function respuestaTablaAjax(Request $request, LengthAwarePaginator $paginador, string $vistaPartial, array $datosVista, string $etiqueta = 'registros')
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return null;
        }

        return response()->json([
            'html'  => view($vistaPartial, $datosVista)->render(),
            'links' => $paginador->links('pagination::bootstrap-4')->render(),
            'total' => $paginador->total(),
            'info'  => 'Mostrando ' . ($paginador->firstItem() ?? 0) . ' a ' . ($paginador->lastItem() ?? 0) . " de {$paginador->total()} $etiqueta",
        ]);
    }
}
```

### 3.B — JS compartido (autobootstrapping por `data-*`)

```js
// resources/js/components/tabla-interactiva.js
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-tabla-interactiva]').forEach(cont => {
        const tbody = document.getElementById(cont.dataset.tbodyTarget);
        const info = document.getElementById(cont.dataset.infoTarget);
        const paginacion = document.getElementById(cont.dataset.paginacionTarget);
        const buscar = cont.querySelector('[data-rol="buscar"]');
        let debounce;

        function recolectarFiltros() {
            const f = {};
            cont.querySelectorAll('[data-filtro]:checked').forEach(chk => {
                const k = chk.dataset.filtro;
                f[k] = f[k] ? `${f[k]},${chk.value}` : chk.value;
            });
            return f;
        }

        function cargar(extra = {}) {
            const url = new URL(cont.dataset.endpoint, location.origin);
            const params = { buscar: buscar?.value, ...recolectarFiltros(), ...extra };
            Object.entries(params).forEach(([k, v]) => v && url.searchParams.set(k, v));

            tbody.style.opacity = '0.5';
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(d => {
                    tbody.innerHTML = d.html;
                    if (info) info.textContent = d.info;
                    if (paginacion) paginacion.innerHTML = d.links;
                    tbody.style.opacity = '1';
                });
        }

        buscar?.addEventListener('input', () => { clearTimeout(debounce); debounce = setTimeout(() => cargar(), 400); });
        cont.querySelector('[data-rol="aplicar-filtros"]')?.addEventListener('click', () => cargar());
        paginacion?.addEventListener('click', e => {
            const a = e.target.closest('a[href]'); if (!a) return;
            e.preventDefault();
            cargar({ page: new URL(a.href).searchParams.get('page') });
        });
    });
});
```
Import en `resources/js/app.js`: `import './components/tabla-interactiva.js';`

### 3.D — Componente de celdas `<x-tabla-dinamica>` (actualizado tras analizar `insumos_impresoras`)

Dos ajustes sobre el diseño original, encontrados al auditar el módulo hermano:

1. **`clase` genérica por columna** — el módulo necesita `fw-semibold text-dark` en "Modelo" y `small text-muted` en "Compatibilidad". Se agrega al `<td>` para que aplique a cualquier tipo, no solo a texto.
2. **`toggle` cambia de estilo ícono → badge con texto** — al comparar, `insumos_impresoras` y `movimientos` (los 2 módulos más recientes del proyecto) usan un badge `rounded-pill` con texto "Activo/Inactivo", no el ícono solo check/square que se diseñó basado en los módulos viejos (`personas`, `usuarios`). Como son los módulos más nuevos, se adopta ese estilo como el estándar compartido.

```blade
{{-- resources/views/components/tabla-dinamica.blade.php --}}
@props(['columnas' => [], 'filas', 'vacio' => 'No se encontraron registros.'])

<thead class="table-light text-uppercase font-size-xs text-secondary sticky-top bg-light">
    <tr>
        @foreach($columnas as $col)
            <th class="{{ ($col['centrado'] ?? false) ? 'text-center' : '' }}"
                @if(!empty($col['ancho'])) style="width: {{ $col['ancho'] }};" @endif>
                {{ $col['label'] }}
            </th>
        @endforeach
    </tr>
</thead>
<tbody>
    @foreach($filas as $row)
        <tr class="{{ (isset($row->activo) && $row->activo == 0) ? 'text-muted bg-light-gray' : '' }}">
            @foreach($columnas as $col)
                <td class="{{ ($col['centrado'] ?? false) ? 'text-center' : '' }} {{ $col['clase'] ?? '' }}">
                    @switch($col['tipo'])

                        @case('indice')
                            {{ isset($col['campo']) ? $row->{$col['campo']} : ($filas->currentPage() - 1) * $filas->perPage() + $loop->parent->iteration }}
                            @break

                        @case('acciones')
                            <a href="{{ route($col['ruta'], $row->{$col['param']}) }}" class="btn btn-sm btn-outline-dark border-0" title="Editar">
                                <i class="fa fa-pencil-square-o"></i>
                            </a>
                            @break

                        @case('texto')
                            {{ $row->{$col['campo']} ?? '—' }}
                            @break

                        @case('texto-combo')
                            <div class="fw-semibold">{{ $row->{$col['principal']} }}</div>
                            <small class="text-muted">{{ $row->{$col['secundario']} ?? '' }}</small>
                            @break

                        @case('relacion')
                            {{ data_get($row, $col['campo']) ?: ($col['fallback'] ?? 'N/A') }}
                            @break

                        @case('toggle')
                            <a href="#" class="btn-toggle-status badge {{ $row->{$col['campo']} == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none py-2 px-3 rounded-pill shadow-sm"
                               data-id="{{ $row->{$col['campo_id'] ?? 'id'} }}"
                               title="{{ $row->{$col['campo']} == 1 ? 'Click para desactivar' : 'Click para activar' }}">
                                <i class="fa {{ $row->{$col['campo']} == 1 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                {{ $row->{$col['campo']} == 1 ? ($col['texto_on'] ?? 'Activo') : ($col['texto_off'] ?? 'Inactivo') }}
                            </a>
                            @break

                        @case('personalizado')
                            @include($col['vista'], ['row' => $row])
                            @break

                    @endswitch
                </td>
            @endforeach
        </tr>
    @endforeach

    @if($filas->isEmpty())
        <tr><td colspan="{{ count($columnas) }}" class="text-center py-4 text-muted"><i class="fa fa-exclamation-circle me-2"></i>{{ $vacio }}</td></tr>
    @endif
</tbody>
```

Nota: este componente ya no incluye `<table>` propio — se espera usarlo dentro de un `<table>` que el `<x-tabla-contenedor>` (o el propio `index.blade.php`) provea, para no forzar la envoltura si algún módulo la necesita distinta.

## 4. Análisis quirúrgico del módulo piloto: `insumos_impresoras`

Controlador (169 líneas) y vista ya usan el patrón `ajax()/wantsJson()` idéntico al trait — migración de bajo riesgo. Sin filtros de categoría (a diferencia de `movimientos`), por lo que es el caso más simple posible del contenedor: solo búsqueda + footer + paginación.

### Mapeo de columnas actuales → config del componente

| Columna actual | Tipo asignado | Parámetros |
|---|---|---|
| `#` | `indice` | — |
| Editar | `acciones` | `ruta: insumos_impresoras.edit`, `param: id_insumo_impresora` |
| Tipo | `texto` | `campo: familia` |
| Modelo | `texto` | `campo: modelo`, `clase: fw-semibold text-dark` |
| Color | `texto` | `campo: color` |
| Compatibilidad | `texto` | `campo: modelos_compatibles`, `clase: small text-muted` |
| Rendimiento | `personalizado` | formato `number_format() . ' hojas'` — no hay 2do caso real todavía para justificar un tipo compartido |
| Tiempo Uso | `texto` | `campo: tiempo_uso` |
| Stock | `personalizado` | badge con color condicional por cantidad — mismo caso, sin precedente aún |
| Status | `toggle` | `campo: activo`, `campo_id: id_insumo_impresora` |

⚠️ Detalle real encontrado: la vista actual usa `$insumo->tiempo_uso ?: '—'` (operador `?:`, atrapa también string vacío) mientras el tipo `texto` del componente usa `?? '—'` (solo atrapa `null`). Si `tiempo_uso` llega alguna vez como cadena vacía en vez de `null`, se vería distinto. Verificar en datos reales antes de migrar, o ajustar el tipo `texto` a `?:` en vez de `??`.

### Los 2 escape hatches necesarios

```blade
{{-- resources/views/control_insumos/insumos_impresoras/partials/celda-rendimiento.blade.php --}}
@if($row->hojas_uso_total)
    {{ number_format($row->hojas_uso_total) }} hojas
@else
    <span class="text-muted">—</span>
@endif
```

```blade
{{-- resources/views/control_insumos/insumos_impresoras/partials/celda-stock.blade.php --}}
<span class="badge {{ $row->stock > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
    {{ $row->stock }}
</span>
```

## 5. Plan de implementación

### Fase 0 — Crear las 4 piezas base (una sola vez, sin tocar ningún módulo)
1. `app/Http/Controllers/Concerns/RespondeTablaAjax.php` (sección 3.A)
2. `resources/js/components/tabla-interactiva.js` (sección 3.B) + import en `app.js`
3. `resources/css/components/tabla-interactiva.css` (shell/filtros/footer, sin `!important`) + import en `app.css`
4. `resources/views/components/tabla-dinamica.blade.php` (sección 3.D)

### Fase 1 — Piloto: `insumos_impresoras`
1. `InsumoImpresoraController`: agregar `use RespondeTablaAjax;` y reemplazar el bloque manual de `index()` por `$this->respuestaTablaAjax(...)`
2. Crear los 2 partials de escape hatch (sección 4)
3. En `index.blade.php`: reemplazar `<thead>` + `@include(partials.tabla)` por `<x-tabla-dinamica :columnas="[...]" :filas="$insumos" />` dentro del `<table>` existente; envolver la card con `data-tabla-interactiva data-endpoint="{{ route('insumos_impresoras.index') }}" data-tbody-target="cuerpoTablaInsumos" data-info-target="infoPaginacion" data-paginacion-target="contenedorPaginacion"`; agregar `data-rol="buscar"` al input `#busqueda-global`
4. Borrar `partials/tabla.blade.php` (ya no se usa)
5. Vaciar `insumos_impresoras.js` de la lógica de búsqueda/paginación/toggle-status (ahora la cubre el JS compartido) — dejar solo las alertas de sesión (Swal) si las tiene
6. Probar: búsqueda, paginación, toggle de status, botón editar, estado vacío, modal de alta (no se toca, sigue igual)

### Fase 2 — Validación
- [ ] Búsqueda por texto funciona igual que antes
- [ ] Paginación AJAX funciona igual que antes
- [ ] Toggle de status funciona (mismo endpoint `/control-insumos/insumos-impresoras/{id}/status`)
- [ ] Columnas "Rendimiento" y "Stock" se ven idénticas al original
- [ ] Verificar el caso de `tiempo_uso` vacío (ver advertencia de la sección 4)

### Fase 3 — Expansión (orden sugerido, de menor a mayor complejidad)
`categoria_modulos` → `proyectos` → `modulos` → `carga_archivos` → `categoria_archivos` → `permisos_archivos` → `buscador_archivos` → `impresoras` → `personas` (accessor de edad primero) → `usuarios` (tipo `personalizado` para reset password) → `movimientos_insumos` (ya tiene filtros de categoría, buen candidato para probar `data-filtro`) → `mobiliario` (módulo nuevo, sin auditar aún) → `computadoras` (al final, requiere separar su paginación embebida)

### Fase 4 — Actualizar `PROJECT_MEMORY.md`
Entrada con fecha, las 4 piezas creadas, módulos migrados, y el hallazgo del estilo de toggle actualizado a badge.

## 6. Métricas estimadas

| Concepto | Cantidad |
|---|---|
| Se elimina (13 `tabla.blade.php` + 13 `<thead>` + JS de tabla/paginación duplicado en ~12 módulos) | −919 (celdas) − ~600-700 (JS estimado, 12 × ~50-60 líneas de lógica duplicada de búsqueda/paginación) |
| Se agrega: 4 piezas base (trait + JS + CSS + componente celdas) | +180 |
| Se agrega: configs de columnas inline (13 módulos) | +150 |
| Se agrega: escape hatches (estimado 8-10 casos reales) | +60 |
| **Neto estimado** | **≈ −1,100 a −1,200 líneas**, en ~30 archivos tocados a lo largo de todas las fases |
