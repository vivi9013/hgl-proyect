<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrarDetallePlantillasLegacy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrar:detalle-plantillas-legacy {--dry-run : Muestra una vista previa sin modificar la base de datos} {--truncate : Limpia las tablas destino antes de migrar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra plantillas e insumos de detalle de las tablas legacy (plantillas y detalle_plantillas) a las nuevas tablas (plantilla_pedidos y detalle_plantilla_pedidos)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $shouldTruncate = $this->option('truncate');

        $this->info($isDryRun ? '--- MODO DRY-RUN (SIMULACIÓN DE MIGRACIÓN) ---' : '--- INICIANDO MIGRACIÓN REAL DE PLANTILLAS Y DETALLES ---');

        if (!Schema::hasTable('plantillas') || !Schema::hasTable('detalle_plantillas')) {
            $this->error('Error: Las tablas legacy (plantillas o detalle_plantillas) no existen en la base de datos.');
            return 1;
        }

        $legacyHeaders = DB::table('plantillas')->get();
        $legacyDetails = DB::table('detalle_plantillas')->get();

        $this->line("Registros legacy encontrados:");
        $this->line(" - Encabezados (plantillas): {$legacyHeaders->count()}");
        $this->line(" - Detalles (detalle_plantillas): {$legacyDetails->count()}");
        $this->newLine();

        $hasFondoFijoCol = Schema::hasColumn('detalle_plantilla_pedidos', 'fondo_fijo');

        $headersToInsert = [];
        $detailsToInsert = [];
        $skippedDetails = 0;
        $orphanInsumoIds = [];

        // 1. Mapeo de Encabezados (Copiando id_area_almacen, id_area_abastecimiento, id_subarea_abastecimiento)
        foreach ($legacyHeaders as $header) {
            $headersToInsert[] = [
                'id_plantilla_pedido'       => $header->id_plantilla,
                'nombre'                    => $header->nombre,
                'descripcion'               => 'Migrada desde sistema legacy (ID: ' . $header->id_plantilla . ')',
                'id_area_abastecimiento'    => $header->id_area_abastecimiento ?? 1,
                'id_subarea_abastecimiento' => $header->id_subarea_abastecimiento,
                'id_area_almacen'           => $header->id_area_almacen,
                'fecha_registro'            => $header->fecha_registro ?? now()->toDateString(),
                'hora_registro'             => $header->hora_registro ?? now()->toTimeString(),
                'activo'                    => $header->activo ?? 1,
                'id_usuario'                => $header->id_usuario ?? 1,
            ];
        }

        // 2. Mapeo de Detalles (Copiando cve_insumo, cantidad y fondo_fijo)
        foreach ($legacyDetails as $detail) {
            // Verificar existencia de la plantilla correspondiente
            $plantillaExists = collect($headersToInsert)->contains('id_plantilla_pedido', $detail->id_plantilla);
            if (!$plantillaExists) {
                $skippedDetails++;
                continue;
            }

            // Verificar si el insumo existe en el catálogo de insumos
            $insumoExists = DB::table('insumos')->where('id_insumo', $detail->id_insumo)->exists();
            if (!$insumoExists) {
                $skippedDetails++;
                $orphanInsumoIds[] = $detail->id_insumo;
                continue;
            }

            $item = [
                'id_detalle_plantilla' => $detail->id_detalle_plantilla,
                'id_plantilla_pedido'  => $detail->id_plantilla,
                'id_insumo'            => $detail->id_insumo,
                'cve_insumo'           => $detail->clave_insumo,
                'cantidad'             => $detail->cantidad ?? 1,
                'fondo_fijo'           => $detail->fondo_fijo ?? $detail->cantidad ?? 1,
            ];

            $detailsToInsert[] = $item;
        }

        // Mostrar muestra del mapeo (Dry-Run Preview)
        $this->info("--- MUESTRA DE MAPEO DE PLANTILLAS ENCABEZADO (Top 5) ---");
        $this->table(
            ['ID Plantilla', 'Nombre', 'Area Abast', 'Subarea Abast', 'Area Almacén', 'Activo'],
            array_map(fn($h) => [
                $h['id_plantilla_pedido'],
                $h['nombre'],
                $h['id_area_abastecimiento'],
                $h['id_subarea_abastecimiento'],
                $h['id_area_almacen'] ?? 'NULL',
                $h['activo']
            ], array_slice($headersToInsert, 0, 5))
        );

        $this->newLine();
        $this->info("--- MUESTRA DE MAPEO DE DETALLE INSUMOS (Top 10) ---");
        $this->table(
            ['ID Detalle', 'ID Plantilla', 'ID Insumo', 'Clave Insumo', 'Cantidad', 'Fondo Fijo'],
            array_map(fn($d) => [
                $d['id_detalle_plantilla'],
                $d['id_plantilla_pedido'],
                $d['id_insumo'],
                $d['cve_insumo'],
                $d['cantidad'],
                $d['fondo_fijo']
            ], array_slice($detailsToInsert, 0, 10))
        );

        $this->newLine();
        $this->info("=== RESUMEN DE MAPEO ===");
        $this->line(" - Plantillas a insertar/actualizar (updateOrInsert): " . count($headersToInsert));
        $this->line(" - Detalles a insertar/actualizar (updateOrInsert): " . count($detailsToInsert));
        $this->line(" - Detalles omitidos/huérfanos: {$skippedDetails}");
        if (count($orphanInsumoIds) > 0) {
            $this->warn("   IDs de insumos no encontrados: " . implode(', ', array_unique($orphanInsumoIds)));
        }

        if ($isDryRun) {
            $this->newLine();
            $this->comment("✨ Simulación ejecutada con éxito. No se escribieron registros a la base de datos.");
            $this->comment("Para aplicar la migración real, ejecuta: php artisan migrar:detalle-plantillas-legacy");
            return 0;
        }

        // Ejecutar migración dentro de una transacción DB
        try {
            DB::transaction(function () use ($shouldTruncate, $headersToInsert, $detailsToInsert) {
                if ($shouldTruncate) {
                    Schema::disableForeignKeyConstraints();
                    DB::table('detalle_plantilla_pedidos')->truncate();
                    DB::table('plantilla_pedidos')->truncate();
                    Schema::enableForeignKeyConstraints();
                }

                // Insertar o actualizar Encabezados (usa updateOrInsert por PK id_plantilla_pedido)
                foreach ($headersToInsert as $header) {
                    DB::table('plantilla_pedidos')->updateOrInsert(
                        ['id_plantilla_pedido' => $header['id_plantilla_pedido']],
                        $header
                    );
                }

                // Insertar o actualizar Detalles (usa updateOrInsert por PK id_detalle_plantilla)
                foreach (array_chunk($detailsToInsert, 100) as $chunk) {
                    foreach ($chunk as $detail) {
                        DB::table('detalle_plantilla_pedidos')->updateOrInsert(
                            ['id_detalle_plantilla' => $detail['id_detalle_plantilla']],
                            $detail
                        );
                    }
                }
            });

            $this->newLine();
            $this->info("✅ MIGRACIÓN COMPLETADA CON ÉXITO.");
            $this->line(" - Registros en plantilla_pedidos: " . DB::table('plantilla_pedidos')->count());
            $this->line(" - Registros en detalle_plantilla_pedidos: " . DB::table('detalle_plantilla_pedidos')->count());
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ ERROR DURANTE LA MIGRACIÓN: " . $e->getMessage());
            return 1;
        }
    }
}
