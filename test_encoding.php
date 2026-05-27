<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Persona;

$personas = Persona::where('nombre', 'LIKE', '%├%')
    ->orWhere('ap_paterno', 'LIKE', '%├%')
    ->orWhere('ap_materno', 'LIKE', '%├%')
    ->take(15)
    ->get();

foreach ($personas as $p) {
    echo "ID: " . $p->id . "\n";
    echo "  Nombre: " . $p->nombre . " -> Hex: " . bin2hex($p->nombre) . "\n";
    echo "  Ap Paterno: " . $p->ap_paterno . " -> Hex: " . bin2hex($p->ap_paterno) . "\n";
    echo "  Ap Materno: " . $p->ap_materno . " -> Hex: " . bin2hex($p->ap_materno) . "\n";
    echo "  Estado: " . $p->estado . " -> Hex: " . bin2hex($p->estado) . "\n";
    echo "----------------------------------------\n";
}
