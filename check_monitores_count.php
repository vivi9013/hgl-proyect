<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$totalMonitoresTable = DB::table('monitores')->count();
echo "Total records in 'monitores' table: $totalMonitoresTable\n";

$sampleMonitores = DB::table('monitores')->take(10)->get();
echo "\nFirst 10 records in 'monitores' table:\n";
foreach ($sampleMonitores as $m) {
    echo "ID: {$m->id_monitor} | Inv: {$m->inventario} | Marca: {$m->marca} | Modelo: {$m->modelo} | Activo: {$m->activo}\n";
}

$totalMobiliarioMonitors = DB::table('mobiliario')->where('id_tipo_mobiliario', 4)->count();
echo "\nTotal records in 'mobiliario' table with id_tipo_mobiliario = 4 (Monitor): $totalMobiliarioMonitors\n";

$unassignedMobiliarioMonitors = DB::table('mobiliario')
    ->leftJoin('monitores', 'mobiliario.inventario', '=', 'monitores.inventario')
    ->where('mobiliario.id_tipo_mobiliario', 4)
    ->whereNull('monitores.id_monitor')
    ->count();
echo "Total unassigned records in 'mobiliario' (type Monitor) that are NOT in 'monitores': $unassignedMobiliarioMonitors\n";

// Let's check if there are monitors in the 'monitores' table whose inventario DOES NOT exist in 'mobiliario' at all!
$monitorsNoMobiliario = DB::table('monitores')
    ->leftJoin('mobiliario', 'monitores.inventario', '=', 'mobiliario.inventario')
    ->whereNull('mobiliario.id')
    ->count();
echo "Monitores in 'monitores' table with NO matching record in 'mobiliario': $monitorsNoMobiliario\n";

// Let's check if there are other types in tipo_mobiliario that have "monitor" in their name
$monitorTypes = DB::table('tipo_mobiliario')->where('tipo', 'like', '%monitor%')->orWhere('tipo', 'like', '%pantalla%')->get();
echo "\nTipo Mobiliario records matching 'monitor' or 'pantalla':\n";
print_r($monitorTypes);
