<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Faculty') {
    header("Location: login.php");
    exit;
}

$faculty_id = $_SESSION['user_id'];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID.");
}

// Fetch event details
$event_query = "SELECT * FROM events WHERE id = ? AND created_by = ?";
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("ii", $event_id, $faculty_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    die("Event not found or you do not have permission to manage this event.");
}

// Check if today is the event day
$event_date = date('Y-m-d', strtotime($event['date']));
$today = date('Y-m-d');
if ($event_date !== $today) {
    die("Attendance can only be marked on the event day ($event_date).");
}

// Fetch registered students
$students_query = "SELECT r.user_id, u.name, r.status, ea.status as attendance_status 
                  FROM registrations r 
                  JOIN users u ON r.user_id = u.id 
                  LEFT JOIN event_attendance ea ON ea.event_id = r.event_id AND ea.user_id = r.user_id 
                  WHERE r.event_id = ? AND r.status = 'Approved'";
$students_stmt = $conn->prepare($students_query);
$students_stmt->bind_param("i", $event_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $attendance = $_POST['attendance'] ?? [];
    
    foreach ($attendance as $user_id => $status) {
        // Check if attendance already exists
        $check_query = "SELECT * FROM event_attendance WHERE event_id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $event_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing record
            $update_query = "UPDATE event_attendance SET status = ?, marked_on = CURDATE() WHERE event_id = ? AND user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sii", $status, $event_id, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert new record
            $insert_query = "INSERT INTO event_attendance (event_id, user_id, status, marked_on) VALUES (?, ?, ?, CURDATE())";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iis", $event_id, $user_id, $status);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
    $_SESSION['success'] = "Attendance marked successfully!";
    header("Location: attendance.php?id=$event_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - AEG College Event Portal</title>
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
                    <li class="nav-item"><a class="nav-link" href="faculty_dashboard.php">Faculty Dashboard</a></li>
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
                        <h2 class="mb-0">Mark Attendance for <?php echo htmlspecialchars($event['title']); ?></h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="event-details mb-4">
                            <h4>Event Details</h4>
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($event['time'])); ?></p>
                            <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                        </div>

                        <form method="POST" class="attendance-form">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Registration Status</th>
                                            <th>Attendance Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($student = $students_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td><span class="badge bg-success"><?php echo $student['status']; ?></span></td>
                                                <td>
                                                    <select name="attendance[<?php echo $student['user_id']; ?>]" class="form-select">
                                                        <option value="Present" <?php echo $student['attendance_status'] === 'Present' ? 'selected' : ''; ?>>Present</option>
                                                        <option value="Absent" <?php echo $student['attendance_status'] === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-4">
                                <a href="faculty_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                                <button type="submit" class="btn btn-primary">Save Attendance</button>
                            </div>
                        </form>
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