<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::select("SELECT CONVERT(CAST(CONVERT('P├®rez' USING cp850) AS BINARY) USING utf8mb4) AS fixed");
echo "Fixed 'P├®rez': " . $res[0]->fixed . "\n";

$res2 = DB::select("SELECT CONVERT(CAST(CONVERT('Brise├▒o' USING cp850) AS BINARY) USING utf8mb4) AS fixed");
echo "Fixed 'Brise├▒o': " . $res2[0]->fixed . "\n";
