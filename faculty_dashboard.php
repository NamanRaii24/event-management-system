<?php
session_start();
include 'db.php';

// Check if user is logged in and is a faculty
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Faculty') {
    header("Location: index.php");
    exit();
}

// Handle event deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];

    // Check if the event belongs to this faculty
    $stmt = $conn->prepare("SELECT created_by FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $stmt->close();

    if ($event['created_by'] == $_SESSION['user_id']) {
        // Delete registrations for this event first
        $stmt = $conn->prepare("DELETE FROM registrations WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $stmt->close();

        // Delete the event
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: faculty_dashboard.php");
    exit();
}

// Handle registration approval
if (isset($_GET['approve_registration']) && is_numeric($_GET['approve_registration'])) {
    $registration_id = (int)$_GET['approve_registration'];
    $stmt = $conn->prepare("UPDATE registrations SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $registration_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php");
    exit();
}

// Handle registration rejection
if (isset($_GET['reject_registration']) && is_numeric($_GET['reject_registration'])) {
    $registration_id = (int)$_GET['reject_registration'];
    $stmt = $conn->prepare("UPDATE registrations SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param("i", $registration_id);
    $stmt->execute();
    $stmt->close();
    header("Location: faculty_dashboard.php");
    exit();
}

// Fetch events created by this faculty
$stmt = $conn->prepare("SELECT * FROM events WHERE created_by = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch registration requests for this faculty's events
$registration_requests = [];
foreach ($events as $event) {
    $stmt = $conn->prepare("
        SELECT r.id, r.user_id, r.event_id, r.status, u.name, u.course, u.section 
        FROM registrations r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.event_id = ? AND r.status = 'Pending'
    ");
    $stmt->bind_param("i", $event['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $registration_requests[$event['id']] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch enrolled users for each event
$enrolled_users = [];
foreach ($events as $event) {
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.course, u.section 
        FROM users u 
        JOIN registrations r ON u.id = r.user_id 
        WHERE r.event_id = ? AND r.status = 'Approved'
    ");
    $stmt->bind_param("i", $event['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $enrolled_users[$event['id']] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle new event creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = $_POST['venue'];
    $description = $_POST['description'];
    $created_by = $_SESSION['user_id'];

    // Validate session user_id
    if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
        $error = "Invalid user session. Please log in again.";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (title, category, date, time, venue, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $error = "Failed to prepare the statement: " . $conn->error;
        } else {
            $stmt->bind_param("ssssssi", $title, $category, $date, $time, $venue, $description, $created_by);
            if ($stmt->execute()) {
                $success = "Event created successfully!";
                header("Location: faculty_dashboard.php");
                exit();
            } else {
                $error = "Failed to create event: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - AEG College Event Portal</title>
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
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Faculty Dashboard</h2>

        <!-- Error/Success Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Add New Event Form -->
        <h4 class="mb-4">Add New Event</h4>
        <form method="POST" class="col-md-6 mx-auto mb-5">
            <div class="mb-3">
                <label for="title" class="form-label">Event Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">General</option>
                    <option value="Tech">Tech</option>
                    <option value="Art">Art</option>
                    <option value="Sports">Sports</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="time" class="form-label">Time</label>
                <input type="time" class="form-control" id="time" name="time" required>
            </div>
            <div class="mb-3">
                <label for="venue" class="form-label">Venue</label>
                <input type="text" class="form-control" id="venue" name="venue" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Event</button>
        </form>

        <!-- Registration Requests -->
        <h4 class="mb-4">Registration Requests</h4>
        <?php $has_requests = false; ?>
        <?php foreach ($events as $event): ?>
            <?php if (!empty($registration_requests[$event['id']])): ?>
                <?php $has_requests = true; ?>
                <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registration_requests[$event['id']] as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['name']); ?></td>
                                <td><?php echo htmlspecialchars($request['course']); ?></td>
                                <td><?php echo htmlspecialchars($request['section']); ?></td>
                                <td>
                                    <a href="faculty_dashboard.php?approve_registration=<?php echo $request['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                    <a href="faculty_dashboard.php?reject_registration=<?php echo $request['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this registration?')">Reject</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!$has_requests): ?>
            <p class="text-center">No registration requests pending.</p>
        <?php endif; ?>

        <!-- List of Created Events -->
        <h4 class="mb-4">My Events</h4>
        <?php if (empty($events)): ?>
            <p class="text-center">No events created.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-4 mb-4">
                        <div class="event-card shadow-sm">
                            <div class="card-body">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><span class="badge bg-primary"><?php echo htmlspecialchars($event['category'] ?: 'General'); ?></span></p>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
                                <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <div class="btn-group">
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                                    <?php if (date('Y-m-d', strtotime($event['date'])) === date('Y-m-d')): ?>
                                        <a href="attendance.php?id=<?php echo $event['id']; ?>" class="btn btn-success">Mark Attendance</a>
                                    <?php endif; ?>
                                    <a href="faculty_dashboard.php?delete=<?php echo $event['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                </div>

                                <!-- Enrolled Users -->
                                <h5 class="mt-3">Enrolled Users</h5>
                                <?php if (empty($enrolled_users[$event['id']])): ?>
                                    <p>No users enrolled.</p>
                                <?php else: ?>
                                    <ul class="list-group">
                                        <?php foreach ($enrolled_users[$event['id']] as $user): ?>
                                            <li class="list-group-item">
                                                <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['course']); ?>, Section <?php echo htmlspecialchars($user['section']); ?>)
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
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