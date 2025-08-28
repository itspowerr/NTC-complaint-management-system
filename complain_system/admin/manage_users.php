<?php
include '../db_setup.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Unauthorized access.");
}

$action = $_POST['action'];

if ($action == 'add') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $password, $role);
    $stmt->execute();
    $stmt->close();

} elseif ($action == 'delete') {
    $user_id = (int)$_POST['user_id'];
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        echo "Error: You cannot delete your own account.";
        exit();
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin_dashboard.php");
exit();
?>