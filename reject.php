<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE registrations SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Registration rejected successfully.";
    } else {
        $_SESSION['error'] = "Error rejecting registration: " . $stmt->error;
    }
    $stmt->close();
    header("Location: admin.php");
    exit;
}
?>