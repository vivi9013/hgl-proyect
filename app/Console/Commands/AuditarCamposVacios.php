<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditarCamposVacios extends Command
{
    protected $signature = 'auditoria:campos-vacios
                            {--min=1 : Mínimo de filas para reportar una columna}
                            {--aplicar : Ejecutar la conversión, no solo reportar}';

    protected $description = "Escanea toda la base de datos buscando columnas de texto nullable que contengan '' en vez de NULL";

    // Tablas propias del framework, sin datos de negocio — se excluyen del escaneo
    protected array $tablasExcluidas = [
        'migrations', 'sessions', 'cache', 'cache_locks',
        'jobs', 'failed_jobs', 'password_reset_tokens', 'personal_access_tokens',
    ];

    public function handle()
    {
        $baseDatos = DB::getDatabaseName();

        $columnas = DB::select("
            SELECT TABLE_NAME as tabla, COLUMN_NAME as columna
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND IS_NULLABLE = 'YES'
              AND DATA_TYPE IN ('varchar', 'char', 'text', 'mediumtext', 'longtext')
            ORDER BY TABLE_NAME, ORDINAL_POSITION
        ", [$baseDatos]);

        $columnas = array_filter($columnas, fn($c) => !in_array($c->tabla, $this->tablasExcluidas));

        $this->info("Escaneando '{$baseDatos}' — " . count($columnas) . " columnas de texto nullable...");
        $barra = $this->output->createProgressBar(count($columnas));
        $barra->start();

        $hallazgos = [];
        foreach ($columnas as $col) {
            try {
                $conteo = DB::table($col->tabla)->where($col->columna, '')->count();
            } catch (\Throwable $e) {
                $barra->advance();
                continue;
            }
            if ($conteo >= (int) $this->option('min')) {
                $hallazgos[] = ['tabla' => $col->tabla, 'columna' => $col->columna, 'filas' => $conteo];
            }
            $barra->advance();
        }
        $barra->finish();
        $this->newLine(2);

        if (empty($hallazgos)) {
            $this->info('No se encontraron columnas con cadenas vacías. Nada que limpiar.');
            return self::SUCCESS;
        }

        $this->warn(count($hallazgos) . " columna(s) con datos vacíos que deberían ser NULL:");
        $this->table(
            ['Tabla', 'Columna', "Filas con ''"],
            array_map(fn($h) => array_values($h), $hallazgos)
        );

        if (!$this->option('aplicar')) {
            $this->newLine();
            $this->info('Modo solo-reporte. Para aplicar la conversión: php artisan auditoria:campos-vacios --aplicar');
            return self::SUCCESS;
        }

        if (!$this->confirm('¿Convertir estas ' . count($hallazgos) . " columna(s) de '' a NULL? Esta acción no se puede deshacer.")) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($hallazgos) {
            foreach ($hallazgos as $h) {
                $actualizadas = DB::table($h['tabla'])->where($h['columna'], '')->update([$h['columna'] => null]);
                $this->line("{$h['tabla']}.{$h['columna']}: {$actualizadas} fila(s) actualizadas a NULL");
            }
        });

        $this->info('Listo.');
        return self::SUCCESS;
    }
}
