<?php
// Iniciar sesión
session_start();

// Manejar el guardado de la configuración de conexión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_configuracion'])) {
    $_SESSION['db_config'] = [
        'host' => $_POST['host'] ?? '',
        'port' => $_POST['port'] ?? '',
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? '',
        'database' => $_POST['database'] ?? ''
    ];
    $mensaje_exito = "Configuración de conexión guardada correctamente.";
}

// Determinar la página actual
$pagina_actual = isset($_GET['page']) ? $_GET['page'] : 'principal';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Aplicación PHP</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        /* Menú lateral */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #34495e;
        }

        .sidebar nav ul {
            list-style: none;
        }

        .sidebar nav ul li {
            margin-bottom: 10px;
        }

        .sidebar nav ul li a {
            display: block;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar nav ul li a:hover {
            background-color: #34495e;
        }

        .sidebar nav ul li a.active {
            background-color: #3498db;
        }

        /* Contenido principal */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            flex: 1;
            background-color: white;
            min-height: 100vh;
        }

        .main-content h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .main-content p {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .content-section {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Estilos para el formulario de configuración */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }

        .btn-guardar {
            background-color: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-guardar:hover {
            background-color: #2980b9;
        }

        .mensaje-exito {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }

        .config-info {
            background-color: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 14px;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <!-- Menú lateral -->
    <aside class="sidebar">
        <h2>Mi App PHP</h2>
        <nav>
            <ul>
                <li>
                    <a href="?page=principal" class="<?php echo $pagina_actual === 'principal' ? 'active' : ''; ?>">
                        📋 Principal
                    </a>
                </li>
                <li>
                    <a href="?page=configuracion" class="<?php echo $pagina_actual === 'configuracion' ? 'active' : ''; ?>">
                        ⚙️ Configuración
                    </a>
                </li>
                <li>
                    <a href="fuente.php" class="<?php echo $pagina_actual === 'fuente' ? 'active' : ''; ?>">
                        📊 Fuente
                    </a>
                </li>
                <li>
                    <a href="?page=acerca_de" class="<?php echo $pagina_actual === 'acerca_de' ? 'active' : ''; ?>">
                        ℹ️ Acerca de
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Contenido principal -->
    <main class="main-content">
        <?php
        switch($pagina_actual) {
            case 'principal':
                ?>
                <h1>Página Principal</h1>
                <div class="content-section">
                    <p>Bienvenido a la aplicación. Esta es la página principal donde encontrarás la información más importante.</p>
                    <p>Aquí puedes mostrar dashboards, estadísticas, o cualquier contenido relevante para el usuario.</p>
                </div>
                <?php
                break;

            case 'configuracion':
                ?>
                <h1>Configuración</h1>
                
                <?php if (isset($mensaje_exito)): ?>
                    <div class="mensaje-exito">
                        <?php echo htmlspecialchars($mensaje_exito); ?>
                    </div>
                <?php endif; ?>

                <div class="content-section">
                    <p>En esta sección puedes configurar los parámetros de tu aplicación.</p>
                    
                    <h3 style="margin-top: 20px; margin-bottom: 15px; color: #2c3e50;">🔌 Conexión</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="guardar_configuracion" value="1">
                        
                        <div class="form-group">
                            <label for="host">Host:</label>
                            <input type="text" id="host" name="host" 
                                   value="<?php echo htmlspecialchars($_SESSION['db_config']['host'] ?? 'localhost'); ?>" 
                                   placeholder="Ej: localhost" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="port">Puerto:</label>
                            <input type="number" id="port" name="port" 
                                   value="<?php echo htmlspecialchars($_SESSION['db_config']['port'] ?? '3306'); ?>" 
                                   placeholder="Ej: 3306" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Usuario:</label>
                            <input type="text" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_SESSION['db_config']['username'] ?? ''); ?>" 
                                   placeholder="Ej: root" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input type="password" id="password" name="password" 
                                   value="<?php echo htmlspecialchars($_SESSION['db_config']['password'] ?? ''); ?>" 
                                   placeholder="Contraseña de la base de datos">
                        </div>
                        
                        <div class="form-group">
                            <label for="database">Nombre de la Base de Datos:</label>
                            <input type="text" id="database" name="database" 
                                   value="<?php echo htmlspecialchars($_SESSION['db_config']['database'] ?? ''); ?>" 
                                   placeholder="Ej: mi_base_de_datos" required>
                        </div>
                        
                        <button type="submit" class="btn-guardar">Guardar Configuración</button>
                    </form>

                    <?php if (isset($_SESSION['db_config']) && !empty($_SESSION['db_config']['host'])): ?>
                        <div class="config-info">
                            <strong>Configuración actual guardada en sesión:</strong><br>
                            Host: <?php echo htmlspecialchars($_SESSION['db_config']['host']); ?> | 
                            Puerto: <?php echo htmlspecialchars($_SESSION['db_config']['port']); ?> | 
                            Usuario: <?php echo htmlspecialchars($_SESSION['db_config']['username']); ?> | 
                            Base de datos: <?php echo htmlspecialchars($_SESSION['db_config']['database']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                break;

            case 'acerca_de':
                ?>
                <h1>Acerca de</h1>
                <div class="content-section">
                    <p>Esta aplicación fue desarrollada utilizando PHP, HTML y JavaScript.</p>
                    <p><strong>Versión:</strong> 1.0.0</p>
                    <p><strong>Tecnologías:</strong></p>
                    <ul style="margin-left: 20px; margin-top: 10px; color: #7f8c8d;">
                        <li>PHP - Backend</li>
                        <li>HTML5 - Estructura</li>
                        <li>JavaScript - Interactividad</li>
                        <li>CSS3 - Estilos</li>
                    </ul>
                </div>
                <?php
                break;

            default:
                ?>
                <h1>Página no encontrada</h1>
                <div class="content-section">
                    <p>La página que buscas no existe. Por favor, selecciona una opción del menú.</p>
                </div>
                <?php
                break;
        }
        ?>
    </main>

    <script src="js/app.js"></script>
</body>
</html>