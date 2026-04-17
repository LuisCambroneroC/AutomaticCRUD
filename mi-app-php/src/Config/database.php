<?php
/**
 * Clase para manejar la conexión a la base de datos MySQL
 * Utiliza los parámetros de conexión guardados en la sesión desde la página de Configuración
 */

class Database {
    private $connection;
    private $config;
    
    /**
     * Constructor - Obtiene la configuración de la sesión
     */
    public function __construct() {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Obtener configuración de la sesión
        $this->config = $_SESSION['db_config'] ?? null;
        
        if (!$this->config) {
            throw new Exception("No hay configuración de base de datos en la sesión. Por favor, configure la conexión en la página de Configuración.");
        }
        
        $this->connect();
    }
    
    /**
     * Establece la conexión a la base de datos
     */
    private function connect() {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $database = $this->config['database'];
        
        // Crear conexión usando MySQLi
        $this->connection = new mysqli($host, $username, $password, $database, (int)$port);
        
        // Verificar si hubo error en la conexión
        if ($this->connection->connect_error) {
            throw new Exception("Error de conexión: " . $this->connection->connect_error);
        }
        
        // Establecer charset a utf8
        $this->connection->set_charset("utf8mb4");
    }
    
    /**
     * Obtiene la instancia de conexión
     * @return mysqli
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Cierra la conexión a la base de datos
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Método para ejecutar consultas preparadas de forma segura
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros para la consulta preparada
     * @return mysqli_stmt|bool El statement o false en caso de error
     */
    public function query($sql, $params = []) {
        if (empty($params)) {
            return $this->connection->query($sql);
        }
        
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Obtiene todos los resultados de una consulta
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros para la consulta preparada
     * @return array Los resultados como array asociativo
     */
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        if ($result instanceof mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } elseif ($result instanceof mysqli_stmt) {
            $meta = $result->result_metadata();
            $rows = [];
            while ($row = $result->get_result()->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->close();
            return $rows;
        }
        
        return [];
    }
    
    /**
     * Obtiene un solo registro de una consulta
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros para la consulta preparada
     * @return array|null El resultado como array asociativo o null si no hay resultados
     */
    public function fetchOne($sql, $params = []) {
        $results = $this->fetchAll($sql, $params);
        return $results[0] ?? null;
    }
}