<?php
// Database connection
$conn = new mysqli("localhost", "root", "mysql@2917", "registration_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if email is set via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"])) {
    $email = $_POST["email"];

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);

    // Execute and check
    if ($stmt->execute()) {
        echo "User with email $email has been deleted.";
    } else {
        echo "Error deleting user: " . $stmt->error;
    }

    // Close
    $stmt->close();
} else {
    echo "Please provide an email.";
}

$conn->close();
?>
