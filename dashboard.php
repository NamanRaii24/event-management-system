<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch events the user is registered for
$stmt = $conn->prepare("
    SELECT e.*, r.status 
    FROM events e 
    JOIN registrations r ON e.id = r.event_id 
    WHERE r.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$registered_events = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - AEG College Event Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Event Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="my_account.php">My Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">My Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_attendance.php">My Attendance</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Faculty'): ?>
                        <li class="nav-item"><a class="nav-link" href="faculty_dashboard.php">Faculty Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5">
        <h2 class="text-center mb-4">My Dashboard</h2>

        <!-- Registered Events -->
        <h4 class="mb-4">My Registered Events</h4>
        <?php if (empty($registered_events)): ?>
            <p class="text-center">You are not registered for any events.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($registered_events as $event): ?>
                    <div class="col-md-4 mb-4">
                        <div class="event-card shadow-sm">
                            <div class="card-body">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><span class="badge bg-primary"><?php echo htmlspecialchars($event['category'] ?: 'General'); ?></span></p>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
                                <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                                <p><strong>Status:</strong> <?php echo htmlspecialchars($event['status']); ?></p>
                                <?php
                                // Check if attendance is marked for this event
                                $attendance_stmt = $conn->prepare("SELECT status FROM event_attendance WHERE event_id = ? AND user_id = ?");
                                $attendance_stmt->bind_param("ii", $event['id'], $user_id);
                                $attendance_stmt->execute();
                                $attendance_result = $attendance_stmt->get_result();
                                $attendance = $attendance_result->fetch_assoc();
                                $attendance_stmt->close();
                                
                                if ($attendance): ?>
                                    <p><strong>Attendance:</strong> <span class="badge bg-<?php echo $attendance['status'] === 'Present' ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars($attendance['status']); ?></span></p>
                                <?php endif; ?>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer py-4 bg-light text-center">
        <span class="text-muted">© 2025 AEG College Event Portal</span>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>