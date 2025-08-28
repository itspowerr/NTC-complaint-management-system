<?php
// db_setup.php - The single source of truth for the database schema.

// --- Database Configuration ---
$servername = "localhost";
$username = "root"; // Your DB username
$password = "";     // Your DB password
$dbname = "ntc_complaint_system";

// --- Establish Connection ---
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname); // Switch to the database

// --- Helper Function to Check for Columns ---
function columnExists($conn, $tableName, $columnName) {
    $result = $conn->query("SHOW COLUMNS FROM `$tableName` LIKE '$columnName'");
    return $result->num_rows > 0;
}

// --- Schema Definition for 'users' Table ---
$conn->query("
CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);");

// --- Schema Definition for 'complaints' Table ---
$conn->query("
CREATE TABLE IF NOT EXISTS complaints (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    complaint_details TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);");

// --- ADD THE NEW COLUMN FOR ADMIN COMMENTS ---
if (!columnExists($conn, 'complaints', 'admin_comments')) {
    $conn->query("ALTER TABLE complaints ADD admin_comments TEXT NULL AFTER status");
}


// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>