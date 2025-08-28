<?php
include '../db_setup.php';

// Ensure user is a logged-in admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// --- Fetch data for the overview cards ---
$total_complaints_res = $conn->query("SELECT COUNT(*) as count FROM complaints");
$total_complaints = $total_complaints_res->fetch_assoc()['count'];
$pending_complaints_res = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'Pending'");
$pending_complaints = $pending_complaints_res->fetch_assoc()['count'];
$total_users_res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$total_users = $total_users_res->fetch_assoc()['count'];
$total_admins_res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$total_admins = $total_admins_res->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - NTC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>

<div class="dashboard-wrapper">
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fa-solid fa-headset"></i> NTC Admin</h3>
        </div>
        <ul class="sidebar-links">
            <li class="active"><a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
            <li><a href="#user-management"><i class="fa-solid fa-users-cog"></i> Manage Users</a></li>
            <li><a href="#recent-complaints"><i class="fa-solid fa-list-check"></i> Complaints</a></li>
            <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <header class="main-header">
            <h2>Dashboard Overview</h2>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
            </div>
        </header>

        <section class="overview-cards">
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-ticket"></i></div>
                <div class="card-info"><p>Total Complaints</p><span><?php echo $total_complaints; ?></span></div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="card-info"><p>Pending Complaints</p><span><?php echo $pending_complaints; ?></span></div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-users"></i></div>
                <div class="card-info"><p>Total Users</p><span><?php echo $total_users; ?></span></div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa-solid fa-user-shield"></i></div>
                <div class="card-info"><p>Administrators</p><span><?php echo $total_admins; ?></span></div>
            </div>
        </section>

        <section class="data-section" id="recent-complaints">
            <div class="data-card">
                <div class="data-card-header">
                    <h3>Complaints Management</h3>
                    <form action="admin_dashboard.php#recent-complaints" method="GET" class="search-form">
                        <input type="text" name="search_id" placeholder="Search by Complaint ID..." value="<?php echo isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : ''; ?>">
                        <button type="submit"><i class="fa-solid fa-search"></i></button>
                    </form>
                </div>
                <?php if(isset($_GET['search_id']) && !empty($_GET['search_id'])): ?>
                <div class="search-results">
                    <h4>Search Result:</h4>
                    <?php
                    $search_id = (int)$_GET['search_id'];
                    $stmt_search = $conn->prepare("SELECT c.*, u.full_name, u.email FROM complaints c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
                    $stmt_search->bind_param("i", $search_id);
                    $stmt_search->execute();
                    $result_search = $stmt_search->get_result();
                    if($result_search->num_rows > 0) {
                        $complaint = $result_search->fetch_assoc();
                        echo "<div class='complaint-details'><strong>ID:</strong> {$complaint['id']} | <strong>User:</strong> " . htmlspecialchars($complaint['full_name']);
                        echo " <button class='btn-view open-modal-btn' 
                                    data-id='{$complaint['id']}' 
                                    data-username='" . htmlspecialchars($complaint['full_name']) . "'
                                    data-email='" . htmlspecialchars($complaint['email']) . "'
                                    data-phone='" . htmlspecialchars($complaint['phone_number']) . "'
                                    data-service='" . htmlspecialchars($complaint['service_type']) . "'
                                    data-status='" . htmlspecialchars($complaint['status']) . "'
                                    data-details='" . htmlspecialchars($complaint['complaint_details']) . "'
                                    data-date='" . date('F j, Y, g:i a', strtotime($complaint['created_at'])) . "'
                                    data-notes='" . htmlspecialchars($complaint['admin_comments'] ?? '') . "'>
                                    <i class='fa-solid fa-eye'></i> View / Manage
                               </button></div>";
                    } else {
                        echo "<p>No complaint found with ID: " . htmlspecialchars($search_id) . "</p>";
                    }
                    $stmt_search->close();
                    ?>
                </div>
                <?php endif; ?>
                <table>
                   <thead><tr><th>ID</th><th>User</th><th>Service</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                   <tbody>
                   <?php
                   $complaints_result = $conn->query("SELECT c.*, u.full_name, u.email FROM complaints c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 10");
                   while($row = $complaints_result->fetch_assoc()):
                   ?>
                       <tr>
                           <td><?php echo $row['id']; ?></td>
                           <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                           <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                           <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                           <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                           <td>
                               <button class="btn-view open-modal-btn" 
                                    data-id="<?php echo $row['id']; ?>" 
                                    data-username="<?php echo htmlspecialchars($row['full_name']); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                    data-phone="<?php echo htmlspecialchars($row['phone_number']); ?>"
                                    data-service="<?php echo htmlspecialchars($row['service_type']); ?>"
                                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                    data-details="<?php echo htmlspecialchars($row['complaint_details']); ?>"
                                    data-date="<?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?>"
                                    data-notes="<?php echo htmlspecialchars($row['admin_comments'] ?? ''); ?>">
                                    <i class="fa-solid fa-eye"></i> View
                               </button>
                           </td>
                       </tr>
                   <?php endwhile; ?>
                   </tbody>
                </table>
            </div>
        </section>

        <section class="data-section" id="user-management">
            <div class="data-card">
                <div class="data-card-header"><h3>User Management</h3></div>
                
                <div class="add-user-form">
                    <h4><i class="fa-solid fa-user-plus"></i> Add New User or Admin</h4>
                    <form action="manage_users.php" method="POST">
                        <input type="text" name="full_name" placeholder="Full Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <select name="role"><option value="user">User</option><option value="admin">Admin</option></select>
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="btn">Add User</button>
                    </form>
                </div>

                <div class="user-table-wrapper">
                    <h4><i class="fa-solid fa-user-shield"></i> Administrator Accounts</h4>
                    <table>
                        <thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php
                        $admin_result = $conn->query("SELECT id, full_name, email FROM users WHERE role = 'admin'");
                        while($user = $admin_result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-edit"><i class="fa-solid fa-pencil-alt"></i> Edit</a>
                                    
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form action="manage_users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-delete"><i class="fa-solid fa-trash-alt"></i> Delete</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="user-table-wrapper">
                    <h4><i class="fa-solid fa-users"></i> Standard User Accounts</h4>
                    <table>
                        <thead><tr><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php
                        $user_result = $conn->query("SELECT id, full_name, email FROM users WHERE role = 'user'");
                        while($user = $user_result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-edit"><i class="fa-solid fa-pencil-alt"></i> Edit</a>
                                    <form action="manage_users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn-delete"><i class="fa-solid fa-trash-alt"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<div id="complaintModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Complaint Details</h3>
            <span class="close-btn">&times;</span>
        </div>
        <div class="modal-body">
            <div class="modal-info-grid">
                <div><strong>Complaint ID:</strong> <span id="modal-id"></span></div>
                <div><strong>Status:</strong> <span id="modal-status" class="status"></span></div>
                <div><strong>User:</strong> <span id="modal-username"></span></div>
                <div><strong>Email:</strong> <span id="modal-email"></span></div>
                <div><strong>Service Number:</strong> <span id="modal-phone"></span></div>
                <div><strong>Service Type:</strong> <span id="modal-service"></span></div>
                <div class="full-width"><strong>Submitted On:</strong> <span id="modal-date"></span></div>
                <div class="full-width">
                    <strong>Complaint Details:</strong>
                    <p id="modal-details"></p>
                </div>
            </div>
            
            <div class="modal-action-form">
                <h4>Update Complaint</h4>
                <form action="handle_update_complaint.php" method="POST">
                    <input type="hidden" name="complaint_id" id="modal-form-id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_status">Set Status</label>
                            <select name="new_status" id="new_status">
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="admin_comments">Resolution Notes / Comments</label>
                            <textarea name="admin_comments" id="modal-form-notes" rows="4" placeholder="Add comments on how this issue was handled..."></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Save Changes</button>
                </form>
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
        
        const id = btn.dataset.id;
        const username = btn.dataset.username;
        const email = btn.dataset.email;
        const phone = btn.dataset.phone;
        const service = btn.dataset.service;
        const status = btn.dataset.status;
        const details = btn.dataset.details;
        const date = btn.dataset.date;
        const notes = btn.dataset.notes;

        document.getElementById('modal-id').textContent = id;
        document.getElementById('modal-username').textContent = username;
        document.getElementById('modal-email').textContent = email;
        document.getElementById('modal-phone').textContent = phone;
        document.getElementById('modal-service').textContent = service;
        document.getElementById('modal-details').textContent = details;
        document.getElementById('modal-date').textContent = date;
        
        document.getElementById('modal-form-id').value = id;
        document.getElementById('modal-form-notes').value = notes;

        const statusBadge = document.getElementById('modal-status');
        statusBadge.textContent = status;
        statusBadge.className = 'status status-' + status.toLowerCase().replace(/ /g, '-');

        document.querySelector('.modal-action-form select[name="new_status"]').value = status;
        
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    openModalBtns.forEach(btn => btn.addEventListener('click', openModal));
    
    if(closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
});
</script>

</body>
</html>