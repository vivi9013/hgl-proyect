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

echo "Starting database correction process...\n";
echo "Found " . count($affectedFields) . " affected columns.\n\n";

DB::beginTransaction();

try {
    foreach ($affectedFields as $field) {
        $table = $field['table'];
        $column = $field['column'];
        
        echo "Processing Table [{$table}], Column [{$column}]...\n";
        
        // 2. Determine primary key or unique identifier for the table
        // We can inspect the table indexes or just look for common primary key names
        $primaryKeys = [];
        try {
            $indexes = DB::select("SHOW INDEXES FROM `{$table}` WHERE Key_name = 'PRIMARY'");
            foreach ($indexes as $index) {
                $primaryKeys[] = $index->Column_name;
            }
        } catch (\Exception $e) {
            // Fallback
        }
        
        if (empty($primaryKeys)) {
            // Check if there is an 'id' column or 'id_something'
            $cols = Schema::getColumnListing($table);
            foreach ($cols as $c) {
                if ($c === 'id' || strpos($c, 'id_') === 0 || strpos($c, '_id') !== false) {
                    $primaryKeys[] = $c;
                    break;
                }
            }
        }
        
        // Fetch all rows with corrupted characters in this column
        $rows = DB::table($table)->where($column, 'LIKE', '%├%')->get();
        $updatedCount = 0;
        
        foreach ($rows as $row) {
            $originalVal = $row->$column;
            // Convert using CP850
            $fixedVal = @iconv('UTF-8', 'CP850//IGNORE', $originalVal);
            
            // Build the where clause for this specific row
            $query = DB::table($table);
            if (!empty($primaryKeys)) {
                foreach ($primaryKeys as $pk) {
                    if (isset($row->$pk)) {
                        $query->where($pk, $row->$pk);
                    }
                }
            } else {
                // If absolutely no primary key, match by all column values (fallback)
                foreach ((array)$row as $colName => $colVal) {
                    $query->where($colName, $colVal);
                }
            }
            
            $updated = $query->update([$column => $fixedVal]);
            if ($updated) {
                $updatedCount++;
            }
        }
        
        echo "  Successfully updated {$updatedCount} / {$field['count']} records.\n";
    }
    
    DB::commit();
    echo "\nDatabase correction finished successfully! All changes have been committed.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\nAn error occurred. Database changes have been rolled back.\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
