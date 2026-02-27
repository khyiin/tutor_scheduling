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

$requests = mysqli_query($conn,
    "SELECT sr.id AS request_id, sr.status, sr.created_at,
            u.fullname AS student_name, u.email AS student_email,
            s.subject, s.day, s.time_start, s.time_end
     FROM session_requests sr
     INNER JOIN schedules s ON s.id = sr.schedule_id
     INNER JOIN users u ON u.id = sr.student_id
     WHERE s.teacher_id = $teacherId
     ORDER BY sr.created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorFlow | My Students</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/client.css">
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
            <a href="students.php" class="nav-link active"><i class="fas fa-user-graduate"></i> Students</a>
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
                <h1>View Students</h1>
                <p>Session requests from students. Confirm or reject pending bookings.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">
            <?php if (isset($_GET['updated'])): ?>
                <div class="page-card glow-card" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #86efac;">
                    <p style="margin:0; color:#166534; font-weight:600;"><i class="fas fa-check-circle"></i> Request <?php echo $_GET['updated'] === 'confirm' ? 'confirmed' : 'rejected'; ?>.</p>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="page-card" style="background: #fef2f2; border: 1px solid #fecaca;">
                    <p style="margin:0; color:#b91c1c;"><i class="fas fa-exclamation-circle"></i> Invalid request.</p>
                </div>
            <?php endif; ?>

            <div class="table-frame glass-effect">
                <div class="table-header">
                    <h3><i class="fas fa-user-graduate"></i> Student Session Requests</h3>
                </div>
                <div class="table-scroll">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests && mysqli_num_rows($requests) > 0): ?>
                                <?php while ($r = mysqli_fetch_assoc($requests)): ?>
                                    <tr>
                                        <td><div class="std-cell"><strong><?php echo htmlspecialchars($r['student_name']); ?></strong><br><small style="color:#64748b;"><?php echo htmlspecialchars($r['student_email']); ?></small></div></td>
                                        <td><span class="tag-math"><?php echo htmlspecialchars($r['subject']); ?></span></td>
                                        <td><?php echo htmlspecialchars($r['day']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($r['time_start'], 0, 5)); ?> – <?php echo htmlspecialchars(substr($r['time_end'], 0, 5)); ?></td>
                                        <td><?php echo date('M j, g:i A', strtotime($r['created_at'])); ?></td>
                                        <td>
                                            <?php if ($r['status'] === 'confirmed'): ?>
                                                <span class="status done">Confirmed</span>
                                            <?php elseif ($r['status'] === 'rejected'): ?>
                                                <span class="status" style="background:#fee2e2; color:#b91c1c;">Rejected</span>
                                            <?php else: ?>
                                                <span class="status wait">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($r['status'] === 'pending'): ?>
                                                <form method="post" action="process_booking.php" style="display:inline-flex; gap:8px;">
                                                    <input type="hidden" name="request_id" value="<?php echo (int)$r['request_id']; ?>">
                                                    <input type="hidden" name="action" value="confirm">
                                                    <button type="submit" class="btn-glow btn-success" style="padding:6px 12px; font-size:0.8rem;"><i class="fas fa-check"></i> Confirm</button>
                                                </form>
                                                <form method="post" action="process_booking.php" style="display:inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo (int)$r['request_id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn-glow btn-danger" style="padding:6px 12px; font-size:0.8rem;"><i class="fas fa-times"></i> Reject</button>
                                                </form>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No session requests yet. When students schedule a session with you, they will appear here.</td>
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

