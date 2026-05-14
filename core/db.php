<?php
// Database Class for CRUD operations

class Database {
    private $conn;
    
    public function __construct() {
        require_once dirname(__DIR__) . '/config/db.php';
        $this->conn = $conn;
    }
    
    // Get connection
    public function getConnection() {
        return $this->conn;
    }
    
    // Execute query
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    // Prepare statement
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    // Fetch all rows
    public function fetchAll($sql) {
        $result = $this->conn->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    // Fetch single row
    public function fetch($sql) {
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }
    
    // Insert data
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_map(fn($v) => "'" . $this->conn->real_escape_string($v) . "'", array_values($data)));
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        return $this->conn->query($sql);
    }
    
    // Update data
    public function update($table, $data, $where) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = '" . $this->conn->real_escape_string($value) . "'";
        }
        $sql = "UPDATE $table SET " . implode(", ", $set) . " WHERE $where";
        return $this->conn->query($sql);
    }
    
    // Delete data
    public function delete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->conn->query($sql);
    }
    
    // Get last insert id
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    // Escape string
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}
?>
