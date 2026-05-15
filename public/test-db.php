<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $columns = Schema::getColumnListing('usuarios');
    echo "Columnas en 'usuarios': " . implode(', ', $columns) . "\n";
    
    $user = DB::table('usuarios')->first();
    echo "Ejemplo de usuario (ID): " . ($user->id ?? $user->id_usuario ?? 'No se encontró columna ID') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
