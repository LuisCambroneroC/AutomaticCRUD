<?php
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
                <div class="content-section">
                    <p>En esta sección puedes configurar los parámetros de tu aplicación.</p>
                    <p>Opciones de configuración disponibles:</p>
                    <ul style="margin-left: 20px; margin-top: 10px; color: #7f8c8d;">
                        <li>Preferencias de usuario</li>
                        <li>Configuración del sistema</li>
                        <li>Gestión de notificaciones</li>
                    </ul>
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