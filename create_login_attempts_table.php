<?php
require_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    attempts INT NOT NULL DEFAULT 1,
    last_attempt DATETIME NOT NULL,
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $conn->query($sql);
    echo "Login attempts table created successfully";
} catch (Exception $e) {
    echo "Error creating login attempts table: " . $e->getMessage();
}
?> 