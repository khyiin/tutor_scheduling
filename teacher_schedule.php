<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::TEACHER) !== 0) {
    header("Location: login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION['name']);

// Basic schedule listing for the logged-in teacher
$teacherId = (int)$_SESSION['user_id'];
$scheduleResult = mysqli_query(
    $conn,
    "SELECT subject, day, time_start, time_end 
     FROM schedules 
     WHERE teacher_id = $teacherId 
     ORDER BY field(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), time_start"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorFlow | My Schedule</title>
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
            <a href="dashboard.php" class="nav-link"><i class="fas fa-grid-2"></i> Dashboard</a>
            <a href="teacher_schedule.php" class="nav-link active"><i class="fas fa-calendar-alt"></i> Schedule</a>
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
                <h1>My Weekly Schedule</h1>
                <p>Hello, <?php echo $userName; ?>. Manage your teaching slots.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">
            <div class="glass-effect">
                <div class="table-header">
                    <h3><i class="fas fa-calendar-day"></i> Your Schedule</h3>
                </div>
                <div class="table-scroll">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Day</th>
                                <th>Start</th>
                                <th>End</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($scheduleResult && mysqli_num_rows($scheduleResult) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($scheduleResult)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($row['day']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($row['time_start'], 0, 5)); ?></td>
                                        <td><?php echo htmlspecialchars(substr($row['time_end'], 0, 5)); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No schedule added yet.</td>
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

