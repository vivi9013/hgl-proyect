# Fix: NULL vs cadena vacía en campos opcionales (`nullable`)

**Proyecto:** HGL Proyecto
**Alcance:** 4 controladores, 18 líneas + 1 hallazgo adicional
**Causa raíz:** `trim($request->campo ?? '')` convierte campos opcionales sin llenar en `''` en vez de `null`, rompiendo la semántica de "dato no proporcionado" y causando renderizado inconsistente en Blade (`??` vs `?:`).

**Patrón de fix estándar** (aplica a 17 de los 18 casos):
```php
// Antes
'campo' => trim($request->campo ?? ''),
// Después
'campo' => $request->filled('campo') ? trim($request->campo) : null,
```

---

## 1. `app/Http/Controllers/ControlInsumos/ImpresoraController.php`

### 1.1 — Caso especial: `modelo` pasa a `required` (no usa `filled()`)

```php
// Línea 87 (guardar) y línea 140 (actualizar) — validación
- 'modelo'      => 'nullable|string|max:100',
+ 'modelo'      => 'required|string|max:100',
```

```php
// Línea 103 (guardar) y línea 153 (actualizar) — asignación
- 'modelo'      => trim($request->modelo ?? ''),
+ 'modelo'      => trim($request->modelo),
```

### 1.2 — `descripcion`, `tecnologia`, `ip` → `filled()`

```php
// Líneas 105, 106, 109 (guardar) y 155, 156, 159 (actualizar)
- 'descripcion' => trim($request->descripcion ?? ''),
- 'tecnologia'  => trim($request->tecnologia ?? ''),
+ 'descripcion' => $request->filled('descripcion') ? trim($request->descripcion) : null,
+ 'tecnologia'  => $request->filled('tecnologia') ? trim($request->tecnologia) : null,
```
```php
- 'ip'          => trim($request->ip ?? ''),
+ 'ip'          => $request->filled('ip') ? trim($request->ip) : null,
```

**Total en este archivo: 4 líneas de validación (2 pares) + 8 líneas de asignación = 12 cambios.**

---

## 2. `app/Http/Controllers/ControlInsumos/InsumoImpresoraController.php`

```php
// Líneas 68-69 (guardar) y 113-114 (actualizar)
- 'modelos_compatibles'=> trim($request->modelos_compatibles ?? ''),
- 'tiempo_uso'         => trim($request->tiempo_uso ?? ''),
+ 'modelos_compatibles'=> $request->filled('modelos_compatibles') ? trim($request->modelos_compatibles) : null,
+ 'tiempo_uso'         => $request->filled('tiempo_uso') ? trim($request->tiempo_uso) : null,
```

**Total: 4 cambios.**

---

## 3. `app/Http/Controllers/ControlInsumos/MovimientoInsumoController.php`

```php
// Línea 113 (guardar)
- 'proveedor'           => trim($request->proveedor ?? ''),
+ 'proveedor'           => $request->filled('proveedor') ? trim($request->proveedor) : null,
```

```php
// Línea 274 (actualizar, dentro del if $nuevoTipo === 'Entrada')
- $mov->proveedor = trim($request->proveedor ?? '');
+ $mov->proveedor = $request->filled('proveedor') ? trim($request->proveedor) : null;
```

### ⚠️ Hallazgo adicional (no estaba en el conteo original de 18): línea 275
```php
// Rama else — cuando el tipo cambia a Salida, proveedor no aplica
- $mov->proveedor = '';
+ $mov->proveedor = null;
```
Mismo principio: "proveedor no aplica a una Salida" es semánticamente `null` (dato no aplicable), no cadena vacía. No usa `filled()` porque no viene de un campo de formulario en este punto — es una limpieza explícita, así que el fix es directo.

**Total: 3 cambios (2 del patrón original + 1 hallazgo adicional).**

---

## 4. `app/Http/Controllers/Mobiliario/MobiliarioController.php`

```php
// Línea 100 (guardar) y línea 174 (actualizar)
- 'serie' => trim($request->serie ?? ''),
+ 'serie' => $request->filled('serie') ? trim($request->serie) : null,
```
```php
// Línea 102 (guardar) y línea 176 (actualizar)
- 'otros' => trim($request->otros ?? ''),
+ 'otros' => $request->filled('otros') ? trim($request->otros) : null,
```

**Total: 4 cambios.**

---

## Resumen

| Archivo | Cambios |
|---|---|
| `ImpresoraController.php` | 12 (incluye 4 de validación `modelo`) |
| `InsumoImpresoraController.php` | 4 |
| `MovimientoInsumoController.php` | 3 (incluye 1 hallazgo adicional) |
| `MobiliarioController.php` | 4 |
| **Total** | **23** |

---

## Paso previo a la limpieza de datos históricos: auditar TODA la base de datos primero

Los 8 campos de arriba son solo los que aparecieron en el código que revisamos. Es posible que existan más columnas con `''` en vez de `NULL` en tablas que ningún controlador actual toca (datos heredados del sistema legacy, ediciones manuales directas a la BD, etc.). En vez de adivinar, se crea un comando Artisan que **examina todas las tablas y columnas de texto nullable de la base de datos real**, cuenta cuántas filas tienen `''` en cada una, y genera el script de limpieza solo para lo que realmente encuentre — nada hardcodeado.

```php
<?php
// app/Console/Commands/AuditarCamposVacios.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditarCamposVacios extends Command
{
    protected $signature = 'auditoria:campos-vacios {--min=1 : Mínimo de filas para reportar una columna} {--aplicar : Ejecutar la conversión, no solo reportar}';
    protected $description = 'Escanea toda la base de datos buscando columnas de texto nullable que contengan \'\' en vez de NULL';

    // Tablas propias del framework, sin datos de negocio — se excluyen del escaneo
    protected array $tablasExcluidas = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'password_reset_tokens', 'personal_access_tokens'];

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

        $this->warn(count($hallazgos) . ' columna(s) con datos vacíos que deberían ser NULL:');
        $this->table(['Tabla', 'Columna', "Filas con ''"], array_map(fn($h) => array_values($h), $hallazgos));

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
```

### Uso

```bash
# 1. Solo reportar (no toca datos) — correrlo primero siempre
php artisan auditoria:campos-vacios

# 2. Revisar la tabla de resultados con el equipo/DBA

# 3. Aplicar la conversión (pide confirmación, corre en una transacción)
php artisan auditoria:campos-vacios --aplicar
```

El paso 1 reemplaza la necesidad de adivinar qué tablas limpiar — el comando genera su propia lista de hallazgos reales contra la base de datos de producción, y el paso 3 solo convierte exactamente esas columnas, no una lista fija.

---

## Checklist de verificación post-fix

- [ ] Crear un registro de impresora sin llenar `descripcion`/`tecnologia`/`ip` → confirmar que quedan `NULL` en BD, no `''`
- [ ] Intentar guardar una impresora **sin** modelo → debe rechazar la validación (antes lo permitía)
- [ ] Editar un insumo de impresora dejando `tiempo_uso` vacío → confirmar `NULL`
- [ ] Registrar una Salida de movimiento → confirmar que `proveedor` queda `NULL`, no `''`
- [ ] Registrar mobiliario sin `serie`/`otros` → confirmar `NULL`
- [ ] Correr `php artisan auditoria:campos-vacios` (modo reporte) en un respaldo/copia de la BD real antes de tocar producción
- [ ] Revisar la tabla de hallazgos con el equipo — puede incluir columnas fuera de los 4 controladores ya auditados
- [ ] Correr `php artisan auditoria:campos-vacios --aplicar` sobre producción solo después de validar el paso anterior
