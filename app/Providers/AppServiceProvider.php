<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // ── Límite de peticiones en el Login (Doble Capa de Protección) ───────
        // Regla 1: 5 intentos por minuto por combinación de (Usuario + IP).
        // Regla 2: 8 intentos por minuto globales por IP (previene escaneo de múltiples usuarios).
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->input('user', '') . '|' . $request->ip()),
                Limit::perMinute(8)->by('login-ip|' . $request->ip()),
            ];
        });
    }
}
