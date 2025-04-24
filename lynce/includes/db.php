<?php
require_once 'config.php';

class Database {
    private $conn;
    private static $instance = null;

    private function __construct() {
        try {
            // Create database directory if it doesn't exist
            if (!file_exists(DB_DIR)) {
                mkdir(DB_DIR, 0777, true);
            }

            $this->conn = new PDO("sqlite:" . DB_PATH);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable foreign key support
            $this->conn->exec('PRAGMA foreign_keys = ON;');
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
            die();
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
