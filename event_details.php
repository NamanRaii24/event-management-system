<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$event_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = 'pending'");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - Hansraj College</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="bg-primary text-white text-center py-4">
        <h1><?php echo htmlspecialchars($event['title']); ?></h1>
    </header>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3>Event Details</h3>
                        <p><?php echo htmlspecialchars($event['description']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                        <p><strong>Time:</strong> <?php echo $event['time']; ?></p>
                        <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST">
                        <button type="submit" class="btn btn-primary btn-block">Register Now</button>
                    </form>
                <?php else: ?>
                    <p><a href="login.php" class="btn btn-primary btn-block">Log in to Register</a></p>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary btn-block mt-2">Back to Events</a>
            </div>
        </div>
    </div>
    <footer class="footer mt-5 py-3 bg-light text-center">
        <span class="text-muted">© 2025 AEG College Event Portal</span>
    </footer>
</body>
</html>