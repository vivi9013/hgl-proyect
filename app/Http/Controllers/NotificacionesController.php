<?php

namespace App\Http\Controllers;

use App\Models\SolicitudReseteoPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificacionesController extends Controller
{
    /**
     * Devuelve las notificaciones de la campanita en el header.
     * Accesible para cualquier usuario autenticado (sin restricción de módulo).
     *
     * Genera dos grupos de notificaciones:
     *   1. Solicitudes de reseteo pendientes  → enlaza al módulo de Usuarios
     *   2. Actividades recientes de recuperación de contraseña → enlaza al registro de actividades
     */
    public function index()
    {
        $notificaciones        = [];
        $solicitudesPendientes = collect();

        // ── 1. Solicitudes de reseteo pendientes ──────────────────────────────
        try {
            $solicitudesPendientes = SolicitudReseteoPassword::with(['usuario.persona'])
                ->where('estado', 'pendiente')
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();

            foreach ($solicitudesPendientes as $solicitud) {
                $persona = $solicitud->usuario?->persona;
                $nombreCompleto = $persona
                    ? trim(($persona->nombre ?? '') . ' ' . ($persona->ap_paterno ?? '') . ' ' . ($persona->ap_materno ?? ''))
                    : $solicitud->nombre_declarado;

                $notificaciones[] = [
                    'tipo'   => 'solicitud',
                    'icono'  => 'bi-person-lock',
                    'color'  => 'warning',
                    'titulo' => 'Solicitud de restablecimiento de contraseña',
                    'cuerpo' => $nombreCompleto ?: $solicitud->nombre_usuario,
                    'fecha'  => $solicitud->fecha . ' ' . $solicitud->hora,
                    'enlace' => route('usuarios.index'),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('NotificacionesController – solicitudes: ' . $e->getMessage());
        }

        // ── 2. Actividades recientes de "Recuperación de Contraseña" ──────────
        // NOTA: el PK de la tabla `actividades` es `id_actividad`, no `id`
        try {
            $actividades = DB::table('actividades')
                ->where('filtro', 'Recuperación de Contraseña')
                ->orderBy('id_actividad', 'desc')
                ->take(10)
                ->get();

            foreach ($actividades as $actividad) {
                $descripcion = $actividad->descripcion ?? '';

                // Enriquecer con nombre completo si hay persona vinculada
                if (!empty($actividad->id_persona)) {
                    $persona = DB::table('personas')
                        ->where('id', $actividad->id_persona)
                        ->first(['nombre', 'ap_paterno', 'ap_materno']);

                    if ($persona) {
                        $nombreCompleto = trim(
                            ($persona->nombre    ?? '') . ' ' .
                            ($persona->ap_paterno ?? '') . ' ' .
                            ($persona->ap_materno ?? '')
                        );
                        if ($nombreCompleto) {
                            $descripcion = $nombreCompleto . ' — Solicitud de recuperación de contraseña';
                        }
                    }
                }

                $notificaciones[] = [
                    'tipo'   => 'actividad',
                    'icono'  => 'bi-clock-history',
                    'color'  => 'info',
                    'titulo' => 'Registro de Actividades',
                    'cuerpo' => $descripcion,
                    'fecha'  => ($actividad->fecha ?? '') . ' ' . ($actividad->hora ?? ''),
                    'enlace' => route('actividades.index'),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('NotificacionesController – actividades: ' . $e->getMessage());
        }

        // Ordenar por fecha/hora descendente (mezcla ambos grupos)
        usort($notificaciones, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));

        // Tomar sólo las 15 más recientes para no saturar el dropdown
        $notificaciones = array_slice($notificaciones, 0, 15);

        return response()->json([
            'total'          => count($notificaciones),
            'pendientes'     => $solicitudesPendientes->count(),
            'notificaciones' => $notificaciones,
        ]);
    }
}
