<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $comments = filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'] ?? null;

    $stmt = $conn->prepare("INSERT INTO feedback (user_id, comments, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $comments);
    
    if ($stmt->execute()) {
        echo "Feedback submitted successfully!";
        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>