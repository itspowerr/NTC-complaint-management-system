<?php
include '../db_setup.php';

// Check if user is logged in and has the 'user' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - NTC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/user_dashboard.css">
</head>
<body>

<div class="user-dashboard-container">
    <header class="main-header">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
        <a href="../logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </header>

    <?php
    // Check for and display flash messages
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_message_type'];
        // Display the message in an alert box
        echo "<div class='alert alert-{$type}'>{$message}</div>";
        // Unset the session variables so the message doesn't show again
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_message_type']);
    }
    ?>

    <div class="user-content-wrapper">
        <div class="data-card form-card">
            <div class="data-card-header">
                <h3><i class="fa-solid fa-file-circle-plus"></i> Submit a New Complaint</h3>
            </div>
            <form action="submit_complaint.php" method="POST" class="user-complaint-form">
                <div class="form-group">
                    <label for="service_type">Service Type</label>
                    <select id="service_type" name="service_type" required>
                        <option value="Mobile Prepaid">Mobile Prepaid</option>
                        <option value="Mobile Postpaid">Mobile Postpaid</option>
                        <option value="FTTH Internet">FTTH Internet</option>
                        <option value="Landline">Landline</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="phone_number">Associated Phone/Service Number</label>
                    <input type="text" id="phone_number" name="phone_number" required>
                </div>
                <div class="form-group full-width">
                    <label for="complaint_details">Please describe your issue</label>
                    <textarea id="complaint_details" name="complaint_details" rows="5" required></textarea>
                </div>
                <div class="form-group full-width">
                    <button type="submit" class="btn">Submit Complaint</button>
                </div>
            </form>
        </div>

        <div class="data-card">
            <div class="data-card-header">
                <h3><i class="fa-solid fa-clock-rotate-left"></i> Your Complaint History</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tracking ID</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT id, service_type, status, admin_comments, complaint_details, created_at FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>#" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['service_type']) . "</td>";
                            echo "<td><span class='status status-" . strtolower(str_replace(' ', '-', $row['status'])) . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                            echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                            echo "<td>
                                    <button class='btn-view open-modal-btn' 
                                        data-id='" . $row['id'] . "'
                                        data-status='" . htmlspecialchars($row['status']) . "'
                                        data-date='" . date('F j, Y, g:i a', strtotime($row['created_at'])) . "'
                                        data-details='" . htmlspecialchars($row['complaint_details']) . "'
                                        data-notes='" . htmlspecialchars($row['admin_comments'] ?? 'No comments from admin yet.') . "'>
                                        <i class='fa-solid fa-eye'></i> View Details
                                    </button>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>You have not submitted any complaints yet.</td></tr>";
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="complaintModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Complaint Details</h3>
            <span class="close-btn">&times;</span>
        </div>
        <div class="modal-body">
            <div class="modal-info-grid">
                <div><strong>Tracking ID:</strong> #<span id="modal-id"></span></div>
                <div><strong>Status:</strong> <span id="modal-status" class="status"></span></div>
                <div class="full-width"><strong>Submitted On:</strong> <span id="modal-date"></span></div>
                <div class="full-width">
                    <strong>Your Complaint:</strong>
                    <p id="modal-details"></p>
                </div>
                <div class="full-width">
                    <strong>Admin Resolution Notes:</strong>
                    <p id="modal-notes" class="admin-notes"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('complaintModal');
    const openModalBtns = document.querySelectorAll('.open-modal-btn');
    const closeModalBtn = document.querySelector('.close-btn');

    function openModal(event) {
        const btn = event.currentTarget;
        
        // Populate the modal with data from the button's data attributes
        document.getElementById('modal-id').textContent = btn.dataset.id;
        document.getElementById('modal-date').textContent = btn.dataset.date;
        document.getElementById('modal-details').textContent = btn.dataset.details;
        document.getElementById('modal-notes').textContent = btn.dataset.notes;

        // Handle the status badge class and text
        const statusBadge = document.getElementById('modal-status');
        statusBadge.textContent = btn.dataset.status;
        statusBadge.className = 'status status-' + btn.dataset.status.toLowerCase().replace(/ /g, '-');
        
        // Display the modal
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    // Attach event listeners to all "View Details" buttons
    openModalBtns.forEach(btn => btn.addEventListener('click', openModal));
    
    // Attach event listener to the close button
    if(closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Close modal if user clicks on the background overlay
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
});
</script>

</body>
</html>