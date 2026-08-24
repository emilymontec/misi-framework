<?php

declare(strict_types=1);

/**
 * Test de regresión para la CLI (`bin/biz`).
 *
 * No usa PHPUnit (mismo criterio que tests/router_middleware_pipeline_test.php):
 * son pocos comandos, y `proc_open()` + comparar stdout/stderr/exit code es
 * suficiente y no agrega una dependencia nueva solo para esto.
 *
 * A diferencia del otro test (que corre en memoria contra clases sueltas),
 * este dispara `php bin/biz ...` como subproceso real, porque lo que hay que
 * verificar es el comportamiento del propio script (parseo de argv, códigos
 * de salida, mensajes) — no algo que se pueda probar importando una clase.
 *
 * Corre contra un proyecto temporal real, creado con `misi new` (reutiliza
 * el propio comando en vez de duplicar su lógica de copiado) y contra una
 * base de datos MariaDB real y separada (`MISI_CLI_TEST_DB`, por defecto
 * `misi_cli_test`) — igual que el resto del framework, sin mocks para lo
 * que toca base de datos (ver CHANGELOG.md / AUDIT_REPORT.md: "real testing
 * contra MariaDB" es el estándar de este proyecto).
 *
 * Requiere:
 *   - Una base de datos MySQL/MariaDB alcanzable y vacía (se migra y se deja
 *     con datos de prueba; no se borra automáticamente al final).
 *   - Las mismas credenciales que usarías en .env, vía variables de entorno:
 *       MISI_CLI_TEST_DB_HOST (default 127.0.0.1)
 *       MISI_CLI_TEST_DB_NAME (default misi_cli_test)
 *       MISI_CLI_TEST_DB_USER (default misi)
 *       MISI_CLI_TEST_DB_PASS (default misi)
 *
 * Uso:
 *   php tests/cli_test.php
 */

$failures = 0;
$projectRoot = dirname(__DIR__);

// `misi new <nombre>` (ver bin/biz::runNew) siempre crea el proyecto como
// HERMANO del proyecto actual (dirname($basePath) . '/' . $nombre) y solo
// acepta un nombre simple (sin "/"), nunca una ruta arbitraria — por
// diseño, no por limitación del test. El test respeta esa misma regla en
// vez de pedirle al comando algo que nunca soportó.
$tempProjectName = 'misi-cli-test-' . uniqid();
$tempProjectPath = dirname($projectRoot) . '/' . $tempProjectName;

function check(string $label, bool $condition, int &$failures): void
{
    if ($condition) {
        echo "  OK   {$label}\n";
        return;
    }

    echo "  FAIL {$label}\n";
    $failures++;
}

/**
 * @return array{0: int, 1: string, 2: string} [exitCode, stdout, stderr]
 */
