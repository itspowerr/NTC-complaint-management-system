<?php
// handle_registration.php
include 'db_setup.php'; // This already starts the session

// --- Form Validation ---
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$errors = [];

// 1. Check for empty fields
if (empty($full_name) || empty($email) || empty($password)) {
    $errors[] = "All fields are required.";
}

// 2. Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// 3. Check if email already exists in the database
if (empty($errors)) {
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $errors[] = "An account with this email address already exists.";
    }
    $stmt_check->close();
}


// --- Process Registration or Redirect with Errors ---
if (!empty($errors)) {
    // If there are errors, store them in a session flash message and redirect back
    $_SESSION['flash_message'] = implode("<br>", $errors);
    $_SESSION['flash_message_type'] = 'error';
    header("Location: register.php");
    exit();
} else {
    // No errors, proceed with registration
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt_insert = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $full_name, $email, $hashed_password);

    if ($stmt_insert->execute()) {
        // Set success flash message and redirect to login page
        $_SESSION['flash_message'] = "✅ Registration successful! Please log in to continue.";
        $_SESSION['flash_message_type'] = 'success';
        header("Location: login.php");
        exit();
    } else {
        // Handle unexpected database error
        $_SESSION['flash_message'] = "An unexpected error occurred. Please try again.";
        $_SESSION['flash_message_type'] = 'error';
        header("Location: register.php");
        exit();
    }
    $stmt_insert->close();
}

$conn->close();
?>