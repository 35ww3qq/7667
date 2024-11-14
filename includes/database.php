<?php
class Database {
    private static $instance = null;
    private $connection;
    private $queries = [];
    
    private function __construct() {
        try {
            if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
                throw new Exception('Database configuration constants are not defined');
            }

            $this->connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset('utf8mb4');
            $this->connection->query("SET time_zone = '+03:00'"); // Türkiye saat dilimi
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your configuration.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            try {
                self::$instance = new self();
            } catch (Exception $e) {
                error_log("Database Instance Error: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$instance;
    }
    
    public function getConnection() {
        if (!$this->connection || !$this->connection->ping()) {
            $this->__construct();
        }
        return $this->connection;
    }
    
    public function prepare($query) {
        try {
            error_log("Preparing query: " . $query);
            
            $stmt = $this->connection->prepare($query);
            if ($stmt === false) {
                $error = $this->connection->error;
                error_log("Prepare Error: " . $error . " in query: " . $query);
                throw new Exception("Prepare failed: " . $error);
            }
            return $stmt;
        } catch (Exception $e) {
            error_log("Prepare Exception: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function query($sql) {
        try {
            $start = microtime(true);
            $result = $this->connection->query($sql);
            $duration = microtime(true) - $start;
            
            if (!$result) {
                throw new Exception("Query failed: " . $this->connection->error);
            }
            
            $this->queries[] = [
                'sql' => $sql,
                'duration' => $duration,
                'timestamp' => date('Y-m-d H:i:s'),
                'error' => null
            ];
            
            return $result;
        } catch (Exception $e) {
            $this->queries[] = [
                'sql' => $sql,
                'duration' => microtime(true) - $start,
                'timestamp' => date('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ];
            error_log("Query Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function beginTransaction() {
        try {
            return $this->connection->begin_transaction();
        } catch (Exception $e) {
            error_log("Transaction Begin Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function commit() {
        try {
            return $this->connection->commit();
        } catch (Exception $e) {
            error_log("Transaction Commit Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function rollback() {
        try {
            return $this->connection->rollback();
        } catch (Exception $e) {
            error_log("Transaction Rollback Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getQueryLog() {
        return $this->queries;
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
    
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }
    
    public function multiQuery($sql) {
        try {
            if (!$this->connection->multi_query($sql)) {
                throw new Exception("Multi query failed: " . $this->connection->error);
            }
            
            $results = [];
            do {
                if ($result = $this->connection->store_result()) {
                    $results[] = $result->fetch_all(MYSQLI_ASSOC);
                    $result->free();
                }
            } while ($this->connection->next_result());
            
            return $results;
        } catch (Exception $e) {
            error_log("Multi Query Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function close() {
        if ($this->connection) {
            try {
                $this->connection->close();
            } catch (Exception $e) {
                error_log("Close Connection Error: " . $e->getMessage());
                throw $e;
            }
        }
    }
    
    public function __destruct() {
        $this->close();
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}