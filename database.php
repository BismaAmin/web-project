<?php
class Database {
    private $host = "127.0.0.1";
    private $port = "3306";
    private $db_name = "sizzle_share";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $e) {
            return null;
        }
        
        return $this->conn;
    }
}
?>