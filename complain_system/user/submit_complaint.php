<?php
include '../db_setup.php'; // This already starts the session

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    // Set an error flash message and redirect
    $_SESSION['flash_message'] = "❌ You must be logged in to submit a complaint.";
    $_SESSION['flash_message_type'] = 'error';
    header("Location: ../login.php");
    exit();
}

// Basic validation
if (empty($_POST['service_type']) || empty($_POST['phone_number']) || empty($_POST['complaint_details'])) {
    $_SESSION['flash_message'] = "❌ Please fill out all fields in the complaint form.";
    $_SESSION['flash_message_type'] = 'error';
    header("Location: user_dashboard.php");
    exit();
}

// Get form data
$user_id = $_SESSION['user_id'];
$service_type = $_POST['service_type'];
$phone_number = $_POST['phone_number'];
$complaint_details = $_POST['complaint_details'];

// Insert into the database
$stmt = $conn->prepare("INSERT INTO complaints (user_id, service_type, phone_number, complaint_details) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $service_type, $phone_number, $complaint_details);

if ($stmt->execute()) {
    $complaint_id = $conn->insert_id;
    // Set a success flash message with the new ID
    $_SESSION['flash_message'] = "✅ Complaint submitted successfully! Your tracking ID is <strong>#" . $complaint_id . "</strong>.";
    $_SESSION['flash_message_type'] = 'success';
} else {
    // Set an error flash message
    $_SESSION['flash_message'] = "❌ There was an error submitting your complaint. Please try again.";
    $_SESSION['flash_message_type'] = 'error';
}

$stmt->close();
$conn->close();

// Redirect back to the user dashboard
header("Location: user_dashboard.php");
exit();
?>