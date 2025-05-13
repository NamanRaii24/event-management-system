<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID.");
}

// Check if already registered
$check_query = "SELECT * FROM registrations WHERE user_id = ? AND event_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $user_id, $event_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "You are already registered for this event.";
    header("Location: event.php?id=$event_id");
    exit;
}

// Fetch event details
$event_query = "SELECT * FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

// Register the user
$insert_query = "INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, 'Pending')";
$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param("ii", $user_id, $event_id);

if ($insert_stmt->execute()) {
    $_SESSION['success'] = "Registration successful! Awaiting approval.";
} else {
    $_SESSION['error'] = "Error registering: " . $insert_stmt->error;
}

$insert_stmt->close();
$check_stmt->close();
$event_stmt->close();
header("Location: event.php?id=$event_id");
exit;
?>