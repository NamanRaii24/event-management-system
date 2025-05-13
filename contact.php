<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - AEG College Event Portal</title>
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
            <h1>Contact Us</h1>
            <p>Get in touch with us for any queries or support</p>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="contact-info">
        <div class="container">
            <div class="info-box">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Our Location</h3>
                <p>Asian Education Group<br>123 Education Street<br>New Delhi, India</p>
            </div>
            <div class="info-box">
                <i class="fas fa-phone"></i>
                <h3>Phone Numbers</h3>
                <p>+91 1234567890<br>+91 9876543210</p>
            </div>
            <div class="info-box">
                <i class="fas fa-envelope"></i>
                <h3>Email Address</h3>
                <p>support@aegcollegeeventportal.com<br>info@aegcollegeeventportal.com</p>
            </div>
        </div>
    </section>

    <!-- Contact Form and Map Section -->
    <section class="contact-form-map">
        <div class="container">
            <div class="contact-form">
                <h2>Send us a Message</h2>
                <form id="contactForm" method="POST" action="process_contact.php">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="subject" placeholder="Subject" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3504.8487027267674!2d77.3255003!3d28.544267!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce42a38005d07%3A0x5ad67e927c642b77!2sAsian%20Education%20Group!5e0!3m2!1sen!2sin!4v1743129140744!5m2!1sen!2sin" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <!-- Social Media Section -->
    <section class="social-media">
        <div class="container">
            <h2>Follow Us</h2>
            <div class="social-icons">
                <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
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