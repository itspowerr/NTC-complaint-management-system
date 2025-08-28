<?php
// Start the session to access flash messages
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - NTC Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container form-container">
        <img src="assets/images/ntc_logo.png" alt="NTC Logo" class="logo">
        <h2>User Login</h2>
        
        <?php
        // Check for and display flash messages
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = $_SESSION['flash_message_type'];
            // Display the message
            echo "<div class='alert alert-{$type}'>{$message}</div>";
            // Unset the session variables so the message doesn't show again
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_message_type']);
        }
        ?>

        <form action="handle_login.php" method="POST">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        <p class="back-link"><a href="index.php">← Back to Home</a></p>
    </div>
</body>
</html>