<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::TEACHER) !== 0) {
    header("Location: login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION['name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorFlow | Earnings</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard">

<div class="app-viewport">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-glow"><i class="fas fa-bolt"></i></div>
            <span>TutorFlow</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link"><i class="fas fa-grid-2"></i> Dashboard</a>
            <a href="teacher_schedule.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
            <a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i> Students</a>
            <a href="earnings.php" class="nav-link active"><i class="fas fa-chart-line"></i> Earnings</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-info">
                <h1>Earnings Overview</h1>
                <p>A future breakdown of your earnings will appear here.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">
            <div class="stats-grid">
                <div class="stat-card glow-green">
                    <div class="stat-icon-wrap bg-green">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-txt">
                        <span class="val">$0.00</span>
                        <span class="lbl">Total Earnings (placeholder)</span>
                    </div>
                </div>
            </div>

            <div class="table-frame glass-effect">
                <div class="table-header">
                    <h3><i class="fas fa-list-ul"></i> Recent Payments (placeholder)</h3>
                </div>
                <div class="table-scroll">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>-</td>
                                <td>Coming soon</td>
                                <td>$0.00</td>
                                <td><span class="status wait">Placeholder</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>

