<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::STUDENT) !== 0) {
    header("Location: login.php");
    exit();
}

$studentId = (int)$_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['name']);

// Pagination
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Summary for header widget
$summaryResult = mysqli_query(
    $conn,
    "SELECT 
        COUNT(*) AS total_tx,
        COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) AS total_paid
     FROM payments
     WHERE student_id = $studentId"
);
$summary = $summaryResult ? mysqli_fetch_assoc($summaryResult) : ['total_tx' => 0, 'total_paid' => 0];

// Count for pagination
$countResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM payments WHERE student_id = $studentId"
);
$countRow = $countResult ? mysqli_fetch_assoc($countResult) : ['total' => 0];
$totalRows = (int)$countRow['total'];
$totalPages = $totalRows > 0 ? ceil($totalRows / $perPage) : 1;

// Fetch paginated payment history
$payments = mysqli_query(
    $conn,
    "SELECT 
        p.id,
        p.amount,
        p.status,
        p.created_at,
        s.subject,
        s.day,
        s.time_start,
        u.fullname AS tutor_name
     FROM payments p
     INNER JOIN session_requests sr ON sr.id = p.session_request_id
     INNER JOIN schedules s ON s.id = sr.schedule_id
     INNER JOIN users u ON u.id = s.teacher_id
     WHERE p.student_id = $studentId
     ORDER BY p.created_at DESC
     LIMIT $perPage OFFSET $offset"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | My Transactions</title>
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
            <a href="client_sessions.php" class="nav-link"><i class="fas fa-calendar-check"></i> My Sessions</a>
            <a href="history.php" class="nav-link"><i class="fas fa-clock"></i> History</a>
            <a href="student_transactions.php" class="nav-link active"><i class="fas fa-receipt"></i> Transactions</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-info">
                <h1>My Transactions</h1>
                <p>Hi <?php echo $userName; ?>, here is your payment history.</p>
            </div>
            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>

        <div class="scroll-area">
            <div class="stats-grid" style="margin-bottom:20px;">
                <div class="stat-card glow-green">
                    <div class="stat-icon-wrap bg-green">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <span class="val">$<?php echo number_format((float)$summary['total_paid'], 2); ?></span>
                        <span class="lbl">Total Paid</span>
                    </div>
                </div>
                <div class="stat-card glow-purple">
                    <div class="stat-icon-wrap bg-purple">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <span class="val"><?php echo (int)$summary['total_tx']; ?></span>
                        <span class="lbl">Total Transactions</span>
                    </div>
                </div>
            </div>

            <div class="client-table glass-effect">
                <div class="table-header">
                    <h3><i class="fas fa-list-ul"></i> Transaction History</h3>
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
                                <th>Date</th>
                                <th>Session</th>
                                <th>Tutor</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payments && mysqli_num_rows($payments) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($payments)): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['subject']); ?></strong>
                                            <div style="font-size:0.75rem; color:#6b7280;">
                                                <?php echo htmlspecialchars($row['day']); ?> · 
                                                <?php echo htmlspecialchars(substr($row['time_start'], 0, 5)); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['tutor_name']); ?></td>
                                        <td>$<?php echo number_format((float)$row['amount'], 2); ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'paid'): ?>
                                                <span class="status done">Paid</span>
                                            <?php elseif ($row['status'] === 'pending' || $row['status'] === 'processing'): ?>
                                                <span class="status wait">Pending</span>
                                            <?php else: ?>
                                                <span class="status" style="background:#fee2e2; color:#b91c1c;">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:24px; color:#9ca3af;">
                                        No transactions yet. Payments you make for confirmed sessions will appear here.
                                    </td>
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

