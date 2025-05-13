<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - AEG College Event Portal</title>
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
            <h1>About AEG College Event Portal</h1>
            <p>Empowering College Events with Technology</p>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <h2>What is AEG College Event Portal?</h2>
            <p>AEG College Event Portal is an innovative platform designed to streamline and enhance the management of college events. We provide a comprehensive solution for organizing, managing, and participating in various college activities and events.</p>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mission-vision">
        <div class="container">
            <div class="box">
                <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                <p>To revolutionize college event management by providing a seamless, efficient, and user-friendly platform that connects students, faculty, and administrators.</p>
            </div>
            <div class="box">
                <h3><i class="fas fa-eye"></i> Our Vision</h3>
                <p>To become the leading college event management platform, setting new standards in digital event organization and student engagement.</p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <h2>How It Works</h2>
        <div class="steps">
            <div class="step">
                <i class="fas fa-calendar-check"></i>
                <h4>Create Events</h4>
                <p>Organizers can easily create and manage events with detailed information.</p>
            </div>
            <div class="step">
                <i class="fas fa-users"></i>
                <h4>Register & Participate</h4>
                <p>Students can browse events and register with just a few clicks.</p>
            </div>
            <div class="step">
                <i class="fas fa-chart-line"></i>
                <h4>Track & Manage</h4>
                <p>Monitor attendance, track participation, and manage event details.</p>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose">
        <h2>Why Choose AEG College Event Portal?</h2>
        <div class="features">
            <div class="feature-box">
                <i class="fas fa-clock"></i>
                <h4>Time-Saving</h4>
                <p>Automate event management tasks and save valuable time.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-mobile-alt"></i>
                <h4>Mobile Friendly</h4>
                <p>Access and manage events from any device, anywhere.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-shield-alt"></i>
                <h4>Secure & Reliable</h4>
                <p>Your data is protected with advanced security measures.</p>
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