function runCli(string $cwd, string ...$args): array
{
    $cmd = array_merge([PHP_BINARY, 'bin/biz'], $args);
    $process = proc_open(
        $cmd,
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        $cwd
    );

    if ($process === false) {
        return [1, '', 'no se pudo iniciar el proceso'];
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [$exitCode, (string) $stdout, (string) $stderr];
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($dir);
}

// ---------------------------------------------------------------------
// Setup: comandos que NO tocan base de datos, corridos directo sobre el
// checkout real (solo lectura / generan archivos que limpiamos después).
// ---------------------------------------------------------------------

echo "Comandos sin base de datos (contra el checkout real)\n";

[$code, $out] = runCli($projectRoot, 'version');
check('version responde 0 y muestra "Misi Framework"', $code === 0 && str_contains($out, 'Misi Framework'), $failures);

[$code, $out] = runCli($projectRoot, 'help');
check('help responde 0 y lista "db migrate"', $code === 0 && str_contains($out, 'db migrate'), $failures);

[$code] = runCli($projectRoot); // sin argumentos == help
check('sin argumentos también responde 0 (alias de help)', $code === 0, $failures);

[$code, , $err] = runCli($projectRoot, 'comando-inexistente');
check('comando desconocido responde exit 1', $code === 1, $failures);
check('comando desconocido menciona el nombre en stderr', str_contains($err, 'comando-inexistente'), $failures);

[$code, , $err] = runCli($projectRoot, 'make', 'controller');
check('make controller sin nombre responde exit 1', $code === 1, $failures);
check('make controller sin nombre explica el uso', str_contains($err, 'Uso:'), $failures);

[$code, , $err] = runCli($projectRoot, 'make', 'controller', '../fuera');
check('make controller con path traversal se rechaza (exit 1)', $code === 1, $failures);
check('make controller con path traversal explica el motivo', str_contains($err, 'inválido'), $failures);

$genController = $projectRoot . '/app/Http/Controllers/CliSmokeTestController.php';
@unlink($genController);
[$code, $out] = runCli($projectRoot, 'make', 'controller', 'CliSmokeTest');
check('make controller crea el archivo (exit 0)', $code === 0 && is_file($genController), $failures);

[$code, , $err] = runCli($projectRoot, 'make', 'controller', 'CliSmokeTest');
check('make controller no sobrescribe un archivo existente (exit 1)', $code === 1 && str_contains($err, 'Ya existe'), $failures);
@unlink($genController);

$genModel = $projectRoot . '/app/Models/CliSmokeTest.php';
@unlink($genModel);
[$code] = runCli($projectRoot, 'make:model', 'CliSmokeTest'); // sintaxis alias (Fase 11-12)
check('make:model (alias plano) crea el archivo (exit 0)', $code === 0 && is_file($genModel), $failures);
@unlink($genModel);

echo "\n";

// ---------------------------------------------------------------------
// Proyecto temporal real (misi new) + base de datos MariaDB real.
// ---------------------------------------------------------------------

echo "Proyecto temporal (misi new) + base de datos real\n";

[$code, $out] = runCli($projectRoot, 'new', $tempProjectName);
check('new crea un proyecto nuevo (exit 0)', $code === 0, $failures);
check('new deja bin/biz ejecutable en el proyecto nuevo', is_file($tempProjectPath . '/bin/biz'), $failures);
check('new deja .env creado a partir de .env.example', is_file($tempProjectPath . '/.env'), $failures);

[$code, , $err] = runCli($projectRoot, 'new', $tempProjectName);
check('new no sobrescribe un directorio existente (exit 1)', $code === 1 && str_contains($err, 'Ya existe'), $failures);

if (is_file($tempProjectPath . '/.env')) {
    $dbHost = getenv('MISI_CLI_TEST_DB_HOST') ?: '127.0.0.1';
    $dbName = getenv('MISI_CLI_TEST_DB_NAME') ?: 'misi_cli_test';
    $dbUser = getenv('MISI_CLI_TEST_DB_USER') ?: 'misi';
    $dbPass = getenv('MISI_CLI_TEST_DB_PASS') ?: 'misi';

    // La base de datos de prueba se reutiliza entre corridas de este
    // archivo (no la crea/destruye este test, ver el docblock de
    // arriba) — se limpia aquí para que "db status" antes de migrar
    // parta siempre de cero, sin importar qué dejó una corrida previa.
    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass
        );
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec('DROP TABLE IF EXISTS `' . $table . '`');
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    } catch (\Throwable $e) {
        // Si no se puede limpiar (BD no disponible con esas
        // credenciales), los checks de abajo van a fallar igual y ya
        // reportan el problema con más contexto — no hace falta
        // duplicar el mensaje aquí.
    }

    $env = file_get_contents($tempProjectPath . '/.env');
    $env = preg_replace('/^DB_HOST=.*/m', "DB_HOST={$dbHost}", $env);
    $env = preg_replace('/^DB_DATABASE=.*/m', "DB_DATABASE={$dbName}", $env);
    $env = preg_replace('/^DB_USERNAME=.*/m', "DB_USERNAME={$dbUser}", $env);
    $env = preg_replace('/^DB_PASSWORD=.*/m', "DB_PASSWORD={$dbPass}", $env);
    file_put_contents($tempProjectPath . '/.env', $env);

    [$code, $out] = runCli($tempProjectPath, 'doctor');
    check('doctor conecta a la base de datos real (exit 0, "Todo en orden")', $code === 0 && str_contains($out, 'Todo en orden'), $failures);

    [$code, $out] = runCli($tempProjectPath, 'db', 'status');
    check('db status lista migraciones pendientes antes de migrar', $code === 0 && str_contains($out, 'Pending'), $failures);

    [$code, $out] = runCli($tempProjectPath, 'db', 'migrate');
    check('db migrate corre sin error (exit 0)', $code === 0, $failures);

    [$code, $out] = runCli($tempProjectPath, 'db', 'status');
    check('db status muestra todo como "Ran" después de migrar', $code === 0 && !str_contains($out, 'Pending') && str_contains($out, 'Ran'), $failures);

    // Password de prueba deliberadamente distinguible de cualquier otro
    // dato visible en la salida (nombre de BD, usuario...), para que
    // "no aparece en la salida" sea una aserción real y no un falso
    // positivo por coincidencia de substring (ej. contraseña "misi"
    // siendo substring de "misi_cli_test").
    $dbPassMarker = 'S3cr3tM4rk3r!!';
    $env = file_get_contents($tempProjectPath . '/.env');
    $env = preg_replace('/^DB_PASSWORD=.*/m', "DB_PASSWORD={$dbPassMarker}", $env);
    file_put_contents($tempProjectPath . '/.env', $env);

    [$code, $out] = runCli($tempProjectPath, 'info');
    check('info responde 0 y muestra la base de datos configurada', $code === 0 && str_contains($out, $dbName), $failures);
    check('info nunca imprime la contraseña en texto plano', !str_contains($out, $dbPassMarker), $failures);

    [$code, $out] = runCli($tempProjectPath, 'config:list');
    check('config:list oculta la contraseña de base de datos', $code === 0 && str_contains($out, '••••••••') && !str_contains($out, $dbPassMarker), $failures);

    // Se restaura la contraseña real para los comandos que sí necesitan
    // conectar (rollback/fresh, más abajo).
    $env = file_get_contents($tempProjectPath . '/.env');
    $env = preg_replace('/^DB_PASSWORD=.*/m', "DB_PASSWORD={$dbPass}", $env);
    file_put_contents($tempProjectPath . '/.env', $env);

    [$code, $out] = runCli($tempProjectPath, 'route:list');
    check('route:list responde 0 y lista al menos una ruta GET', $code === 0 && str_contains($out, 'GET'), $failures);

    [$code, $out] = runCli($tempProjectPath, 'db', 'rollback');
    check('db rollback revierte el último batch (exit 0)', $code === 0 && str_contains($out, 'Revertido'), $failures);

    [$code, $out] = runCli($tempProjectPath, 'db', 'fresh', '--force');
    check('db fresh --force no pide confirmación y vuelve a migrar (exit 0)', $code === 0, $failures);

    [$code, $out] = runCli($tempProjectPath, 'create', 'business');
    check('create business sin tipo lista los tipos disponibles (exit 0)', $code === 0 && str_contains($out, 'catalog'), $failures);

    [$code, , $err] = runCli($tempProjectPath, 'create', 'business', 'restaurant');
    check('create business con tipo inexistente falla explícito (exit 1)', $code === 1 && str_contains($err, 'desconocido'), $failures);
} else {
    check('(saltado: doctor/db/info/config/route requieren .env de "new")', false, $failures);
}

echo "\n";

rrmdir($tempProjectPath);

if ($failures > 0) {
    echo "{$failures} verificación(es) fallaron.\n";
    exit(1);
}

echo "Todas las verificaciones pasaron.\n";
exit(0);
