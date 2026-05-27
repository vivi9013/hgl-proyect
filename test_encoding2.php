<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Persona;

$p = Persona::find(1);
echo "Original ap_paterno: " . $p->ap_paterno . "\n";

$converted = @iconv('UTF-8', 'CP850//IGNORE', $p->ap_paterno);
echo "Converted ap_paterno (CP850): " . $converted . "\n";
echo "Converted hex: " . bin2hex($converted) . "\n";

$converted2 = @mb_convert_encoding($p->ap_paterno, 'CP850', 'UTF-8');
echo "Converted ap_paterno (mb CP850): " . $converted2 . "\n";
echo "Converted mb hex: " . bin2hex($converted2) . "\n";
