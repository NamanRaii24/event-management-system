<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$attendance_query = "SELECT e.title, e.date, ea.status 
                     FROM event_attendance ea 
                     JOIN events e ON ea.event_id = e.id 
                     WHERE ea.user_id = ? 
                     ORDER BY e.date DESC";
$attendance_stmt = $conn->prepare($attendance_query);
$attendance_stmt->bind_param("i", $user_id);

if (!$attendance_stmt->execute()) {
    $error = "Error fetching attendance: " . $attendance_stmt->error;
} else {
    $attendance_result = $attendance_stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - AEG College Event Portal</title>
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
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">My Attendance Records</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($attendance_result) && $attendance_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Event Title</th>
                                            <th>Date</th>
                                            <th>Attendance Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($record = $attendance_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['title']); ?></td>
                                                <td><?php echo date('F j, Y', strtotime($record['date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $record['status'] === 'Present' ? 'success' : 'danger'; ?>">
                                                        <?php echo htmlspecialchars($record['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <p class="text-muted">No attendance records found. Register for events and wait for faculty to mark attendance.</p>
                                <a href="index.php#events" class="btn btn-primary">Browse Events</a>
                            </div>
                        <?php endif; ?>

                        <div class="text-end mt-4">
                            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer py-4 bg-light text-center">
        <span class="text-muted">© 2025 AEG College Event Portal</span>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$attendance_stmt->close();
?>