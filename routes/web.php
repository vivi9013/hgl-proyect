<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

/* Route::get('/', function () {
    return view('index');
})->name('dashboard'); */

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');

// Placeholder para rutas del sidebar
Route::get('/citas', function() { return "Página de Citas en construcción"; })->name('citas.index');
