<?php
include '../db_setup.php';

// Ensure user is a logged-in admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Unauthorized access.");
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Get form data
$user_id = (int)$_POST['user_id'];
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$role = $_POST['role'];
$password = $_POST['password'];

// Prevent admin from changing their own role from admin to user
if ($user_id === $_SESSION['user_id'] && $role === 'user') {
    die("Error: You cannot remove your own admin privileges.");
}

// Check if a new password was provided
if (!empty($password)) {
    // A new password was entered, so update it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $full_name, $email, $role, $hashed_password, $user_id);
} else {
    // No new password, so don't update the password field
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $role, $user_id);
}

// Execute the update
if ($stmt->execute()) {
    // Redirect back to the dashboard, focusing on the user management section
    header("Location: admin_dashboard.php#user-management");
    exit();
} else {
    echo "Error updating record: " . $conn->error;
}

$stmt->close();
$conn->close();
?>