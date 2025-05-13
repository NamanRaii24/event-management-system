<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$signup_status = null;
$stmt = $conn->prepare("SELECT status FROM signup_requests WHERE email = ?");
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $signup_status = $result->fetch_assoc()['status'];
} else {
    $signup_status = "Approved"; // User exists in users table, so approved
}
$stmt->close();

$registered_events = [];
if ($user['role'] === 'Student') {
    $stmt = $conn->prepare("SELECT e.*, r.status FROM events e JOIN registrations r ON e.id = r.event_id WHERE r.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $registered_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$created_events = [];
if ($user['role'] === 'Faculty') {
    $stmt = $conn->prepare("SELECT * FROM events WHERE created_by = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $created_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - AEG College Event Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/styles.css">
    <script src="./js/script.js" defer></script>
</head>
<body>
    <nav>
        <div class="logo">AEG College Event Portal</div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="index.php#events">Events</a></li>
            <li><a href="achievements.php">Achievements</a></li>
            <li><a href="contact.php">Contact Us</a></li>
            <li><a href="my_account.php">My Account</a></li>
            <li><a href="logout.php">Logout</a></li>
            <?php if ($user['role'] === 'Admin'): ?>
                <li><a href="admin.php">Admin Dashboard</a></li>
            <?php elseif ($user['role'] === 'Faculty'): ?>
                <li><a href="faculty_dashboard.php">Faculty Dashboard</a></li>
            <?php elseif ($user['role'] === 'Student'): ?>
                <li><a href="dashboard.php">My Dashboard</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <main class="container my-5">
        <h2 class="text-center mb-4">My Account</h2>

        <h4 class="mb-4">My Details</h4>
        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                <p><strong>College:</strong> <?php echo htmlspecialchars($user['college']); ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($user['course']); ?></p>
                <p><strong>Year:</strong> <?php echo htmlspecialchars($user['year']); ?></p>
                <p><strong>Section:</strong> <?php echo htmlspecialchars($user['section']); ?></p>
            </div>
        </div>

        <h4 class="mb-4">Signup Request Status</h4>
        <div class="alert alert-info">
            Your signup request is currently <strong><?php echo htmlspecialchars($signup_status); ?></strong>.
        </div>

        <?php if ($user['role'] === 'Student'): ?>
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
                                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="text-center mt-4">
                <a href="view_attendance.php" class="btn btn-primary">View Attendance</a>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] === 'Faculty'): ?>
            <h4 class="mb-4">My Created Events</h4>
            <?php if (empty($created_events)): ?>
                <p class="text-center">You have not created any events.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($created_events as $event): ?>
                        <div class="col-md-4 mb-4">
                            <div class="event-card shadow-sm">
                                <div class="card-body">
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p><span class="badge bg-primary"><?php echo htmlspecialchars($event['category'] ?: 'General'); ?></span></p>
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                                    <p><strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
                                    <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                                    <a href="attendance.php?id=<?php echo $event['id']; ?>" class="btn btn-primary mt-2">Mark Attendance</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer>
        <p>© AEG College Event Portal</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>