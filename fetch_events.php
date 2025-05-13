<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// Log to a file for debugging
file_put_contents('fetch_events_log.txt', "Fetching events at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

$where = "WHERE date >= CURDATE()";
$params = [];
$types = "";

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where .= " AND category = ?";
    $params[] = $_GET['category'];
    $types .= "s";
}

$query = "SELECT id, title, description, date, time, venue, category FROM events $where ORDER BY date ASC";
$stmt = $conn->prepare($query);

if (!$stmt) {
    file_put_contents('fetch_events_log.txt', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
    die(json_encode(['error' => 'Database prepare error: ' . $conn->error]));
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    file_put_contents('fetch_events_log.txt', "Execute failed: " . $stmt->error . "\n", FILE_APPEND);
    die(json_encode(['error' => 'Database execute error: ' . $stmt->error]));
}

$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);

file_put_contents('fetch_events_log.txt', "Events fetched: " . json_encode($events) . "\n", FILE_APPEND);

echo json_encode($events);
$stmt->close();
?>