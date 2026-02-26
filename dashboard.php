<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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
    <title>TutorFlow | Dashboard</title>
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
            <a href="dashboard.php" class="nav-link active"><i class="fas fa-grid-2"></i> Dashboard</a>
            <a href="teacher_schedule.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
            <a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i> Students</a>
            <a href="earnings.php" class="nav-link"><i class="fas fa-chart-line"></i> Earnings</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-info">
                <h1>Dashboard Overview</h1>
                <p>Hello, <?php echo $userName; ?>! Check your updates today.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">

            <div class="quick-actions">
                <a href="teacher_schedule.php" class="quick-btn glow-blue">
                    <i class="fas fa-users"></i>
                    View Students
                </a>

                <a href="teacher_schedule.php" class="quick-btn glow-purple">
                    <i class="fas fa-calendar-check"></i>
                    Set Availability
                </a>

                <a href="#" class="quick-btn glow-green">
                    <i class="fas fa-comments"></i>
                    Messages
                </a>
            </div>

            <div class="stats-grid">
                <div class="stat-card glow-blue">
                    <div class="stat-icon-wrap bg-blue">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-txt">
                        <span class="val">4</span>
                        <span class="lbl">Upcoming</span>
                    </div>
                </div>
                
                <div class="stat-card glow-purple">
                    <div class="stat-icon-wrap bg-purple">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                    <div class="stat-txt">
                        <span class="val">28.5</span>
                        <span class="lbl">Total Hours</span>
                    </div>
                </div>
                
                <div class="stat-card glow-green">
                    <div class="stat-icon-wrap bg-green">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-txt">
                        <span class="val">$1,420</span>
                        <span class="lbl">Revenue</span>
                    </div>
                </div>
            </div>

            <div class="table-frame glass-effect">
                <div class="table-header">
                    <h3><i class="fas fa-list-ul"></i> Recent Bookings</h3>
                    <button class="btn-sm">View All</button>
                </div>
                <div class="table-scroll">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><div class="std-cell"><strong>Alex Johnson</strong></div></td>
                                <td><span class="tag-math">Mathematics</span></td>
                                <td>10:00 AM</td>
                                <td><span class="status done">Confirmed</span></td>
                            </tr>
                            <tr>
                                <td><div class="std-cell"><strong>Sarah Miller</strong></div></td>
                                <td><span class="tag-eng">English Lit.</span></td>
                                <td>02:00 PM</td>
                                <td><span class="status wait">Pending</span></td>
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