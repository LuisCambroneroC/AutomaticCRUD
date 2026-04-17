<?php
session_start();
require_once __DIR__ . '/../src/Config/database.php';

$mensaje = '';
$error = '';
$tabla_seleccionada = '';
$campos_marcados = [];
$campos_info = [];
$registros = [];

// Verificar si hay configuración en sesión
if (!isset($_SESSION['db_config']) || !isset($_SESSION['tabla_seleccionada']) || !isset($_SESSION['campos_marcados'])) {
    $error = "No hay configuración de base de datos o tabla/campos seleccionados. Por favor configura la fuente de datos primero en <a href='fuente.php'>Fuente.php</a>.";
} else {
    try {
        $db = new Database();
        
        $tabla_seleccionada = $_SESSION['tabla_seleccionada'];
        $campos_marcados = $_SESSION['campos_marcados'];
        
        // Obtener información de los campos
        $campos_data = $db->query("SHOW COLUMNS FROM `" . $tabla_seleccionada . "`");
        if ($campos_data instanceof mysqli_result) {
            while ($row = $campos_data->fetch_assoc()) {
                if (in_array($row['Field'], $campos_marcados)) {
                    $campos_info[] = $row;
                }
            }
        } elseif ($campos_data instanceof mysqli_stmt) {
            $result = $campos_data->get_result();
            while ($row = $result->fetch_assoc()) {
                if (in_array($row['Field'], $campos_marcados)) {
                    $campos_info[] = $row;
                }
            }
            $campos_data->close();
        }
        
        // Obtener todos los registros de la tabla
        $campos_sql = implode(', ', array_map(function($c) {
            return '`' . $c . '`';
        }, $campos_marcados));
        
        $registros = $db->fetchAll("SELECT " . $campos_sql . " FROM `" . $tabla_seleccionada . "`");
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Determinar campo primary key (para edición/eliminación)
$primary_key = null;
foreach ($campos_info as $campo) {
    if ($campo['Key'] === 'PRI') {
        $primary_key = $campo['Field'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD - <?php echo htmlspecialchars($tabla_seleccionada); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1400px;
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
        .btn {
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .actions {
            white-space: nowrap;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group small {
            color: #666;
            font-size: 12px;
        }
        .code-container {
            background-color: #2d2d2d;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            overflow-x: auto;
        }
        .code-container h3 {
            color: #61dafb;
            margin-top: 0;
        }
        .code-container pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .code-container code {
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .tabs {
            margin-top: 30px;
        }
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #ddd;
        }
        .tab-button {
            padding: 12px 24px;
            background-color: #f1f1f1;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }
        .tab-button.active {
            background-color: #007bff;
            color: white;
        }
        .tab-content {
            display: none;
            padding: 20px;
            background-color: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }
        .tab-content.active {
            display: block;
        }
        .info-panel {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #b3d9ff;
        }
    </style>
</head>
<body>
    <div class="nav-links">
        <a href="index.php">← Configuración</a>
        <a href="fuente.php">← Fuente</a>
    </div>
    
    <h1>CRUD - Tabla: <?php echo htmlspecialchars($tabla_seleccionada); ?></h1>
    
    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php else: ?>
        
        <div class="info-panel">
            <h3>Información del CRUD</h3>
            <p><strong>Tabla:</strong> <?php echo htmlspecialchars($tabla_seleccionada); ?></p>
            <p><strong>Campos seleccionados:</strong> <?php echo implode(', ', array_map('htmlspecialchars', $campos_marcados)); ?></p>
            <?php if ($primary_key): ?>
                <p><strong>Primary Key:</strong> <?php echo htmlspecialchars($primary_key); ?></p>
            <?php else: ?>
                <p style="color: #dc3545;"><strong>Advertencia:</strong> No se encontró una Primary Key. La edición y eliminación pueden no funcionar correctamente.</p>
            <?php endif; ?>
        </div>
        
        <button class="btn btn-success" onclick="openModal('insert')">+ Nuevo Registro</button>
        
        <table id="crudTable">
            <thead>
                <tr>
                    <?php foreach ($campos_info as $campo): ?>
                        <th><?php echo htmlspecialchars($campo['Field']); ?></th>
                    <?php endforeach; ?>
                    <th class="actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <?php foreach ($campos_info as $campo): ?>
                            <td><?php echo htmlspecialchars($registro[$campo['Field']] ?? ''); ?></td>
                        <?php endforeach; ?>
                        <td class="actions">
                            <?php if ($primary_key): ?>
                                <button class="btn btn-warning" onclick="editRecord(<?php echo htmlspecialchars(json_encode($registro)); ?>)">Editar</button>
                                <button class="btn btn-danger" onclick="deleteRecord('<?php echo htmlspecialchars($registro[$primary_key]); ?>')">Eliminar</button>
                            <?php else: ?>
                                <span style="color: #dc3545;">Sin PK</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Modal para Insertar/Editar -->
        <div id="recordModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="modalTitle">Nuevo Registro</h2>
                <form id="recordForm" method="POST" action="crud_action.php">
                    <input type="hidden" name="action" id="formAction" value="insert">
                    <input type="hidden" name="tabla" value="<?php echo htmlspecialchars($tabla_seleccionada); ?>">
                    <?php if ($primary_key): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($primary_key); ?>" id="pkValue" value="">
                    <?php endif; ?>
                    
                    <?php foreach ($campos_info as $campo): ?>
                        <?php if ($campo['Field'] !== $primary_key || !$primary_key): ?>
                            <div class="form-group">
                                <label for="<?php echo htmlspecialchars($campo['Field']); ?>">
                                    <?php echo htmlspecialchars($campo['Field']); ?>
                                </label>
                                <?php
                                $is_nullable = $campo['Null'] === 'YES';
                                $type = $campo['Type'];
                                $extra = $campo['Extra'];
                                
                                // Determinar tipo de input
                                $input_type = 'text';
                                if (strpos($type, 'int') !== false) {
                                    $input_type = 'number';
                                } elseif (strpos($type, 'date') !== false) {
                                    $input_type = 'date';
                                } elseif (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
                                    $input_type = 'datetime-local';
                                } elseif (strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
                                    $input_type = 'number';
                                } elseif (strpos($type, 'text') !== false || strpos($type, 'blob') !== false) {
                                    $input_type = 'textarea';
                                }
                                ?>
                                <?php if ($input_type === 'textarea'): ?>
                                    <textarea name="<?php echo htmlspecialchars($campo['Field']); ?>" 
                                              id="<?php echo htmlspecialchars($campo['Field']); ?>"
                                              <?php echo $is_nullable ? '' : 'required'; ?>></textarea>
                                <?php else: ?>
                                    <input type="<?php echo $input_type; ?>" 
                                           name="<?php echo htmlspecialchars($campo['Field']); ?>" 
                                           id="<?php echo htmlspecialchars($campo['Field']); ?>"
                                           <?php echo $is_nullable ? '' : 'required'; ?>>
                                <?php endif; ?>
                                <small>Tipo: <?php echo htmlspecialchars($type); ?> <?php echo $extra ? '(' . htmlspecialchars($extra) . ')' : ''; ?></small>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn btn-success" id="submitBtn">Guardar</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                </form>
            </div>
        </div>
        
        <!-- Sección de Código Generado -->
        <div class="tabs">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="showTab('php')">PHP</button>
                <button class="tab-button" onclick="showTab('html')">HTML</button>
                <button class="tab-button" onclick="showTab('js')">JavaScript</button>
            </div>
            
            <div id="php" class="tab-content active">
                <div class="code-container">
                    <h3>código PHP (crud_action.php)</h3>
                    <pre><code><?php echo htmlspecialchars(generatePHPCode($tabla_seleccionada, $campos_info, $primary_key)); ?></code></pre>
                </div>
            </div>
            
            <div id="html" class="tab-content">
                <div class="code-container">
                    <h3>Código HTML</h3>
                    <pre><code><?php echo htmlspecialchars(generateHTMLCode($tabla_seleccionada, $campos_info, $primary_key)); ?></code></pre>
                </div>
            </div>
            
            <div id="js" class="tab-content">
                <div class="code-container">
                    <h3>Código JavaScript</h3>
                    <pre><code><?php echo htmlspecialchars(generateJSCode()); ?></code></pre>
                </div>
            </div>
        </div>
        
    <?php endif; ?>

    <script>
        function openModal(mode) {
            const modal = document.getElementById('recordModal');
            const title = document.getElementById('modalTitle');
            const formAction = document.getElementById('formAction');
            const submitBtn = document.getElementById('submitBtn');
            
            modal.style.display = 'block';
            
            if (mode === 'insert') {
                title.textContent = 'Nuevo Registro';
                formAction.value = 'insert';
                submitBtn.textContent = 'Guardar';
                document.getElementById('recordForm').reset();
                <?php if ($primary_key): ?>
                document.getElementById('pkValue').value = '';
                <?php endif; ?>
            }
        }
        
        function closeModal() {
            document.getElementById('recordModal').style.display = 'none';
        }
        
        function editRecord(record) {
            const modal = document.getElementById('recordModal');
            const title = document.getElementById('modalTitle');
            const formAction = document.getElementById('formAction');
            const submitBtn = document.getElementById('submitBtn');
            
            modal.style.display = 'block';
            title.textContent = 'Editar Registro';
            formAction.value = 'update';
            submitBtn.textContent = 'Actualizar';
            
            <?php if ($primary_key): ?>
            document.getElementById('pkValue').value = record.<?php echo $primary_key; ?>;
            <?php endif; ?>
            
            <?php foreach ($campos_info as $campo): ?>
                <?php if ($campo['Field'] !== $primary_key || !$primary_key): ?>
            document.getElementById('<?php echo $campo['Field']; ?>').value = record.<?php echo $campo['Field']; ?> || '';
                <?php endif; ?>
            <?php endforeach; ?>
        }
        
        function deleteRecord(pkValue) {
            if (confirm('¿Está seguro de eliminar este registro?')) {
                fetch('crud_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete&tabla=<?php echo urlencode($tabla_seleccionada); ?>&<?php echo urlencode($primary_key); ?>=' + encodeURIComponent(pkValue)
                })
                .then(response => response.text())
                .then(data => {
                    alert('Registro eliminado correctamente');
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el registro');
                });
            }
        }
        
        function showTab(tabName) {
            const tabs = document.querySelectorAll('.tab-content');
            const buttons = document.querySelectorAll('.tab-button');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            buttons.forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('recordModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php
function generatePHPCode($tabla, $campos, $primaryKey) {
    $codigo = '<?php
session_start();
require_once __DIR__ . \'/../src/Config/database.php\';

header(\'Content-Type: application/json\');

try {
    $db = new Database();
    $accion = $_POST[\'action\'] ?? \'\';
    $tabla = $_POST[\'tabla\'] ?? \'' . addslashes($tabla) . '\';
    
    switch ($accion) {
        case \'insert\':
            ' . generateInsertCode($campos, $primaryKey) . '
            break;
            
        case \'update\':
            ' . generateUpdateCode($campos, $primaryKey) . '
            break;
            
        case \'delete\':
            ' . generateDeleteCode($tabla, $primaryKey) . '
            break;
            
        default:
            echo json_encode([\'success\' => false, \'message\' => \'Acción no válida\']);
    }
} catch (Exception $e) {
    echo json_encode([\'success\' => false, \'message\' => $e->getMessage()]);
}
?>';
    return $codigo;
}

function generateInsertCode($campos, $primaryKey) {
    $fields = [];
    $placeholders = [];
    $params = [];
    
    foreach ($campos as $campo) {
        if ($campo['Field'] !== $primaryKey || empty($primaryKey)) {
            $fields[] = '`' . $campo['Field'] . '`';
            $placeholders[] = '?';
            $params[] = '$_' . "POST['" . $campo['Field'] . "']";
        }
    }
    
    $fieldsStr = implode(', ', $fields);
    $placeholdersStr = implode(', ', $placeholders);
    $paramsStr = implode(', ', $params);
    
    return '$sql = "INSERT INTO `$tabla` (' . $fieldsStr . ') VALUES (' . $placeholdersStr . ')";
    $stmt = $db->query($sql, [' . $paramsStr . ']);
    if ($stmt) {
        echo json_encode([\'success\' => true, \'message\' => \'Registro insertado correctamente\', \'id\' => $db->getConnection()->insert_id]);
    } else {
        echo json_encode([\'success\' => false, \'message\' => \'Error al insertar registro\']);
    }';
}

function generateUpdateCode($campos, $primaryKey) {
    if (!$primaryKey) {
        return 'echo json_encode([\'success\' => false, \'message\' => \'No hay Primary Key definida\']);';
    }
    
    $sets = [];
    $params = [];
    
    foreach ($campos as $campo) {
        if ($campo['Field'] !== $primaryKey) {
            $sets[] = '`' . $campo['Field'] . '` = ?';
            $params[] = '$_' . "POST['" . $campo['Field'] . "']";
        }
    }
    
    $setsStr = implode(', ', $sets);
    $paramsStr = implode(', ', $params);
    $pkParam = '$_' . "POST['" . $primaryKey . "']";
    
    return '$sql = "UPDATE `$tabla` SET ' . $setsStr . ' WHERE `' . $primaryKey . '` = ?";
    $params = [' . $paramsStr . ', ' . $pkParam . '];
    $stmt = $db->query($sql, $params);
    if ($stmt) {
        echo json_encode([\'success\' => true, \'message\' => \'Registro actualizado correctamente\']);
    } else {
        echo json_encode([\'success\' => false, \'message\' => \'Error al actualizar registro\']);
    }';
}

function generateDeleteCode($tabla, $primaryKey) {
    if (!$primaryKey) {
        return 'echo json_encode([\'success\' => false, \'message\' => \'No hay Primary Key definida\']);';
    }
    
    $pkParam = '$_' . "POST['" . $primaryKey . "']";
    
    return '$sql = "DELETE FROM `$tabla` WHERE `' . $primaryKey . '` = ?";
    $stmt = $db->query($sql, [' . $pkParam . ']);
    if ($stmt) {
        echo json_encode([\'success\' => true, \'message\' => \'Registro eliminado correctamente\']);
    } else {
        echo json_encode([\'success\' => false, \'message\' => \'Error al eliminar registro\']);
    }';
}

function generateHTMLCode($tabla, $campos, $primaryKey) {
    $html = '<!-- Formulario para Insertar/Editar -->
<div class="modal" id="recordModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Nuevo Registro</h2>
        <form id="recordForm" method="POST" action="crud_action.php">
            <input type="hidden" name="action" id="formAction" value="insert">
            <input type="hidden" name="tabla" value="' . htmlspecialchars($tabla) . '">';
    
    if ($primaryKey) {
        $html .= '
            <input type="hidden" name="' . htmlspecialchars($primaryKey) . '" id="pkValue" value="">';
    }
    
    foreach ($campos as $campo) {
        if ($campo['Field'] !== $primaryKey || !$primaryKey) {
            $isRequired = $campo['Null'] === 'NO' && $campo['Extra'] !== 'auto_increment';
            $html .= '
            <div class="form-group">
                <label for="' . htmlspecialchars($campo['Field']) . '">' . htmlspecialchars($campo['Field']) . '</label>
                <input type="text" name="' . htmlspecialchars($campo['Field']) . '" id="' . htmlspecialchars($campo['Field']) . '"' . ($isRequired ? ' required' : '') . '>
                <small>Tipo: ' . htmlspecialchars($campo['Type']) . '</small>
            </div>';
        }
    }
    
    $html .= '
            <button type="submit" class="btn btn-success" id="submitBtn">Guardar</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
        </form>
    </div>
</div>

<!-- Tabla de Registros -->
<table id="crudTable">
    <thead>
        <tr>';
    
    foreach ($campos as $campo) {
        $html .= '
            <th>' . htmlspecialchars($campo['Field']) . '</th>';
    }
    
    $html .= '
            <th class="actions">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <!-- Los registros se cargarán dinámicamente -->
    </tbody>
</table>';
    
    return $html;
}

function generateJSCode() {
    return '// Abrir modal para nuevo registro
function openModal(mode) {
    const modal = document.getElementById(\'recordModal\');
    const title = document.getElementById(\'modalTitle\');
    const formAction = document.getElementById(\'formAction\');
    
    modal.style.display = \'block\';
    
    if (mode === \'insert\') {
        title.textContent = \'Nuevo Registro\';
        formAction.value = \'insert\';
        document.getElementById(\'recordForm\').reset();
    }
}

// Cerrar modal
function closeModal() {
    document.getElementById(\'recordModal\').style.display = \'none\';
}

// Editar registro
function editRecord(record) {
    const modal = document.getElementById(\'recordModal\');
    const title = document.getElementById(\'modalTitle\');
    const formAction = document.getElementById(\'formAction\');
    
    modal.style.display = \'block\';
    title.textContent = \'Editar Registro\';
    formAction.value = \'update\';
    
    // Establecer valores del formulario
    document.getElementById(\'pkValue\').value = record.id;
    // ... establecer otros campos
}

// Eliminar registro
function deleteRecord(pkValue) {
    if (confirm(\'¿Está seguro de eliminar este registro?\')) {
        fetch(\'crud_action.php\', {
            method: \'POST\',
            headers: {
                \'Content-Type\': \'application/x-www-form-urlencoded\',
            },
            body: \'action=delete&tabla=' . htmlspecialchars($tabla_seleccionada ?? 'tabla') . '&id=\' + encodeURIComponent(pkValue)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(\'Registro eliminado correctamente\');
                location.reload();
            } else {
                alert(\'Error: \' + data.message);
            }
        })
        .catch(error => {
            console.error(\'Error:\', error);
            alert(\'Error al eliminar el registro\');
        });
    }
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById(\'recordModal\');
    if (event.target === modal) {
        closeModal();
    }
};';
}
?>
