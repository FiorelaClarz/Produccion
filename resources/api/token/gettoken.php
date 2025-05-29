<?php
// Script para generar un token de autenticación para la API de ventas

header('Content-Type: application/json');

try {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://64.227.4.218/stargroup/middleware/awsToken.php',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 10, // Tiempo máximo para la conexión
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array('userID' => 'voverkok_red%99414'),
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    if ($error) {
        throw new Exception("Error en la petición cURL: " . $error);
    }
    
    if ($httpCode !== 200) {
        throw new Exception("Error HTTP: " . $httpCode);
    }
    
    // Decodificar respuesta para verificar estructura
    $responseData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar respuesta JSON: " . json_last_error_msg());
    }
    
    if (!isset($responseData['token'])) {
        throw new Exception("Estructura de respuesta inválida: no se encontró el token");
    }
    
    // Devolver JSON con el token
    echo json_encode([
        'status' => 'success',
        'token' => $responseData['token']
    ]);
    
} catch (Exception $e) {
    // Manejar errores y retornar respuesta de error en formato JSON
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
