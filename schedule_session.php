<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::STUDENT) !== 0) {
    header("Location: login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION['name']);
$studentId = (int)$_SESSION['user_id'];
$teacherId = isset($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $scheduleId = (int)$_POST['schedule_id'];
    $teacherIdPost = (int)($_POST['teacher_id'] ?? 0);
    if ($scheduleId && $teacherIdPost) {
        $chk = mysqli_query($conn, "SELECT id FROM session_requests WHERE student_id = $studentId AND schedule_id = $scheduleId AND status IN ('pending','confirmed')");
        if ($chk && mysqli_num_rows($chk) > 0) {
            $error = 'You already have a request for this slot.';
        } else {
            mysqli_query($conn, "INSERT INTO session_requests (student_id, schedule_id, status) VALUES ($studentId, $scheduleId, 'pending')");
            $message = 'Session requested. The tutor will confirm or reject it—check My Sessions.';
            $teacherId = $teacherIdPost;
        }
    }
}

$teacher = null;
if ($teacherId) {
    $t = mysqli_query($conn, "SELECT id, fullname, email FROM users WHERE id = $teacherId AND role = '" . mysqli_real_escape_string($conn, Role::TEACHER) . "' LIMIT 1");
    if ($t && $row = mysqli_fetch_assoc($t)) {
        $teacher = $row;
    }
}

$slots = [];
if ($teacher) {
    $slots = mysqli_query($conn,
        "SELECT s.id, s.subject, s.day, s.time_start, s.time_end, s.price
         FROM schedules s
         WHERE s.teacher_id = " . (int)$teacher['id'] . "
         ORDER BY FIELD(s.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), s.time_start"
    );
}

$teachersList = mysqli_query($conn,
    "SELECT id, fullname FROM users WHERE role = '" . mysqli_real_escape_string($conn, Role::TEACHER) . "' ORDER BY fullname"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | Schedule Session</title>
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
            <a href="schedule_session.php" class="nav-link active"><i class="fas fa-calendar-plus"></i> Schedule Session</a>
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
                <h1>Schedule Session</h1>
                <p>Pick a tutor and an available slot to request a session.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
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
                    <p style="margin:0; color:#b91c1c;"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="page-card glow-card">
                <h3 style="margin-bottom:0.5rem;"><i class="fas fa-user-graduate"></i> Choose a tutor</h3>
                <p style="color:#64748b; margin-bottom:1rem; font-size:0.9rem;">Select a tutor to see their available slots (uploaded by them).</p>
                <form method="get" action="">
                    <div class="form-group" style="max-width:320px;">
                        <select name="teacher_id" onchange="this.form.submit()">
                            <option value="">-- Select tutor --</option>
                            <?php if ($teachersList): while ($t = mysqli_fetch_assoc($teachersList)): ?>
                                <option value="<?php echo (int)$t['id']; ?>" <?php echo $teacherId === (int)$t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['fullname']); ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                </form>
            </div>

            <?php if ($teacher): ?>
                <div class="table-frame glass-effect">
                    <div class="table-header">
                        <h3><i class="fas fa-calendar-day"></i> Available slots — <?php echo htmlspecialchars($teacher['fullname']); ?></h3>
                    </div>
                    <div class="table-scroll">
                        <table class="compact-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($slots && mysqli_num_rows($slots) > 0): ?>
                                    <?php while ($slot = mysqli_fetch_assoc($slots)): ?>
                                        <?php
                                        $req = mysqli_query($conn, "SELECT status FROM session_requests WHERE student_id = $studentId AND schedule_id = " . (int)$slot['id']);
                                        $myStatus = ($req && $row = mysqli_fetch_assoc($req)) ? $row['status'] : null;
                                        ?>
                                        <tr>
                                            <td><span class="tag-math"><?php echo htmlspecialchars($slot['subject']); ?></span></td>
                                            <td><?php echo htmlspecialchars($slot['day']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($slot['time_start'], 0, 5)); ?> – <?php echo htmlspecialchars(substr($slot['time_end'], 0, 5)); ?></td>
                                            <td><?php echo '$' . number_format((float)$slot['price'], 2); ?></td>
                                            <td>
                                                <?php if ($myStatus === 'confirmed'): ?>
                                                    <span class="status done">Confirmed</span>
                                                <?php elseif ($myStatus === 'pending'): ?>
                                                    <span class="status wait">Pending</span>
                                                <?php elseif ($myStatus === 'rejected'): ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="schedule_id" value="<?php echo (int)$slot['id']; ?>">
                                                        <input type="hidden" name="teacher_id" value="<?php echo (int)$teacher['id']; ?>">
                                                        <button type="submit" class="btn-glow" style="padding:5px 10px; font-size:0.75rem; border-radius:999px;"><i class="fas fa-redo"></i> Request again</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" style="display:inline;">
                                                        <input type="hidden" name="schedule_id" value="<?php echo (int)$slot['id']; ?>">
                                                        <input type="hidden" name="teacher_id" value="<?php echo (int)$teacher['id']; ?>">
                                                        <button type="submit" class="btn-glow" style="padding:5px 10px; font-size:0.75rem; border-radius:999px;"><i class="fas fa-calendar-plus"></i> Request session</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">This tutor has not added any sessions yet. Ask them to add availability on their Schedule page.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="page-card">
                    <p style="margin:0; color:#64748b;"><i class="fas fa-info-circle"></i> Select a tutor above to see their available slots.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
