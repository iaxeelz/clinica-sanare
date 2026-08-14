<?php
// public/test.php - Archivo de prueba para diagnosticar el error 500
echo "=== DIAGNÓSTICO DE LARAVEL ===\n\n";

// 1. Verificar PHP
echo "1. PHP versión: " . phpversion() . "\n";
echo "   Memory limit: " . ini_get('memory_limit') . "\n\n";

// 2. Verificar autoload
echo "2. Verificando autoload...\n";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "   ✅ vendor/autoload.php existe\n";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "   ✅ Autoload cargado correctamente\n\n";
} else {
    die("   ❌ vendor/autoload.php NO existe\n");
}

// 3. Verificar bootstrap
echo "3. Verificando bootstrap...\n";
if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
    echo "   ✅ bootstrap/app.php existe\n\n";
} else {
    die("   ❌ bootstrap/app.php NO existe\n");
}

// 4. Verificar .env
echo "4. Verificando .env...\n";
if (file_exists(__DIR__ . '/../.env')) {
    echo "   ✅ .env existe\n";
    $env = file_get_contents(__DIR__ . '/../.env');
    echo "   Tamaño: " . strlen($env) . " bytes\n\n";
} else {
    echo "   ⚠️ .env NO existe (usando variables de entorno)\n\n";
}

// 5. Probar conexión a base de datos
echo "5. Probando conexión a base de datos...\n";
try {
    $dsn = "pgsql:host=ep-rapid-mud-ayla9xkl.c-5.us-east-2.aws.neon.tech;port=5432;dbname=neondb";
    $pdo = new PDO($dsn, 'neondb_owner', 'npg_GoDVC3YnHXB4');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Conexión exitosa a Neon\n";
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "   Versión: " . substr($version, 0, 50) . "...\n\n";
} catch (Exception $e) {
    echo "   ❌ Error de conexión: " . $e->getMessage() . "\n\n";
}

// 6. Intentar cargar Laravel
echo "6. Intentando cargar Laravel...\n";
try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "   ✅ App cargada correctamente\n";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "   ✅ Kernel creado\n";
    
    echo "   🎉 ¡Laravel está funcionando!\n";
} catch (Exception $e) {
    echo "   ❌ Error cargando Laravel: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}
