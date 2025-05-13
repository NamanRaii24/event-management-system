<?php
session_start();
include 'db.php';

// Check if event ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$event_id = (int)$_GET['id'];

// Fetch event details
$stmt = $conn->prepare("SELECT e.*, u.name AS faculty_name FROM events e LEFT JOIN users u ON e.created_by = u.id WHERE e.id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if (isset($_POST['register'])) {
        $user_id = $_SESSION['user_id'];

        // Check if the user is already registered or has a pending request
        $stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Register the user for the event with pending status
            $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, 'Pending')");
            $stmt->bind_param("ii", $user_id, $event_id);
            $stmt->execute();
            $stmt->close();
            $success = "Your registration request has been sent for approval.";
        } else {
            $error = "You have already submitted a registration request for this event.";
        }
    } elseif (isset($_POST['feedback'])) {
        $comments = filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING);
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO feedback (user_id, event_id, comments, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $event_id, $comments);
        $stmt->execute();
        $stmt->close();

        $feedback_success = "Feedback submitted successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details - <?php echo htmlspecialchars($event['title']); ?></title>
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
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="my_account.php">My Account</a></li>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">My Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin.php">Admin Dashboard</a></li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Faculty'): ?>
                            <li class="nav-item"><a class="nav-link" href="faculty_dashboard.php">Faculty Dashboard</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5">
        <h2 class="text-center mb-4"><?php echo htmlspecialchars($event['title']); ?></h2>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                    <?php if ($event['image']): ?>
                        <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="card-img-top">
                    <?php else: ?>
                        <div class="card-img-placeholder">No Image Available</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <p><span class="badge bg-primary"><?php echo htmlspecialchars($event['category'] ?: 'General'); ?></span></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
                        <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                        <?php if ($event['faculty_name']): ?>
                            <p><strong>Faculty:</strong> <?php echo htmlspecialchars($event['faculty_name']); ?></p>
                        <?php endif; ?>

                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php elseif (isset($error)): ?>
                            <div class="alert alert-warning"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <p class="text-center"><a href="login.php" class="btn btn-primary">Log in to register</a></p>
                        <?php elseif ($_SESSION['role'] === 'Student'): ?>
                            <form method="POST">
                                <input type="hidden" name="register" value="1">
                                <button type="submit" class="btn btn-primary">Register for Event</button>
                            </form>
                        <?php endif; ?>

                        <!-- Feedback Form -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <h4 class="mt-4">Submit Feedback</h4>
                            <?php if (isset($feedback_success)): ?>
                                <div class="alert alert-success"><?php echo $feedback_success; ?></div>
                            <?php endif; ?>
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="feedback" value="1">
                                <div class="mb-3">
                                    <label for="comments" class="form-label">Your Feedback:</label>
                                    <textarea id="comments" name="comments" class="form-control" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Feedback</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer py-4 bg-light text-center">
        <span class="text-muted">© 2025 AEG College Event Portal</span>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>