<?php
// handle_login.php
include 'db_setup.php';

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $full_name, $hashed_password, $role);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['role'] = $role;

        if ($role == 'admin') {
            header("Location: admin/admin_dashboard.php");
        } else {
            header("Location: user/user_dashboard.php");
        }
        exit();
    } else {
        echo "Invalid password. <a href='login.php'>Try again</a>.";
    }
} else {
    echo "No user found with that email. <a href='login.php'>Try again</a>.";
}
$stmt->close();
$conn->close();
?>