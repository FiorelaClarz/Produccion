<?php

// Script simple para probar la generación de token directamente
echo "<h1>Test de Generación de Token</h1>";
echo "<hr>";

echo "<h2>1. Llamada directa al script gettoken.php</h2>";
$scriptPath = dirname(__DIR__) . '/resources/api/token/gettoken.php';
echo "Ruta del script: " . $scriptPath . "<br>";

if (file_exists($scriptPath)) {
    echo "El script existe.<br>";
    
    // Ejecutar el script directamente
    $command = PHP_BINARY . ' ' . escapeshellarg($scriptPath);
    echo "Comando: " . $command . "<br><br>";
    
    echo "<strong>Respuesta del script:</strong><br>";
    $response = shell_exec($command);
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Intentar decodificar como JSON
    $jsonData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<br>La respuesta es JSON válido: <br>";
        echo "<pre>" . print_r($jsonData, true) . "</pre>";
        
        if (isset($jsonData['token'])) {
            echo "<br><strong style='color:green'>✓ Token obtenido correctamente:</strong> " . substr($jsonData['token'], 0, 20) . "...<br>";
        } else {
            echo "<br><strong style='color:red'>✗ No se encontró token en la respuesta</strong><br>";
        }
    } else {
        echo "<br><strong style='color:red'>✗ La respuesta no es JSON válido</strong><br>";
        echo "Error JSON: " . json_last_error_msg() . "<br>";
    }
} else {
    echo "<strong style='color:red'>✗ El script no existe en la ruta especificada</strong><br>";
}

echo "<hr>";
echo "<h2>2. Llamada directa a la API de token</h2>";

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://64.227.4.218/stargroup/middleware/awsToken.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 10,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('userID' => 'voverkok_red%99414'),
));

$apiResponse = curl_exec($curl);
$error = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "Código HTTP: " . $httpCode . "<br>";
if ($error) {
    echo "<strong style='color:red'>✗ Error en la solicitud cURL: " . htmlspecialchars($error) . "</strong><br>";
} else {
    echo "<strong>Respuesta de API:</strong><br>";
    echo "<pre>" . htmlspecialchars($apiResponse) . "</pre>";
    
    // Intentar decodificar como JSON
    $apiJsonData = json_decode($apiResponse, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<br>La respuesta es JSON válido: <br>";
        echo "<pre>" . print_r($apiJsonData, true) . "</pre>";
        
        if (isset($apiJsonData['token'])) {
            echo "<br><strong style='color:green'>✓ Token obtenido correctamente de la API:</strong> " . substr($apiJsonData['token'], 0, 20) . "...<br>";
        } else {
            echo "<br><strong style='color:red'>✗ No se encontró token en la respuesta de la API</strong><br>";
        }
    } else {
        echo "<br><strong style='color:red'>✗ La respuesta de la API no es JSON válido</strong><br>";
        echo "Error JSON: " . json_last_error_msg() . "<br>";
    }
}

echo "<hr>";
echo "<h2>Instrucciones para usar los endpoints de prueba</h2>";
echo "<p>Para usar los parámetros en tu URL, simplemente agrégalos después de un signo de interrogación:</p>";
echo "<code>http://localhost/produccion/public/api-test/token?tienda_codigo=T01&id_item=250704&fecha_inicio=2025-05-22&fecha_fin=2025-05-29&force=true</code>";
echo "<p>O para el método helper:</p>";
echo "<code>http://localhost/produccion/public/api-test/helper?tienda_codigo=T01&id_item=250704&fecha_inicio=2025-05-22&fecha_fin=2025-05-29</code>";
