<?php
require_once 'connection.php';

class User {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
      
        if (!$this->conn) {
            die("Connection failed: " . $this->conn->errorInfo());
        }
    }

    
    public function register($username, $password, $email) {
      
        $checkQuery = "SELECT COUNT(*) FROM users WHERE username = :username";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();
        $usernameExists = $checkStmt->fetchColumn() > 0;

        if ($usernameExists) {
            return false;  
        }

        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, password, email) VALUES (:username, :password, :email)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);

        
        if ($stmt->execute()) {
            return true;
        }

        return false;  
    }

    
    public function login($username, $password) {
        
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
           
            session_start();
            $_SESSION['user_id'] = $user['user_id'];  
            $_SESSION['username'] = $user['username'];  
            $_SESSION['email'] = $user['email'];  
            
            return $user;  
        }

        return false;  
    }

  
    public function logout() {
        session_start();
        session_unset();  
        session_destroy(); 
    }
}
?>
