<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificarModulo
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $idModulo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, int $idModulo): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Verificar si el perfil del usuario tiene asignado el módulo en la tabla pivote
        $tieneAcceso = DB::table('modulo_perfil')
            ->where('id_perfil', $user->id_perfil)
            ->where('id_modulo', $idModulo)
            ->exists();

        if (!$tieneAcceso) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'No tiene permisos para acceder a este módulo.'
                ], 403);
            }
            abort(403, 'No tiene permisos para acceder a este módulo.');
        }

        return $next($request);
    }
}
