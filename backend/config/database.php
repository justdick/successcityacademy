<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'student_management';
    private $username = 'root';
    private $password = '';
    private $conn = null;

    /**
     * Create database connection
     * @return PDO|null Database connection or null on failure
     */
    public function connect() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            return $this->conn;
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your database configuration.");
        }
    }

    /**
     * Get the current connection
     * @return PDO|null
     */
    public function getConnection() {
        return $this->conn;
    }
}
