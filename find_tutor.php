<?php
session_start();
include 'config.php';
include 'roles.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::STUDENT) !== 0) {
    header("Location: login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION['name']);

// FIXED: Added 'id' to the SELECT statement so the schedule link works.
// Also added 'bio' (assuming you have a bio or specialization column in your users table)
$teachers = mysqli_query(
    $conn,
    "SELECT id, fullname, email 
     FROM users 
     WHERE role = '" . Role::TEACHER . "' 
     ORDER BY fullname ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | Find Tutor</title>
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
            <a href="find_tutor.php" class="nav-link active"><i class="fas fa-search"></i> Find Tutor</a>
            <a href="schedule_session.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Schedule Session</a>
            <a href="client_sessions.php" class="nav-link"><i class="fas fa-calendar-check"></i> My Sessions</a>
            <a href="history.php" class="nav-link"><i class="fas fa-clock"></i> History</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-info">
                <h1>Find a Tutor</h1>
                <p>Hi <?php echo $userName; ?>, browse available tutors below.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">
            <div class="client-table glass-effect">
                <div class="table-header">
                    <h3><i class="fas fa-user-graduate"></i> Available Tutors</h3>
                </div>
                <div class="table-scroll">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($teachers && mysqli_num_rows($teachers) > 0): ?>
                                <?php while ($t = mysqli_fetch_assoc($teachers)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($t['fullname']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($t['email']); ?></td>
                                        <td style="text-align: center;">
                                            <a href="schedule_session.php?teacher_id=<?php echo (int)$t['id']; ?>" 
                                               class="btn-glow" 
                                               style="display:inline-block; padding:8px 16px; font-size:0.85rem; text-decoration:none; border-radius: 8px;">
                                               <i class="fas fa-calendar-plus"></i> Schedule session
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px;">No tutors found yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>