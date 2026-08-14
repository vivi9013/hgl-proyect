<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato de Devolucion</title>
</head>
<body style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #000000;">

@php
    // Agrupar registros por Área para que queden juntos en el mismo formato
    $gruposPorArea = $devoluciones->groupBy(function($dev) {
        return $dev->id_area_abastecimiento 
            ? ('abast_' . $dev->id_area_abastecimiento) 
            : ('almacen_' . ($dev->id_area_almacen ?? '0'));
    });
@endphp

@forelse($gruposPorArea as $grupo)
    @php
        $primeraDev = $grupo->first();
        $deptoNombre = $primeraDev->areaAbastecimiento->nombre 
            ?? $primeraDev->areaAlmacen->nombre 
            ?? '—';

        // Fechas involucradas en el área
        $fechas = $grupo->pluck('fecha_devolucion')->filter()->unique()->sort();
        if ($fechas->count() === 1) {
            $fechaTexto = \Carbon\Carbon::parse($fechas->first())->format('d/m/Y');
        } elseif ($fechaInit && $fechaFin) {
            $fechaTexto = \Carbon\Carbon::parse($fechaInit)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($fechaFin)->format('d/m/Y');
        } elseif ($fechas->count() > 1) {
            $fechaTexto = \Carbon\Carbon::parse($fechas->first())->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($fechas->last())->format('d/m/Y');
        } else {
            $fechaTexto = '—';
        }

        // Motivos involucrados en el área
        $motivosEnGrupo = $grupo->pluck('motivo.descripcion')->filter()->unique();
        $motivosTexto   = $motivosEnGrupo->implode(', ');
        
        $motivosLower = $motivosEnGrupo->map(fn($m) => mb_strtolower($m));
        $isDefecto    = $motivosLower->contains(fn($m) => str_contains($m, 'defecto') || str_contains($m, 'dañado') || str_contains($m, 'danado'));
        $isCaducado   = $motivosLower->contains(fn($m) => str_contains($m, 'caduc'));
        $isExcedente  = $motivosLower->contains(fn($m) => str_contains($m, 'excedente') || str_contains($m, 'sobrante'));

        // Recolectar todos los insumos/detalles del área
        $todosDetalles = collect();
        foreach ($grupo as $dev) {
            $motivoDev = $dev->motivo->descripcion ?? '—';
            foreach ($dev->detalles as $det) {
                $todosDetalles->push((object)[
                    'clave'           => $det->insumo->clave ?? '—',
                    'descripcion'     => $det->insumo->descripcion ?? '—',
                    'cantidad'        => $det->cantidad,
                    'fecha_caducidad' => $det->fecha_caducidad,
                    'motivo'          => $motivoDev,
                ]);
            }
        }

        $totalItems   = $todosDetalles->count();
        $filasMinimas = 8;
        $filasRelleno = max(0, $filasMinimas - $totalItems);
    @endphp

    <table style="border-collapse: collapse; width: 100%; margin-bottom: 30px;">
        {{-- ── 1. ENCABEZADO INSTITUCIONAL Y TÍTULO ── --}}
        <thead>
            <tr>
                {{-- Logo / Identificador Institucional --}}
                <th style="border: 2px solid #000000; text-align: center; vertical-align: middle; height: 55px; background-color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #c55a11;">
                    NL SALUD
                </th>
                {{-- Título Principal del Formato --}}
                <th colspan="4" style="border: 2px solid #000000; text-align: center; vertical-align: middle; height: 55px; background-color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 14pt; font-weight: bold; color: #000000;">
                    Formato de Devolución y Medicamento Caducado
                </th>
            </tr>

            {{-- Separador --}}
            <tr>
                <th colspan="5" style="height: 12px;"></th>
            </tr>

            {{-- ── 2. METADATOS: FECHA Y DEPARTAMENTO ── --}}
            <tr>
                <th colspan="2" style="text-align: left; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; height: 25px;">
                    Fecha: <span style="text-decoration: underline; font-weight: normal;">&nbsp;{{ $fechaTexto }}&nbsp;</span>
                </th>
                <th colspan="3" style="text-align: left; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; height: 25px;">
                    Departamento: <span style="text-decoration: underline; font-weight: normal;">&nbsp;{{ mb_strtoupper($deptoNombre) }}&nbsp;</span>
                </th>
            </tr>

            {{-- Separador --}}
            <tr>
                <th colspan="5" style="height: 8px;"></th>
            </tr>

            {{-- ── 3. CABECERA DE LA TABLA (COLOR SALMÓN / DURAZNO) ── --}}
            <tr>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 18px;">
                    Clave
                </th>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 45px;">
                    Nombre del Material o Medicamento
                </th>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 14px;">
                    Cantidad
                </th>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 22px;">
                    Fecha de Caducidad
                </th>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 26px;">
                    Motivo
                </th>
            </tr>
        </thead>

        {{-- ── 4. CUERPO DE LA TABLA (DATOS AGRUPADOS POR ÁREA) ── --}}
        <tbody>
            @foreach($todosDetalles as $item)
                @php
                    $caducidadTxt = $item->fecha_caducidad 
                        ? \Carbon\Carbon::parse($item->fecha_caducidad)->format('d/m/Y') 
                        : '—';
                @endphp
                <tr>
                    {{-- Clave --}}
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 10pt; height: 26px;">
                        {{ $item->clave }}
                    </td>
                    {{-- Nombre del Material o Medicamento --}}
                    <td style="border: 1px solid #000000; text-align: left; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 10pt; height: 26px;">
                        {{ mb_strtoupper($item->descripcion) }}
                    </td>
                    {{-- Cantidad --}}
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 10pt; height: 26px;">
                        {{ $item->cantidad }}
                    </td>
                    {{-- Fecha de Caducidad --}}
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 10pt; height: 26px;">
                        {{ $caducidadTxt }}
                    </td>
                    {{-- Motivo al lado del registro --}}
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-family: Calibri, Arial, sans-serif; font-size: 10pt; height: 26px; font-weight: 600;">
                        {{ mb_strtoupper($item->motivo) }}
                    </td>
                </tr>
            @endforeach

            {{-- Renglones vacíos para completar el formato visual impreso --}}
            @for($i = 0; $i < $filasRelleno; $i++)
                <tr>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                </tr>
            @endfor

            {{-- Separador post-tabla --}}
            <tr>
                <td colspan="5" style="height: 15px;"></td>
            </tr>

            {{-- ── 5. MOTIVO DE DEVOLUCIÓN (CHECKBOXES / DETALLE) ── --}}
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; height: 24px;">
                    Motivo(s) de Devolución: <span style="font-weight: normal; text-decoration: underline;">&nbsp;{{ mb_strtoupper($motivosTexto ?: '—') }}&nbsp;</span>
                </td>
            </tr>
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; height: 30px;">
                    <span style="font-weight: bold;">{{ $isDefecto ? '[X]' : '[  ]' }}</span> Defecto / Dañado
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span style="font-weight: bold;">{{ $isCaducado ? '[X]' : '[  ]' }}</span> Caducado
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span style="font-weight: bold;">{{ $isExcedente ? '[X]' : '[  ]' }}</span> Excedente
                </td>
            </tr>

            {{-- Separador --}}
            <tr>
                <td colspan="5" style="height: 15px;"></td>
            </tr>

            {{-- ── 6. OBSERVACIONES EN BLANCO ── --}}
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; height: 24px;">
                    Observaciones: ________________________________________________________________________________
                </td>
            </tr>
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; height: 24px;">
                    ____________________________________________________________________________________________________
                </td>
            </tr>
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; height: 24px;">
                    ____________________________________________________________________________________________________
                </td>
            </tr>
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; height: 24px;">
                    ____________________________________________________________________________________________________
                </td>
            </tr>

            {{-- Espacio final entre formatos --}}
            <tr>
                <td colspan="5" style="height: 35px;"></td>
            </tr>
        </tbody>
    </table>
