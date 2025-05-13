<?php
include 'db.php';

// Read the SQL file
$sql = file_get_contents('achievements.sql');

// Execute the SQL queries
if ($conn->multi_query($sql)) {
    echo "Achievements table created and sample data inserted successfully!";
} else {
    echo "Error creating achievements table: " . $conn->error;
}

$conn->close();
?>