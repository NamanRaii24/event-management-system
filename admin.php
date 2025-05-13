<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Handle signup request approval
if (isset($_GET['approve_request']) && is_numeric($_GET['approve_request'])) {
    $request_id = (int)$_GET['approve_request'];

    // Fetch the signup request
    $stmt = $conn->prepare("SELECT * FROM signup_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    $stmt->close();

    if ($request && $request['status'] === 'Pending') {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, college, course, year, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $request['name'], $request['email'], $request['password'], $request['role'], $request['college'], $request['course'], $request['year'], $request['section']);
        $stmt->execute();
        $stmt->close();

        // Update request status
        $stmt = $conn->prepare("UPDATE signup_requests SET status = 'Approved' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin.php");
    exit();
}

// Handle signup request rejection
if (isset($_GET['reject_request']) && is_numeric($_GET['reject_request'])) {
    $request_id = (int)$_GET['reject_request'];

    $stmt = $conn->prepare("UPDATE signup_requests SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php");
    exit();
}

// Handle registration approval
if (isset($_GET['approve_registration']) && is_numeric($_GET['approve_registration'])) {
    $registration_id = (int)$_GET['approve_registration'];
    $stmt = $conn->prepare("UPDATE registrations SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $registration_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Handle registration rejection
if (isset($_GET['reject_registration']) && is_numeric($_GET['reject_registration'])) {
    $registration_id = (int)$_GET['reject_registration'];
    $stmt = $conn->prepare("UPDATE registrations SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param("i", $registration_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Handle feedback deletion
if (isset($_GET['delete_feedback']) && is_numeric($_GET['delete_feedback'])) {
    $feedback_id = (int)$_GET['delete_feedback'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Handle event deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];

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

    header("Location: admin.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $user_id = (int)$_GET['delete_user'];

    // Prevent the admin from deleting their own account
    if ($user_id === $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        // Delete any signup requests for this user (since there's no foreign key)
        $stmt = $conn->prepare("DELETE FROM signup_requests WHERE email = (SELECT email FROM users WHERE id = ?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Delete the user (cascading deletes will handle events, registrations, and feedback)
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin.php");
    exit();
}

// Fetch all signup requests
$stmt = $conn->prepare("SELECT * FROM signup_requests WHERE status = 'Pending'");
$stmt->execute();
$result = $stmt->get_result();
$signup_requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch all events
$result = $conn->query("SELECT e.*, u.name AS faculty_name FROM events e LEFT JOIN users u ON e.created_by = u.id");
$events = $result->fetch_all(MYSQLI_ASSOC);

// Fetch registration requests
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
        SELECT u.id, u.name, u.role, u.college, u.course, u.year, u.section 
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

// Fetch all feedback
$stmt = $conn->prepare("
    SELECT f.id, f.comment, f.user_id, f.created_at, u.name, u.course, u.section, u.email 
    FROM feedback f 
    LEFT JOIN users u ON f.user_id = u.id
");
$stmt->execute();
$result = $stmt->get_result();
$feedbacks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch all registered users (students and faculty, excluding the admin)
$stmt = $conn->prepare("SELECT * FROM users WHERE role IN ('Student', 'Faculty') AND id != ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$registered_users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle new event creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = $_POST['venue'];
    $description = $_POST['description'];
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO events (title, category, date, time, venue, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $title, $category, $date, $time, $venue, $description, $created_by);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AEG College Event Portal</title>
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
                    <li class="nav-item"><a class="nav-link" href="admin.php">Admin Dashboard</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Admin Dashboard</h2>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Signup Requests -->
        <h4 class="mb-4">Signup Requests</h4>
        <?php if (empty($signup_requests)): ?>
            <p class="text-center">No signup requests pending.</p>
        <?php else: ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>College</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($signup_requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['name']); ?></td>
                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                            <td><?php echo htmlspecialchars($request['role']); ?></td>
                            <td><?php echo htmlspecialchars($request['college']); ?></td>
                            <td><?php echo htmlspecialchars($request['course']); ?></td>
                            <td><?php echo htmlspecialchars($request['year']); ?></td>
                            <td><?php echo htmlspecialchars($request['section']); ?></td>
                            <td>
                                <a href="admin.php?approve_request=<?php echo $request['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                <a href="admin.php?reject_request=<?php echo $request['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this request?')">Reject</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Registered Users -->
        <h4 class="mb-4 mt-5">Registered Users</h4>
        <?php if (empty($registered_users)): ?>
            <p class="text-center">No registered users found.</p>
        <?php else: ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>College</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registered_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['college']); ?></td>
                            <td><?php echo htmlspecialchars($user['course']); ?></td>
                            <td><?php echo htmlspecialchars($user['year']); ?></td>
                            <td><?php echo htmlspecialchars($user['section']); ?></td>
                            <td>
                                <a href="admin.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user? This will also delete all their associated data (events, registrations, feedback).')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Registration Requests -->
        <h4 class="mb-4 mt-5">Registration Requests</h4>
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
                                    <a href="admin.php?approve_registration=<?php echo $request['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                    <a href="admin.php?reject_registration=<?php echo $request['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this registration?')">Reject</a>
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

        <!-- Add New Event Form -->
        <h4 class="mb-4 mt-5">Add New Event</h4>
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

        <!-- List of All Events -->
        <h4 class="mb-4">All Events</h4>
        <?php if (empty($events)): ?>
            <p class="text-center">No events found.</p>
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
                                <?php if ($event['created_by']): ?>
                                    <p><strong>Faculty:</strong> <?php echo htmlspecialchars($event['faculty_name']); ?></p>
                                <?php endif; ?>
                                <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                                <a href="admin.php?delete=<?php echo $event['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>

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

        <!-- Feedback Section -->
        <h4 class="mb-4 mt-5">User Feedback</h4>
        <?php if (empty($feedbacks)): ?>
            <p class="text-center">No feedback submitted yet.</p>
        <?php else: ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Email</th>
                        <th>Comments</th>
                        <th>Submitted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td><?php echo $feedback['name'] ? htmlspecialchars($feedback['name']) : 'Anonymous'; ?></td>
                            <td><?php echo $feedback['course'] ? htmlspecialchars($feedback['course']) : '-'; ?></td>
                            <td><?php echo $feedback['section'] ? htmlspecialchars($feedback['section']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($feedback['email'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($feedback['comments']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                            <td>
                                <a href="admin.php?delete_feedback=<?php echo $feedback['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer py-4 bg-light text-center">
        <span class="text-muted">© 2025 AEG College Event Portal</span>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>