<?php
session_start();
include 'db.php';

// Generate CSRF token for feedback form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $comments = filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'] ?? null;

    $stmt = $conn->prepare("INSERT INTO feedback (user_id, comment, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $comments);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Feedback submitted successfully!";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error'] = "Error submitting feedback: " . $stmt->error;
    }
    $stmt->close();
}

// Handle event creation (Admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event']) && $_SESSION['role'] === 'Admin') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = filter_input(INPUT_POST, 'venue', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $created_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO events (title, category, date, time, venue, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $title, $category, $date, $time, $venue, $description, $created_by);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Event created successfully!";
    } else {
        $_SESSION['error'] = "Error creating event: " . $stmt->error;
    }
    $stmt->close();
    header("Location: index.php");
    exit;
}

// Handle signup request approval/rejection (Admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_SESSION['role'] === 'Admin') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Fetch the signup request
        $stmt = $conn->prepare("SELECT * FROM signup_requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, college, course, year, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $request['name'], $request['email'], $request['password'], $request['role'], $request['college'], $request['course'], $request['year'], $request['section']);
        $stmt->execute();
        $stmt->close();

        // Update signup request status
        $stmt = $conn->prepare("UPDATE signup_requests SET status = 'Approved' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Signup request approved!";
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE signup_requests SET status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Signup request rejected!";
    }
    header("Location: index.php");
    exit;
}

// Fetch recent feedback
$feedback_query = "SELECT f.comment, u.name FROM feedback f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 3";
$feedback_result = $conn->query($feedback_query);

// Fetch signup requests (Admin only)
$signup_requests = [];
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    $stmt = $conn->prepare("SELECT * FROM signup_requests WHERE status = 'Pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $signup_requests[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AEG College Event Portal</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="my_account.php">My Account</a></li>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <li><a href="admin.php">Admin Dashboard</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Sign Up</a></li>
        <?php endif; ?>
    </ul>
</nav>
    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-content">
            <h1>Elevate Your Campus Life with AEG College Event Portal</h1>
            <p>Discover, register, and participate in events effortlessly.</p>
            <a href="#events" class="cta-button">Explore Events</a>
        </div>
    </header>

    <!-- Admin Section -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
        <section class="admin-section">
            <h2>Admin Dashboard</h2>

            <!-- Create Event Form -->
            <div class="admin-box">
                <h3>Create New Event</h3>
                <?php if (isset($_SESSION['success'])): ?>
                    <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                <?php endif; ?>
                <form method="POST" action="index.php">
                    <div class="input-group">
                        <label for="title">Event Title</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="input-group">
                        <label for="category">Category</label>
                        <input type="text" name="category">
                    </div>
                    <div class="input-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" required>
                    </div>
                    <div class="input-group">
                        <label for="time">Time</label>
                        <input type="time" name="time" required>
                    </div>
                    <div class="input-group">
                        <label for="venue">Venue</label>
                        <input type="text" name="venue" required>
                    </div>
                    <div class="input-group">
                        <label for="description">Description</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>
                    <button type="submit" name="create_event">Create Event</button>
                </form>
            </div>

            <!-- Manage Signup Requests -->
            <div class="admin-box">
                <h3>Pending Signup Requests</h3>
                <?php if (empty($signup_requests)): ?>
                    <p>No pending signup requests.</p>
                <?php else: ?>
                    <table class="signup-requests">
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
                                        <form method="POST" action="index.php" style="display:inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                                            <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Event Highlights -->
    <section class="event-highlights">
        <div class="highlight">
            <h3>🔥 Trending Events</h3>
            <p>Don't miss out on the most exciting events happening now!</p>
        </div>
        <div class="highlight">
            <h3>🎟️ Seamless Registrations</h3>
            <p>Register for events with just one click.</p>
        </div>
        <div class="highlight">
            <h3>📅 Stay Updated</h3>
            <p>Receive real-time notifications and never miss an event.</p>
        </div>
    </section>

    <!-- Features -->
    <section class="features">
        <h2>What We Provide?</h2>
        <div class="feature-box">
            <i class="fas fa-calendar-check"></i>
            <h3>Easy Event Management</h3>
            <p>Track, organize, and participate in college events effortlessly.</p>
        </div>
        <div class="feature-box">
            <i class="fas fa-users"></i>
            <h3>Community Engagement</h3>
            <p>Connect with peers and stay updated on college activities.</p>
        </div>
        <div class="feature-box">
            <i class="fas fa-bell"></i>
            <h3>Instant Notifications</h3>
            <p>Never miss an important event or deadline.</p>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="events ongoing-events">
        <h1>Ongoing Events</h1>
        <div class="slider-container">
            <button class="left-arrow" onclick="slideLeft('ongoing')">❮</button>
            <div class="event-list ongoing" id="ongoing-event-list">
                <!-- Events will be loaded dynamically -->
            </div>
            <button class="right-arrow" onclick="slideRight('ongoing')">❯</button>
        </div>
    </section>

    <section class="events upcoming-events">
        <h1>Upcoming Events</h1>
        <div class="slider-container">
            <button class="left-arrow" onclick="slideLeft('upcoming')">❮</button>
            <div class="event-list upcoming" id="upcoming-event-list">
                <!-- Events will be loaded dynamically -->
            </div>
            <button class="right-arrow" onclick="slideRight('upcoming')">❯</button>
        </div>
    </section>

    <!-- Calendar View -->
    <section class="calendar-view">
        <h2>Event Calendar</h2>
        <div class="calendar-controls">
            <button onclick="changeMonth(-1)">← Previous</button>
            <span id="calendar-month-year"></span>
            <button onclick="changeMonth(1)">Next →</button>
        </div>
        <table id="event-calendar">
            <thead>
                <tr>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>

    <!-- What Our Users Say Section -->
    <section class="testimonials">
        <h2>What Our Users Say</h2>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <form method="POST" action="index.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="input-group">
                <textarea name="comments" placeholder="Share your feedback..." required rows="5"></textarea>
            </div>
            <button type="submit" name="submit_feedback">Submit Feedback</button>
        </form>
        <div class="recent-feedback">
            <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                <div class="testimonial">
                    <p>"<?php echo htmlspecialchars($feedback['comment']); ?>"</p>
                    <h4>- <?php echo htmlspecialchars($feedback['name'] ?? 'Anonymous'); ?></h4>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>© AEG College Event Portal</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>