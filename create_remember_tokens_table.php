<?php
require_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $conn->query($sql);
    echo "Remember tokens table created successfully";
} catch (Exception $e) {
    echo "Error creating remember tokens table: " . $e->getMessage();
}
?> 