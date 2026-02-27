<?php
session_start();
include 'config.php';
include 'roles.php';

// Access Control: Only Teachers
if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::TEACHER) !== 0) {
    header("Location: login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION['name']);
$teacherId = (int)$_SESSION['user_id'];

/** --- DATA FETCHING FOR STATS --- **/

// 1. Upcoming Sessions (Status: confirmed)
$upcomingQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM session_requests sr 
    INNER JOIN schedules s ON sr.schedule_id = s.id 
    WHERE s.teacher_id = $teacherId AND sr.status = 'confirmed'");
$upcomingCount = mysqli_fetch_assoc($upcomingQuery)['count'] ?? 0;

// 2. Total Hours (Calculated from confirmed sessions)
// This assumes sessions are fixed duration or calculated by start/end time
// Add start_time and end_time to schedules table if they don't exist
// 2. Total Hours (Calculated from confirmed sessions)
$hoursQuery = mysqli_query($conn, "SELECT SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time))/60 as total_hours 
    FROM schedules WHERE teacher_id = $teacherId");

// The variable below MUST match the one above ($hoursQuery)
$totalHours = round(mysqli_fetch_assoc($hoursQuery)['total_hours'] ?? 0, 1);

// 3. Revenue (Assuming a 'price' column in users or schedules - placeholder used)
$revenueQuery = mysqli_query($conn, "SELECT SUM(amount) as total FROM earnings WHERE teacher_id = $teacherId");
$revenue = mysqli_fetch_assoc($revenueQuery)['total'] ?? 0;

/** --- RECENT BOOKINGS WITH PAGINATION --- **/
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$recentRequests = mysqli_query($conn,
    "SELECT sr.status, sr.created_at, u.fullname AS student_name, s.subject
     FROM session_requests sr
     INNER JOIN schedules s ON s.id = sr.schedule_id
     INNER JOIN users u ON u.id = sr.student_id
     WHERE s.teacher_id = $teacherId
     ORDER BY sr.created_at DESC
     LIMIT $limit OFFSET $offset"
);

// Total pages for pagination
$totalBookingsResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM session_requests sr 
    INNER JOIN schedules s ON sr.schedule_id = s.id WHERE s.teacher_id = $teacherId");
$totalBookingsRows = mysqli_fetch_assoc($totalBookingsResult)['total'];
$totalPages = ceil($totalBookingsRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorFlow | Teacher Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-body">

<div class="app-viewport">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-glow"><i class="fas fa-bolt"></i></div>
            <span>TutorFlow</span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="teacher_schedule.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
            <a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i> Students</a>
            <a href="earnings.php" class="nav-link"><i class="fas fa-chart-line"></i> Earnings</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-info">
                <h1>Dashboard Overview</h1>
                <p>Hello, <?php echo $userName; ?>! Here is what's happening today.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">
            <div class="quick-actions">
                <a href="students.php" class="quick-btn">
                    <i class="fas fa-users"></i> View Students
                </a>
                <a href="teacher_schedule.php" class="quick-btn">
                    <i class="fas fa-calendar-check"></i> Set Availability
                </a>
                <a href="earnings.php" class="quick-btn">
                    <i class="fas fa-wallet"></i> View Earnings
                </a>
            </div>

            <div class="stats-grid">
                <div class="stat-card glow-blue">
                    <div class="stat-icon-wrap bg-blue"><i class="fas fa-play-circle"></i></div>
                    <div class="stat-txt">
                        <span class="val"><?php echo $upcomingCount; ?></span>
                        <span class="lbl">Upcoming Sessions</span>
                    </div>
                </div>
                <div class="stat-card glow-purple">
                    <div class="stat-icon-wrap bg-purple"><i class="fas fa-stopwatch"></i></div>
                    <div class="stat-txt">
                        <span class="val"><?php echo $totalHours; ?></span>
                        <span class="lbl">Total Hours</span>
                    </div>
                </div>
                <div class="stat-card glow-green">
                    <div class="stat-icon-wrap bg-green"><i class="fas fa-coins"></i></div>
                    <div class="stat-txt">
                        <span class="val">$<?php echo number_format($revenue, 2); ?></span>
                        <span class="lbl">Revenue</span>
                    </div>
                </div>
            </div>

            <div class="glass-effect">
                <div class="table-header">
                    <h3><i class="fas fa-list-ul"></i> Recent Bookings</h3>
                    <div class="pagination-controls">
                        <?php if($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?>" class="btn-nav"><i class="fas fa-chevron-left"></i></a>
                        <?php endif; ?>
                        <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                        <?php if($page < $totalPages): ?>
                            <a href="?page=<?php echo $page+1; ?>" class="btn-nav"><i class="fas fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Requested On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($recentRequests) > 0): ?>
                                <?php while ($r = mysqli_fetch_assoc($recentRequests)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($r['student_name']); ?></strong></td>
                                        <td><span class="tag-math"><?php echo htmlspecialchars($r['subject']); ?></span></td>
                                        <td><?php echo date('M j, g:i A', strtotime($r['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = ($r['status'] == 'confirmed') ? 'done' : 'wait';
                                                echo "<span class='status $statusClass'>".ucfirst($r['status'])."</span>";
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:30px;">No bookings found.</td></tr>
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