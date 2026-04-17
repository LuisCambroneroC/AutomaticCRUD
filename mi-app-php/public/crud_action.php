<?php
session_start();
require_once __DIR__ . '/../src/Config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $accion = $_POST['action'] ?? '';
    $tabla = $_POST['tabla'] ?? '';
    
    if (empty($tabla)) {
        throw new Exception('Tabla no especificada');
    }
    
    // Validar que la tabla exista en la sesión
    if (!isset($_SESSION['tabla_seleccionada']) || $_SESSION['tabla_seleccionada'] !== $tabla) {
        throw new Exception('Tabla no válida o no configurada en sesión');
    }
    
    // Obtener campos marcados de la sesión
    $campos_marcados = $_SESSION['campos_marcados'] ?? [];
    if (empty($campos_marcados)) {
        throw new Exception('No hay campos configurados');
    }
    
    // Determinar primary key
    $primary_key = null;
    $campos_data = $db->query("SHOW COLUMNS FROM `" . $tabla . "`");
    if ($campos_data instanceof mysqli_result) {
        while ($row = $campos_data->fetch_assoc()) {
            if ($row['Key'] === 'PRI' && in_array($row['Field'], $campos_marcados)) {
                $primary_key = $row['Field'];
                break;
            }
        }
    } elseif ($campos_data instanceof mysqli_stmt) {
        $result = $campos_data->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($row['Key'] === 'PRI' && in_array($row['Field'], $campos_marcados)) {
                $primary_key = $row['Field'];
                break;
            }
        }
        $campos_data->close();
    }
    
    switch ($accion) {
        case 'insert':
            $fields = [];
            $placeholders = [];
            $params = [];
            
            foreach ($campos_marcados as $campo) {
                // Excluir primary key si es auto_increment
                if ($campo !== $primary_key) {
                    $fields[] = '`' . $campo . '`';
                    $placeholders[] = '?';
                    $params[] = $_POST[$campo] ?? null;
                }
            }
            
            if (empty($fields)) {
                throw new Exception('No hay campos para insertar');
            }
            
            $fieldsStr = implode(', ', $fields);
            $placeholdersStr = implode(', ', $placeholders);
            
            $sql = "INSERT INTO `$tabla` ($fieldsStr) VALUES ($placeholdersStr)";
            $stmt = $db->query($sql, $params);
            
            if ($stmt) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Registro insertado correctamente', 
                    'id' => $db->getConnection()->insert_id
                ]);
            } else {
                throw new Exception('Error al insertar registro');
            }
            break;
            
        case 'update':
            if (!$primary_key) {
                throw new Exception('No hay Primary Key definida para actualizar');
            }
            
            $sets = [];
            $params = [];
            
            foreach ($campos_marcados as $campo) {
                if ($campo !== $primary_key) {
                    $sets[] = '`' . $campo . '` = ?';
                    $params[] = $_POST[$campo] ?? null;
                }
            }
            
            if (empty($sets)) {
                throw new Exception('No hay campos para actualizar');
            }
            
            $pkValue = $_POST[$primary_key] ?? null;
            if ($pkValue === null) {
                throw new Exception('Primary Key no especificada');
            }
            
            $params[] = $pkValue;
            $setsStr = implode(', ', $sets);
            
            $sql = "UPDATE `$tabla` SET $setsStr WHERE `$primary_key` = ?";
            $stmt = $db->query($sql, $params);
            
            if ($stmt) {
                echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente']);
            } else {
                throw new Exception('Error al actualizar registro');
            }
            break;
            
        case 'delete':
            if (!$primary_key) {
                throw new Exception('No hay Primary Key definida para eliminar');
            }
            
            $pkValue = $_POST[$primary_key] ?? null;
            if ($pkValue === null) {
                throw new Exception('Primary Key no especificada');
            }
            
            $sql = "DELETE FROM `$tabla` WHERE `$primary_key` = ?";
            $stmt = $db->query($sql, [$pkValue]);
            
            if ($stmt) {
                echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente']);
            } else {
                throw new Exception('Error al eliminar registro');
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
