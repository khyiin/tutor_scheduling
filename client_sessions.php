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

// Optional flash message for payment
$paymentMessage = '';
if (isset($_GET['payment']) && $_GET['payment'] === 'success') {
    $paymentMessage = 'Payment completed successfully. Your tutor will be notified.';
}

// Pagination for "My Sessions"
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

// Total rows for pagination
$totalSessionsResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM session_requests sr
     INNER JOIN schedules s ON s.id = sr.schedule_id
     INNER JOIN users u ON u.id = s.teacher_id
     WHERE sr.student_id = $studentId"
);
$totalSessionRows = $totalSessionsResult ? (int)mysqli_fetch_assoc($totalSessionsResult)['total'] : 0;
$totalPages = $totalSessionRows > 0 ? ceil($totalSessionRows / $limit) : 1;

// Paged data with payment info
$sessions = mysqli_query(
    $conn,
    "SELECT sr.id, sr.status, sr.created_at,
            s.subject, s.day, s.time_start, s.time_end, s.price,
            u.fullname AS tutor_name,
            p.status AS payment_status,
            p.amount AS payment_amount
     FROM session_requests sr
     INNER JOIN schedules s ON s.id = sr.schedule_id
     INNER JOIN users u ON u.id = s.teacher_id
     LEFT JOIN payments p ON p.session_request_id = sr.id
     WHERE sr.student_id = $studentId
     ORDER BY sr.created_at DESC
     LIMIT $limit OFFSET $offset"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | My Sessions</title>
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
            <a href="schedule_session.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Schedule Session</a>
            <a href="client_sessions.php" class="nav-link active"><i class="fas fa-calendar-check"></i> My Sessions</a>
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
                <h1>My Sessions</h1>
                <p>Hi <?php echo $userName; ?>, here are your upcoming lessons.</p>
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
                    <h3><i class="fas fa-clock"></i> Upcoming Sessions</h3>
                    <div class="pagination-controls">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="btn-nav" aria-label="Previous page">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        <span class="pagination-label">
                            Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                        </span>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn-nav" aria-label="Next page">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Tutor</th>
                                <th>Subject</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Requested</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($paymentMessage): ?>
                                <tr>
                                    <td colspan="8" style="background:#ecfdf3; color:#166534; padding:12px 16px; font-size:0.85rem;">
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo htmlspecialchars($paymentMessage); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($sessions && mysqli_num_rows($sessions) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($sessions)): ?>
                                    <?php
                                        $isConfirmed = ($row['status'] === 'confirmed');
                                        $isPaid = ($row['payment_status'] === 'paid');
                                        $baseAmount = (float)$row['price'] > 0 ? (float)$row['price'] : 30.00;
                                        $amount = $row['payment_amount'] !== null ? (float)$row['payment_amount'] : $baseAmount;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['tutor_name']); ?></strong></td>
                                        <td><span class="tag-math"><?php echo htmlspecialchars($row['subject']); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['day']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($row['time_start'], 0, 5)); ?> – <?php echo htmlspecialchars(substr($row['time_end'], 0, 5)); ?></td>
                                        <td><?php echo date('M j, g:i A', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <?php if ($isConfirmed && !$isPaid): ?>
                                                <span class="status wait">Awaiting Payment</span>
                                                <div style="font-size:0.8rem; color:#0f172a; margin-top:2px;">
                                                    $<?php echo number_format($amount, 2); ?>
                                                </div>
                                            <?php elseif ($isPaid): ?>
                                                <span class="status done">Paid</span>
                                                <div style="font-size:0.8rem; color:#0f172a; margin-top:2px;">
                                                    $<?php echo number_format($amount, 2); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="status" style="background:#e5e7eb; color:#4b5563;">No payment due</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] === 'confirmed'): ?>
                                                <span class="status done">Confirmed</span>
                                            <?php elseif ($row['status'] === 'rejected'): ?>
                                                <span class="status" style="background:#fee2e2; color:#b91c1c;">Rejected</span>
                                            <?php else: ?>
                                                <span class="status wait">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <?php if ($isConfirmed && !$isPaid): ?>
                                                <a 
                                                    href="student_pay.php?session_id=<?php echo (int)$row['id']; ?>" 
                                                    class="btn-glow"
                                                    style="display:inline-block; padding:6px 14px; font-size:0.8rem; border-radius:999px; text-decoration:none;"
                                                >
                                                    <i class="fas fa-credit-card"></i> Pay Now
                                                </a>
                                            <?php else: ?>
                                                <span style="font-size:0.8rem; color:#9ca3af;">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No sessions yet. <a href="schedule_session.php" class="btn-glow" style="display:inline-block; margin-top:8px; text-decoration:none;">Schedule a session</a></td>
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

