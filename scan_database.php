<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE');
$keyName = 'Tables_in_' . $dbName;

$affectedFields = [];

foreach ($tables as $tableObj) {
    $tableName = $tableObj->$keyName;
    
    // Skip migrations or other system tables if any
    if ($tableName === 'migrations') {
        continue;
    }

    $columns = Schema::getColumnListing($tableName);
    
    foreach ($columns as $column) {
        // We only care about string/text columns. Let's do a simple count query first.
        try {
            $count = DB::table($tableName)
                ->where($column, 'LIKE', '%├%')
                ->count();
                
            if ($count > 0) {
                $affectedFields[] = [
                    'table' => $tableName,
                    'column' => $column,
                    'count' => $count
                ];
            }
        } catch (\Exception $e) {
            // Probably not a string column, skip
        }
    }
}

echo "Scan complete. Found the following columns with corrupted CP850 characters:\n";
print_r($affectedFields);

echo "\nSample corrections:\n";
foreach ($affectedFields as $field) {
    $samples = DB::table($field['table'])
        ->where($field['column'], 'LIKE', '%├%')
        ->take(3)
        ->get();
        
    echo "Table [{$field['table']}], Column [{$field['column']}]:\n";
    foreach ($samples as $sample) {
        $val = $sample->{$field['column']};
        // We might not have an 'id' column on some pivot tables, so check what key to show
        $idVal = $sample->id ?? ($sample->id_persona ?? ($sample->id_usuario ?? 'N/A'));
        $fixed = @iconv('UTF-8', 'CP850//IGNORE', $val);
        echo "  ID {$idVal}: \"{$val}\" -> \"{$fixed}\"\n";
    }
}
