<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NTC Complaint Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo-link">
                <img src="assets/images/ntc_logo.png" alt="NTC Logo" class="logo">
                <span>NTC Complaints</span>
            </a>
            <nav class="main-nav">
                <a href="login.php" class="btn btn-primary">Login</a>
            </nav>
        </div>
    </header>

    <section class="hero-section">
        <div class="container">
            <h1>Efficiently Manage Your NTC Service Complaints</h1>
            <p class="subtitle">A seamless, transparent, and user-friendly platform to voice your concerns and track their resolution.</p>
            <a href="register.php" class="btn btn-primary btn-large">File a Complaint Now</a>
        </div>
    </section>

    <section class="how-it-works">
        <div class="container">
            <h2>A Simple Three-Step Process</h2>
            <div class="steps-container">
                <div class="step-card">
                    <div class="step-icon"><i class="fa-solid fa-user-plus"></i></div>
                    <h3>1. Register Account</h3>
                    <p>Quickly create a secure account to get started. Your personal information is kept private and safe.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon"><i class="fa-solid fa-file-pen"></i></div>
                    <h3>2. Submit Complaint</h3>
                    <p>Fill out a simple form with the details of your issue. Provide all necessary information for a speedy process.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
                    <h3>3. Track Status</h3>
                    <p>Receive a unique tracking ID for your complaint and monitor its status in real-time from your dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> NTC Complaint Management System. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>