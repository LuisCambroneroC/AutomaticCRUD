<?php
session_start();
require_once __DIR__ . '/../src/Config/database.php';

$mensaje = '';
$error = '';
$tablas = [];
$campos = [];
$tabla_seleccionada = '';
$campos_marcados = [];

// Obtener configuración de la sesión
if (!isset($_SESSION['db_config'])) {
    $error = "No hay configuración de base de datos guardada. Por favor configura la conexión primero.";
} else {
    try {
        $db = new Database();
        
        // Obtener todas las tablas de la base de datos
        $tablas_data = $db->query("SHOW TABLES");
        $tablas = [];
        if ($tablas_data instanceof mysqli_result) {
            while ($row = $tablas_data->fetch_row()) {
                $tablas[] = $row[0];
            }
        } elseif ($tablas_data instanceof mysqli_stmt) {
            $result = $tablas_data->get_result();
            while ($row = $result->fetch_row()) {
                $tablas[] = $row[0];
            }
            $tablas_data->close();
        }
        
        // Si se ha seleccionado una tabla, obtener sus campos y guardar en sesión
        if (isset($_POST['tabla_seleccionada']) && !empty($_POST['tabla_seleccionada'])) {
            $tabla_seleccionada = $_POST['tabla_seleccionada'];
            
            // Guardar la tabla seleccionada en sesión inmediatamente
            $_SESSION['tabla_seleccionada'] = $tabla_seleccionada;
            
            // Obtener campos de la tabla seleccionada
            $campos_data = $db->query("SHOW COLUMNS FROM `" . $tabla_seleccionada . "`");
            $campos = [];
            if ($campos_data instanceof mysqli_result) {
                while ($row = $campos_data->fetch_assoc()) {
                    $campos[] = $row;
                }
            } elseif ($campos_data instanceof mysqli_stmt) {
                $result = $campos_data->get_result();
                while ($row = $result->fetch_assoc()) {
                    $campos[] = $row;
                }
                $campos_data->close();
            }
        }
        
        // Si se han marcado campos, guardar en sesión
        if (isset($_POST['guardar_campos']) && !empty($_POST['campos']) && !empty($_POST['tabla_seleccionada'])) {
            $tabla_seleccionada = $_POST['tabla_seleccionada'];
            $campos_marcados = $_POST['campos'];
            
            // Guardar en sesión explícitamente
            $_SESSION['tabla_seleccionada'] = $tabla_seleccionada;
            $_SESSION['campos_marcados'] = $campos_marcados;
            
            $mensaje = "Tabla y campos guardados correctamente en sesión.";
            
            // Recargar campos para mostrar los marcados
            $campos_data = $db->query("SHOW COLUMNS FROM `" . $tabla_seleccionada . "`");
            $campos = [];
            if ($campos_data instanceof mysqli_result) {
                while ($row = $campos_data->fetch_assoc()) {
                    $campos[] = $row;
                }
            } elseif ($campos_data instanceof mysqli_stmt) {
                $result = $campos_data->get_result();
                while ($row = $result->fetch_assoc()) {
                    $campos[] = $row;
                }
                $campos_data->close();
            }
            // Asegurar que $campos_marcados tenga los valores correctos de la sesión
            $campos_marcados = $_SESSION['campos_marcados'];
            
        } elseif (isset($_SESSION['tabla_seleccionada']) && isset($_SESSION['campos_marcados']) && !isset($_POST['guardar_campos'])) {
            // Cargar desde sesión si existe y no estamos procesando un guardado nuevo
            $tabla_seleccionada = $_SESSION['tabla_seleccionada'];
            $campos_marcados = $_SESSION['campos_marcados'] ?? [];
            
            // Obtener campos de la tabla seleccionada desde sesión
            if (!empty($tabla_seleccionada)) {
                $campos_data = $db->query("SHOW COLUMNS FROM `" . $tabla_seleccionada . "`");
                $campos = [];
                if ($campos_data instanceof mysqli_result) {
                    while ($row = $campos_data->fetch_assoc()) {
                        $campos[] = $row;
                    }
                } elseif ($campos_data instanceof mysqli_stmt) {
                    $result = $campos_data->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $campos[] = $row;
                    }
                    $campos_data->close();
                }
            }
        }
        
    } catch (Exception $e) {
        $error = "Error al conectar con la base de datos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuente - Configuración</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
            color: #007bff;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .campos-container {
            background-color: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .campo-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .campo-item:last-child {
            border-bottom: none;
        }
        .campo-item input[type="checkbox"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
        }
        .campo-item label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
            flex: 1;
        }
        .btn {
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .info-sesion {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            border: 1px solid #b3d9ff;
        }
        .info-sesion h3 {
            margin-top: 0;
            color: #0056b3;
        }
        .tag {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 5px 5px 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="nav-links">
        <a href="index.php">← Volver a Configuración</a>
        <a href="crud.php" style="font-weight: bold; color: #28a745; margin-left: 15px;">IR AL CRUD →</a>
    </div>
    
    <h1>Fuente de Datos</h1>
    
    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label for="tabla_seleccionada">Seleccionar Tabla:</label>
                <select name="tabla_seleccionada" id="tabla_seleccionada" onchange="this.form.submit()">
                    <option value="">-- Seleccione una tabla --</option>
                    <?php foreach ($tablas as $tabla): ?>
                        <option value="<?php echo htmlspecialchars($tabla); ?>" 
                                <?php echo ($tabla === $tabla_seleccionada) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tabla); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        
        <?php if (!empty($tabla_seleccionada) && !empty($campos)): ?>
            <div class="campos-container">
                <h2>Campos de la tabla: <?php echo htmlspecialchars($tabla_seleccionada); ?></h2>
                <form method="POST">
                    <input type="hidden" name="tabla_seleccionada" value="<?php echo htmlspecialchars($tabla_seleccionada); ?>">
                    
                    <?php foreach ($campos as $campo): ?>
                        <div class="campo-item">
                            <input type="checkbox" 
                                   name="campos[]" 
                                   value="<?php echo htmlspecialchars($campo['Field']); ?>" 
                                   id="campo_<?php echo htmlspecialchars($campo['Field']); ?>"
                                   <?php echo in_array($campo['Field'], $campos_marcados) ? 'checked' : ''; ?>>
                            <label for="campo_<?php echo htmlspecialchars($campo['Field']); ?>">
                                <?php echo htmlspecialchars($campo['Field']); ?> 
                                <small style="color: #666;">(<?php echo htmlspecialchars($campo['Type']); ?>)</small>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" name="guardar_campos" class="btn">Guardar Campos en Sesión</button>
                </form>
            </div>
            
            <?php if (!empty($campos_marcados)): ?>
                <div class="info-sesion">
                    <h3>Información guardada en sesión:</h3>
                    <p><strong>Tabla seleccionada:</strong> <?php echo htmlspecialchars($tabla_seleccionada); ?></p>
                    <p><strong>Campos marcados (<?php echo count($campos_marcados); ?>):</strong></p>
                    <div>
                        <?php foreach ($campos_marcados as $campo): ?>
                            <span class="tag"><?php echo htmlspecialchars($campo); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 20px;">
                        <a href="crud.php" class="btn">Ir al CRUD →</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php elseif (!empty($tabla_seleccionada)): ?>
            <div class="campos-container">
                <p>No se encontraron campos para esta tabla.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