@empty
    {{-- Formato en blanco si no se encontraron registros --}}
    <table style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th style="border: 2px solid #000000; text-align: center; vertical-align: middle; height: 55px; background-color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; color: #c55a11;">
                    NL SALUD
                </th>
                <th colspan="4" style="border: 2px solid #000000; text-align: center; vertical-align: middle; height: 55px; background-color: #ffffff; font-family: Calibri, Arial, sans-serif; font-size: 14pt; font-weight: bold; color: #000000;">
                    Formato de Devolución y Medicamento Caducado
                </th>
            </tr>
            <tr>
                <th colspan="5" style="height: 12px;"></th>
            </tr>
            <tr>
                <th colspan="2" style="text-align: left; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; height: 25px;">
                    Fecha: ________________________
                </th>
                <th colspan="3" style="text-align: left; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; height: 25px;">
                    Departamento: ________________________
                </th>
            </tr>
            <tr>
                <th colspan="5" style="height: 8px;"></th>
            </tr>
            <tr>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 18px;">Clave</th>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 45px;">Nombre del Material o Medicamento</th>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 14px;">Cantidad</th>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 22px;">Fecha de Caducidad</th>
                <th style="background-color: #F4B084; color: #000000; font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; text-align: center; vertical-align: middle; border: 1.5px solid #000000; height: 35px; width: 26px;">Motivo</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < 8; $i++)
                <tr>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                    <td style="border: 1px solid #000000; height: 26px;"></td>
                </tr>
            @endfor
            <tr>
                <td colspan="5" style="height: 15px;"></td>
            </tr>
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; height: 24px;">
                    Motivo de Devolución:
                </td>
            </tr>
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; height: 30px;">
                    [  ] Defecto / Dañado &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [  ] Caducado &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [  ] Excedente
                </td>
            </tr>
            <tr>
                <td colspan="5" style="height: 15px;"></td>
            </tr>
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; font-weight: bold; height: 24px;">
                    Observaciones: ________________________________________________________________________________
                </td>
            </tr>
            <tr>
                <td colspan="5" style="font-family: Calibri, Arial, sans-serif; font-size: 11pt; height: 24px;">
                    ____________________________________________________________________________________________________
                </td>
            </tr>
        </tbody>
    </table>
@endforelse

</body>
</html>
