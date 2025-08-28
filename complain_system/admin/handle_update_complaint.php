<?php
include '../db_setup.php';

// Ensure user is a logged-in admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Unauthorized access.");
}

// Check if form data is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Validate input
if (!isset($_POST['complaint_id']) || !isset($_POST['new_status'])) {
    die("Error: Missing form data.");
}

$complaint_id = (int)$_POST['complaint_id'];
$new_status = $_POST['new_status'];
// GET THE NEW ADMIN COMMENTS, allow it to be empty
$admin_comments = $_POST['admin_comments'] ?? ''; 

// List of allowed statuses to prevent arbitrary values
$allowed_statuses = ['Pending', 'In Progress', 'Resolved', 'Closed'];

if (!in_array($new_status, $allowed_statuses)) {
    die("Error: Invalid status value.");
}

// Prepare and execute the update query for both status and comments
$stmt = $conn->prepare("UPDATE complaints SET status = ?, admin_comments = ? WHERE id = ?");
$stmt->bind_param("ssi", $new_status, $admin_comments, $complaint_id);

if ($stmt->execute()) {
    // Redirect back to the dashboard, focusing on the complaints section
    header("Location: admin_dashboard.php#recent-complaints");
    exit();
} else {
    echo "Error updating record: " . $conn->error;
}

$stmt->close();
$conn->close();
?>