<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::TEACHER) !== 0) {
    header("Location: login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION['name']);
$teacherId = (int)$_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slot'])) {
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject'] ?? ''));
    $day = mysqli_real_escape_string($conn, trim($_POST['day'] ?? ''));
    $time_start = mysqli_real_escape_string($conn, $_POST['time_start'] ?? '');
    $time_end = mysqli_real_escape_string($conn, $_POST['time_end'] ?? '');
    if ($subject && $day && $time_start && $time_end) {
        $stmt = mysqli_prepare($conn, "INSERT INTO schedules (teacher_id, subject, day, time_start, time_end) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'issss', $teacherId, $subject, $day, $time_start, $time_end);
        if (mysqli_stmt_execute($stmt)) {
            $message = 'Session added successfully. Students can now see it on Schedule Session.';
        } else {
            $error = 'Could not add session. Please try again.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Please fill all fields.';
    }
}

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
    <link rel="stylesheet" href="assets/client.css">
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
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
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
            <?php if ($message): ?>
                <div class="page-card glow-card" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #86efac;">
                    <p style="margin:0; color:#166534; font-weight:600;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="page-card" style="background: #fef2f2; border: 1px solid #fecaca;">
                    <p style="margin:0; color:#b91c1c; font-weight:600;"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="page-card glow-card">
                <h3 style="margin-bottom:1rem;"><i class="fas fa-calendar-plus"></i> Add Session (Upload Availability)</h3>
                <p style="color:#64748b; margin-bottom:1.25rem; font-size:0.9rem;">New slots will appear on students’ Schedule Session page so they can request a booking.</p>
                <form method="post" action="">
                    <input type="hidden" name="add_slot" value="1">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:1rem;">
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" placeholder="e.g. Math" required>
                        </div>
                        <div class="form-group">
                            <label>Day</label>
                            <select name="day" required>
                                <option value="">Select day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Start time</label>
                            <input type="time" name="time_start" required>
                        </div>
                        <div class="form-group">
                            <label>End time</label>
                            <input type="time" name="time_end" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-glow" style="margin-top:0.5rem;"><i class="fas fa-plus"></i> Add Session</button>
                </form>
            </div>

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

