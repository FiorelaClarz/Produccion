<?php
// public/index.php - Código de prueba simple

header('Content-Type: text/plain');

echo "¡Funciona! - Prueba básica de Laravel\n\n";
echo "=== Información del servidor ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

echo "=== Pruebas básicas ===\n";

// 1. Prueba de conexión a archivos
try {
    require __DIR__.'/../vendor/autoload.php';
    echo "✓ Autoload cargado correctamente\n";
} catch (Exception $e) {
    echo "✗ Error cargando autoload: " . $e->getMessage() . "\n";
}

// 2. Prueba de variables de entorno
try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "✓ Aplicación inicializada\n";
    
    if (file_exists(__DIR__.'/../.env')) {
        echo "✓ Archivo .env encontrado\n";
    } else {
        echo "✗ Archivo .env NO encontrado\n";
    }
} catch (Exception $e) {
    echo "✗ Error inicializando aplicación: " . $e->getMessage() . "\n";
}

// 3. Prueba de base de datos (opcional)
try {
    if (function_exists('mysqli_connect')) {
        echo "✓ Extensión MySQL disponible\n";
    } else {
        echo "✗ Extensión MySQL NO disponible\n";
    }
} catch (Exception $e) {
    echo "✗ Error verificando MySQL: " . $e->getMessage() . "\n";
}

echo "\nSi ves este mensaje, el servidor está procesando PHP correctamente.\n";
echo "Ahora restaura el archivo index.php original de Laravel.\n";