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

// 1. Detect all affected columns
foreach ($tables as $tableObj) {
    $tableName = $tableObj->$keyName;
    if ($tableName === 'migrations') {
        continue;
    }

    $columns = Schema::getColumnListing($tableName);
    foreach ($columns as $column) {
        try {
            $count = DB::table($tableName)->where($column, 'LIKE', '%├%')->count();
            if ($count > 0) {
                $affectedFields[] = [
                    'table' => $tableName,
                    'column' => $column,
                    'count' => $count
                ];
            }
        } catch (\Exception $e) {
            // Skip non-string columns
        }
    }
}

echo "Starting FAST database correction process using SQL CONVERT...\n";
echo "Found " . count($affectedFields) . " affected columns.\n\n";

DB::beginTransaction();

try {
    foreach ($affectedFields as $field) {
        $table = $field['table'];
        $column = $field['column'];
        $count = $field['count'];
        
        echo "Processing Table [{$table}], Column [{$column}] (Records to update: {$count})...\n";
        
        // Execute the fast SQL UPDATE using CONVERT
        $updatedCount = DB::update("
            UPDATE `{$table}` 
            SET `{$column}` = CONVERT(CAST(CONVERT(`{$column}` USING cp850) AS BINARY) USING utf8mb4) 
            WHERE `{$column}` LIKE '%├%'
        ");
        
        echo "  Successfully updated {$updatedCount} records.\n";
    }
    
    DB::commit();
    echo "\nDatabase correction finished successfully in milliseconds! All changes committed.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\nAn error occurred. Database changes have been rolled back.\n";
    echo "Error: " . $e->getMessage() . "\n";
}
