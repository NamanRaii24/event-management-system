<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $college = $_POST['college'];
    $course = $_POST['course'];
    $year = (int)$_POST['year'];
    $section = $_POST['section'];

    $max_years = ['BBA' => 3, 'BCA' => 3, 'BCom' => 3, 'BSc CS' => 3, 'PGDM' => 2, 'BA LLB' => 5];
    $valid_combinations = [
        'ASB' => ['BBA', 'BCA', 'BCom', 'BSc CS'],
        'ABS' => ['PGDM'],
        'ALC' => ['BA LLB']
    ];

    $errors = [];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email already exists.";
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM signup_requests WHERE email = ? AND status = 'Pending'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "A signup request with this email is already pending.";
    }
    $stmt->close();

    if (!in_array($course, $valid_combinations[$college])) {
        $errors[] = "Invalid course for the selected college.";
    }
    if ($year < 1 || $year > $max_years[$course]) {
        $errors[] = "Invalid year for the selected course.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO signup_requests (name, email, password, role, college, course, year, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $name, $email, $password, $role, $college, $course, $year, $section);
        if ($stmt->execute()) {
            $message = "Request to create account has been sent to admin.";
        } else {
            $errors[] = "Failed to submit request: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - AEG College Event Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/styles.css">
    <script src="./js/script.js" defer></script>
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
            <li><a href="login.php">Login</a></li>
        </ul>
    </nav>

    <div class="container mt-5">
        <h2>Sign Up</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="Student">Student</option>
                    <option value="Faculty">Faculty</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="college" class="form-label">College</label>
                <select class="form-select" id="college" name="college" required onchange="updateCourses()">
                    <option value="ASB">Asian School of Business (ASB)</option>
                    <option value="ABS">Asian Business School (ABS)</option>
                    <option value="ALC">Asian Law College (ALC)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="course" class="form-label">Course</label>
                <select class="form-select" id="course" name="course" required onchange="updateYears()">
                </select>
            </div>
            <div class="mb-3">
                <label for="year" class="form-label">Year</label>
                <select class="form-select" id="year" name="year" required>
                </select>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <select class="form-select" id="section" name="section" required>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>
    </div>

    <footer>
        <p>© AEG College Event Portal</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const courseOptions = {
            'ASB': ['BBA', 'BCA', 'BCom', 'BSc CS'],
            'ABS': ['PGDM'],
            'ALC': ['BA LLB']
        };
        const maxYears = {
            'BBA': 3, 'BCA': 3, 'BCom': 3, 'BSc CS': 3,
            'PGDM': 2, 'BA LLB': 5
        };

        function updateCourses() {
            const college = document.getElementById('college').value;
            const courseSelect = document.getElementById('course');
            courseSelect.innerHTML = '';
            courseOptions[college].forEach(course => {
                const option = document.createElement('option');
                option.value = course;
                option.textContent = course;
                courseSelect.appendChild(option);
            });
            updateYears();
        }

        function updateYears() {
            const course = document.getElementById('course').value;
            const yearSelect = document.getElementById('year');
            yearSelect.innerHTML = '';
            const max = maxYears[course];
            for (let i = 1; i <= max; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                yearSelect.appendChild(option);
            }
        }

        updateCourses();
    </script>
</body>
</html>