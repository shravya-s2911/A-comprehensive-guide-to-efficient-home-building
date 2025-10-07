<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "mysql@2917";
$db_name = "homebuilder";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create necessary tables if they don't exist
function createTables($conn) {
    // Create users table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating users table: " . $conn->error;
    }
    
    // Create building_details table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS building_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plot_area FLOAT NOT NULL,
        plot_address TEXT NOT NULL,
        floors INT NOT NULL,
        building_type VARCHAR(50) NOT NULL,
        budget DECIMAL(12,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating building_details table: " . $conn->error;
    }
    
    // Create progress_logs table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS progress_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        task VARCHAR(255) NOT NULL,
        amount_spent DECIMAL(12,2) NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating progress_logs table: " . $conn->error;
    }
    
    // Create forum_posts table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS forum_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating forum_posts table: " . $conn->error;
    }
    
    // Create forum_comments table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS forum_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating forum_comments table: " . $conn->error;
    }
}

// Create necessary tables
createTables($conn);
?>