<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int)$_SESSION['user_id'];
$currentName = htmlspecialchars($_SESSION['name']);
$currentEmail = '';
$role = $_SESSION['role'] ?? '';

$result = mysqli_query($conn, "SELECT fullname, email, role FROM users WHERE id = $id LIMIT 1");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $currentName = htmlspecialchars($row['fullname']);
    $currentEmail = htmlspecialchars($row['email']);
    if ($role === '') {
        $role = $row['role'];
    }
}

$isStudent = (strcasecmp($role, Role::STUDENT) === 0);
$isTeacher = (strcasecmp($role, Role::TEACHER) === 0);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['fullname'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');

    if ($newName && $newEmail) {
        $safeName = mysqli_real_escape_string($conn, $newName);
        $safeEmail = mysqli_real_escape_string($conn, $newEmail);
        mysqli_query($conn, "UPDATE users SET fullname = '$safeName', email = '$safeEmail' WHERE id = $id");
        $_SESSION['name'] = $newName;
        $currentName = htmlspecialchars($newName);
        $currentEmail = htmlspecialchars($newEmail);
        $message = 'Profile updated successfully.';
    } else {
        $message = 'Please fill in all required fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | Edit Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/client.css">
</head>

<body class="<?php echo $isTeacher ? 'dashboard' : 'client'; ?>">

<div class="app-viewport">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-glow"><i class="fas fa-bolt"></i></div>
            <span>TutorFlow</span>
        </div>

        <nav class="sidebar-nav">
            <?php if ($isTeacher): ?>
                <a href="dashboard.php" class="nav-link"><i class="fas fa-grid-2"></i> Dashboard</a>
                <a href="teacher_schedule.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Schedule</a>
                <a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i> Students</a>
                <a href="earnings.php" class="nav-link"><i class="fas fa-chart-line"></i> Earnings</a>
                <a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> Profile</a>
            <?php else: ?>
                <a href="client.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
                <a href="find_tutor.php" class="nav-link"><i class="fas fa-search"></i> Find Tutor</a>
                <a href="schedule_session.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Schedule Session</a>
                <a href="client_sessions.php" class="nav-link"><i class="fas fa-calendar-check"></i> My Sessions</a>
                <a href="history.php" class="nav-link"><i class="fas fa-clock"></i> History</a>
                <a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> Profile</a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-info">
                <h1>Edit Profile</h1>
                <p>Update your basic account information.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($currentName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">
            <?php if ($message): ?>
                <div class="page-card" style="background:#ecfdf3; border:1px solid #bbf7d0; margin-bottom:16px;">
                    <p style="margin:0; color:#166534; font-size:0.9rem;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="page-card glass-effect">
                <form method="post">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" value="<?php echo $currentName; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $currentEmail; ?>" required>
                    </div>

                    <button type="submit" class="btn-glow" style="padding:10px 22px; font-size:0.95rem; border-radius:999px;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="profile.php" style="margin-left:12px; font-size:0.85rem; color:#6b7280; text-decoration:none;">
                        Cancel
                    </a>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>

