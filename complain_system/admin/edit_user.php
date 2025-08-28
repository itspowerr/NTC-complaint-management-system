<?php
include '../db_setup.php';

// Ensure user is a logged-in admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if a user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No user ID provided.");
}

$user_id_to_edit = (int)$_GET['id'];

// Fetch user data from the database
$stmt = $conn->prepare("SELECT full_name, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id_to_edit);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
<div class="edit-user-container">
    <div class="data-card">
        <h3><i class="fa-solid fa-user-edit"></i> Edit User Details</h3>
        <form action="handle_edit_user.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user_id_to_edit; ?>">
            
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>

            <label for="password">New Password (Optional)</label>
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">

            <div class="form-actions">
                <button type="submit" class="btn">Save Changes</button>
                <a href="admin_dashboard.php#user-management" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>