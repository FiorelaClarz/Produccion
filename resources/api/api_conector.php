<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Configuración de la base de datos
$db_host = 'localhost';
$db_port = '5432';
$db_name = 'produccion';
$db_user = 'postgres';
$db_pass = '1234';

// 2. Ruta a tu archivo JSON (ajusta esta ruta)
$json_file_path = __DIR__ . '/productosapi/Productos.json'; // Cambia a la ubicación real de tu archivo

// 3. Función para conectar a la base de datos
function connectToDatabase() {
    global $db_host, $db_port, $db_name, $db_user, $db_pass;
    
    try {
        $conn = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
    }
}

// 4. Función para leer el archivo JSON
function readJsonFile($file_path) {
    if (!file_exists($file_path)) {
        return ['error' => 'Archivo JSON no encontrado en: ' . $file_path];
    }
    
    $json_content = file_get_contents($file_path);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Error al decodificar JSON: ' . json_last_error_msg()];
    }
    
    return $data;
}

// 5. Función para insertar datos en PostgreSQL (optimizada)
function importJsonToDatabase($data) {
    if (isset($data['error'])) {
        return $data; // Retorna el error si lo hay
    }

    $conn = connectToDatabase();
    
    // Consulta SQL optimizada
    $sql = "INSERT INTO productos (
        id_item, id_area, area, codigo, nombre, costo, ref_venta, margen, 
        id_impuesto, unspsc, impuesto, id_categoria, id_presentacion, 
        presentacion, percepcion, id_marca, marca, categoria, 
        id_sub_categoria, sub_categoria, url, condicion, id_item_relacion, 
        item_cantidad_relacion, arti_por, aplicacion, estatus_mayor, 
        precio_mayor, costo_anterior, descuento1, descuento2, venta
    ) VALUES (
        :id_item, :id_area, :area, :codigo, :nombre, :costo, :ref_venta, :margen, 
        :id_impuesto, :unspsc, :impuesto, :id_categoria, :id_presentacion, 
        :presentacion, :percepcion, :id_marca, :marca, :categoria, 
        :id_sub_categoria, :sub_categoria, :url, :condicion, :id_item_relacion, 
        :item_cantidad_relacion, :arti_por, :aplicacion, :estatus_mayor, 
        :precio_mayor, :costo_anterior, :descuento1, :descuento2, :venta
    ) ON CONFLICT (id_item) DO UPDATE SET
        nombre = EXCLUDED.nombre,
        costo = EXCLUDED.costo,
        ref_venta = EXCLUDED.ref_venta,
        margen = EXCLUDED.margen,
        venta = EXCLUDED.venta,
        fecha_actualizacion = NOW()";
    
    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare($sql);
        
        $imported = 0;
        $errors = 0;
        $error_messages = [];
        
        foreach ($data as $item) {
            try {
                // Valores por defecto para campos opcionales
                $item = array_merge([
                    'url' => '',
                    'condicion' => 1,
                    'id_item_relacion' => null,
                    'item_cantidad_relacion' => 0,
                    'aplicacion' => 0,
                    'estatus_mayor' => 0,
                    'precio_mayor' => 0,
                    'descuento1' => '0',
                    'descuento2' => '0'
                ], $item);
                
                $stmt->execute([
                    ':id_item' => $item['id_item'] ?? null,
                    ':id_area' => $item['id_area'] ?? null,
                    ':area' => $item['area'] ?? '',
                    ':codigo' => $item['codigo'] ?? '',
                    ':nombre' => $item['nombre'] ?? '',
                    ':costo' => $item['costo'] ?? 0,
                    ':ref_venta' => $item['ref_venta'] ?? 0,
                    ':margen' => $item['margen'] ?? 0,
                    ':id_impuesto' => $item['id_impuesto'] ?? 1,
                    ':unspsc' => $item['unspsc'] ?? 0,
                    ':impuesto' => $item['impuesto'] ?? '',
                    ':id_categoria' => $item['id_categoria'] ?? 1,
                    ':id_presentacion' => $item['id_presentacion'] ?? 1,
                    ':presentacion' => $item['presentacion'] ?? '',
                    ':percepcion' => $item['percepcion'] ?? 0,
                    ':id_marca' => $item['id_marca'] ?? 0,
                    ':marca' => $item['marca'] ?? '',
                    ':categoria' => $item['categoria'] ?? '',
                    ':id_sub_categoria' => $item['id_sub_categoria'] ?? 0,
                    ':sub_categoria' => $item['sub_categoria'] ?? '',
                    ':url' => $item['url'] ?? '',
                    ':condicion' => $item['condicion'] ?? 1,
                    ':id_item_relacion' => $item['id_item_relacion'] ?? null,
                    ':item_cantidad_relacion' => $item['item_cantidad_relacion'] ?? 0,
                    ':arti_por' => $item['arti_por'] ?? 0,
                    ':aplicacion' => $item['aplicacion'] ?? 0,
                    ':estatus_mayor' => $item['estatus_mayor'] ?? 0,
                    ':precio_mayor' => $item['precio_mayor'] ?? 0,
                    ':costo_anterior' => $item['costo_anterior'] ?? 0,
                    ':descuento1' => $item['descuento1'] ?? '0',
                    ':descuento2' => $item['descuento2'] ?? '0',
                    ':venta' => $item['venta'] ?? 0
                ]);
                $imported++;
            } catch (PDOException $e) {
                $errors++;
                $error_messages[] = [
                    'id_item' => $item['id_item'] ?? 'N/A',
                    'error' => $e->getMessage()
                ];
                // Continúa con el siguiente item aunque falle uno
                continue;
            }
        }
        
        $conn->commit();
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'error_messages' => $error_messages,
            'message' => "Importación completada: $imported registros procesados ($errors errores)"
        ];
        
    } catch (PDOException $e) {
        $conn->rollBack();
        return ['error' => 'Error en la transacción: ' . $e->getMessage()];
    }
}

// 6. Proceso principal
try {
    // Leer el archivo JSON
    $json_data = readJsonFile($json_file_path);
    
    // Importar a la base de datos
    $result = importJsonToDatabase($json_data);
    
    // Mostrar resultados
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
}
?>