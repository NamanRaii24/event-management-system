<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's achievements
$achievements_query = "SELECT * FROM achievements WHERE user_id = ?";
$achievements_stmt = $conn->prepare($achievements_query);
$achievements_stmt->bind_param("i", $user_id);
$achievements_stmt->execute();
$achievements_result = $achievements_stmt->get_result();

// Fetch leaderboard data for students only
$leaderboard_query = "SELECT u.name, COUNT(a.id) as wins 
                     FROM users u 
                     LEFT JOIN achievements a ON u.id = a.user_id 
                     WHERE u.role = 'Student'
                     GROUP BY u.id 
                     ORDER BY wins DESC 
                     LIMIT 10";
$leaderboard_result = $conn->query($leaderboard_query);
$leaderboard = $leaderboard_result->fetch_all(MYSQLI_ASSOC);

// Get user's rank (only among students)
$user_rank_query = "SELECT COUNT(*) + 1 as rank 
                    FROM (SELECT COUNT(*) as wins 
                          FROM achievements a
                          JOIN users u ON a.user_id = u.id
                          WHERE u.role = 'Student'
                          GROUP BY a.user_id 
                          HAVING wins > (SELECT COUNT(*) 
                                       FROM achievements 
                                       WHERE user_id = ?)) as ranks";
$user_rank_stmt = $conn->prepare($user_rank_query);
$user_rank_stmt->bind_param("i", $user_id);
$user_rank_stmt->execute();
$user_rank_result = $user_rank_stmt->get_result();
$user_rank = $user_rank_result->fetch_assoc()['rank'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - AEG College Event Portal</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
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
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Student Achievements</h1>
            <p>Track your progress and see your accomplishments</p>
        </div>
    </section>

    <!-- Achievements Overview -->
    <section class="achievements-overview">
        <div class="container">
            <div class="achievement-stats">
                <div class="stat-box">
                    <i class="fas fa-trophy"></i>
                    <h3>Total Achievements</h3>
                    <p><?php echo $achievements_result->num_rows; ?></p>
                </div>
                <div class="stat-box">
                    <i class="fas fa-medal"></i>
                    <h3>Student Rank</h3>
                    <p>#<?php echo $user_rank; ?></p>
                </div>
                <div class="stat-box">
                    <i class="fas fa-star"></i>
                    <h3>Points Earned</h3>
                    <p><?php echo $achievements_result->num_rows * 100; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Your Achievements -->
    <section class="your-achievements">
        <div class="container">
            <h2>Your Achievements</h2>
            <div class="achievements-grid">
                <?php if ($achievements_result->num_rows > 0): ?>
                    <?php while ($achievement = $achievements_result->fetch_assoc()): ?>
                        <div class="achievement-card">
                            <i class="fas fa-award"></i>
                            <h3><?php echo htmlspecialchars($achievement['title']); ?></h3>
                            <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                            <span class="achievement-date"><?php echo date('M d, Y', strtotime($achievement['date_earned'])); ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-achievements">
                        <i class="fas fa-trophy"></i>
                        <h3>No Achievements Yet</h3>
                        <p>Participate in events to earn achievements!</p>
                        <a href="index.php#events" class="cta-button">Explore Events</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Leaderboard -->
    <section class="leaderboard-section">
        <div class="container">
            <h2>Top Student Performers</h2>
            <div class="leaderboard-table">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student Name</th>
                            <th>Achievements</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaderboard as $index => $entry): ?>
                            <tr class="<?php echo $index < 3 ? 'top-' . ($index + 1) : ''; ?>">
                                <td>
                                    <?php 
                                    if ($index == 0) echo '<i class="fas fa-crown"></i>';
                                    elseif ($index == 1) echo '<i class="fas fa-medal"></i>';
                                    elseif ($index == 2) echo '<i class="fas fa-award"></i>';
                                    else echo ($index + 1);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($entry['name']); ?></td>
                                <td><?php echo $entry['wins']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; AEG College Event Portal</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>