<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::STUDENT) !== 0) {
    header("Location: login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION['name']);
$userEmail = "";

$id = (int)$_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT email FROM users WHERE id = $id LIMIT 1");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $userEmail = htmlspecialchars($row['email']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | My Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/client.css">
</head>

<body class="client">

<div class="app-viewport">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-glow"><i class="fas fa-bolt"></i></div>
            <span>TutorFlow</span>
        </div>

        <nav class="sidebar-nav">
            <a href="client.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
            <a href="find_tutor.php" class="nav-link"><i class="fas fa-search"></i> Find Tutor</a>
            <a href="client_sessions.php" class="nav-link"><i class="fas fa-calendar-check"></i> My Sessions</a>
            <a href="history.php" class="nav-link"><i class="fas fa-clock"></i> History</a>
            <a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> Profile</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-info">
                <h1>My Profile</h1>
                <p>Review your basic account information.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">
            <div class="glass-effect profile-card">
                <h3><i class="fas fa-id-badge"></i> Account Details</h3>
                <p><strong>Name:</strong> <?php echo $userName; ?></p>
                <p><strong>Email:</strong> <?php echo $userEmail; ?></p>
                <p><strong>Role:</strong> Student</p>
                <p style="margin-top:1rem; opacity:0.8;">
                    Editing profile details is not yet implemented. This page is a functional placeholder.
                </p>
            </div>
        </div>
    </main>
</div>

</body>
</html>

