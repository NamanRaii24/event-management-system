<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$total_events = $conn->query("SELECT COUNT(*) FROM events")->fetch_row()[0];
$pending_regs = $conn->query("SELECT COUNT(*) FROM registrations WHERE status = 'pending'")->fetch_row()[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - AEG College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="bg-primary text-white text-center py-4">
        <h1>Admin Dashboard</h1>
    </header>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>Total Events</h5>
                        <p class="display-4"><?php echo $total_events; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>Pending Registrations</h5>
                        <p class="display-4"><?php echo $pending_regs; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <a href="logout.php" class="btn btn-danger">Logout</a>
        <a href="create_event.php" class="btn btn-success mt-3">Create Event</a>

        <h3>All Events</h3>
        <table class="table table-bordered mt-3">
            <tr><th>Title</th><th>Date</th><th>Actions</th></tr>
            <?php
            $result = $conn->query("SELECT * FROM events");
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td>
                        <a href="edit_event.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_event.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h3>Pending Registrations</h3>
        <table class="table table-bordered mt-3">
            <tr><th>User</th><th>Event</th><th>Actions</th></tr>
            <?php
            $stmt = $conn->prepare("SELECT r.id, u.name, e.title FROM registrations r JOIN users u ON r.user_id = u.id JOIN events e ON r.event_id = e.id WHERE r.status = 'pending'");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td>
                        <a href="approve.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Approve</a>
                        <a href="reject.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">Reject</a>
                    </td>
                </tr>
            <?php endwhile; $stmt->close(); ?>
        </table>
    </div>
    <footer class="footer mt-5 py-3 bg-light text-center">
        <span class="text-muted">© 2025 AEG College Event Portal</span>
    </footer>
</body>
</html>