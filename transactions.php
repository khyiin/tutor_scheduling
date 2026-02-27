<?php
session_start();
include 'config.php';
include 'roles.php';

// Admin-only access
if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::ADMIN) !== 0) {
    header("Location: login.php");
    exit();
}

// Pagination settings
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Aggregate stats
$summaryResult = mysqli_query(
    $conn,
    "SELECT 
        COUNT(*) AS total_transactions,
        COALESCE(SUM(amount), 0) AS total_amount
     FROM earnings"
);
$summary = $summaryResult ? mysqli_fetch_assoc($summaryResult) : ['total_transactions' => 0, 'total_amount' => 0];

// Count for pagination
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM earnings");
$countRow = $countResult ? mysqli_fetch_assoc($countResult) : ['total' => 0];
$totalRows = (int)$countRow['total'];
$totalPages = $totalRows > 0 ? ceil($totalRows / $perPage) : 1;

// Fetch paginated transactions
$transactions = mysqli_query(
    $conn,
    "SELECT 
        e.id,
        e.teacher_id,
        e.amount,
        e.created_at,
        u.fullname AS teacher_name,
        u.email     AS teacher_email
     FROM earnings e
     LEFT JOIN users u ON u.id = e.teacher_id
     ORDER BY e.created_at DESC
     LIMIT $perPage OFFSET $offset"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transactions | TutorFlow Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="sidebar">
    <div class="nav-container">
        <div class="logo"><i class="fas fa-bolt"></i> TutorFlow</div>
        <button class="nav-btn" onclick="window.location.href='admin.php'">
            <i class="fas fa-users"></i> Users
        </button>
        <button class="nav-btn active" onclick="window.location.href='transactions.php'">
            <i class="fas fa-money-bill-wave"></i> Transactions
        </button>
        <button class="nav-btn" disabled>
            <i class="fas fa-book"></i> Courses
        </button>
    </div>

    <a href="logout.php" class="logout-link">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<main class="main-content">
    <div class="content-wrapper">
        <div class="header-flex">
            <h2>Payment Transactions</h2>
            <p style="color:#7e8299; max-width:520px;">
                A consolidated view of all recorded tutor payouts. This is based on the
                <code>earnings</code> table and can be extended later to reflect your full escrow flow.
            </p>
        </div>

        <!-- Summary Cards -->
        <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:25px;">
            <div style="
                flex:1;
                min-width:220px;
                background:#ffffff;
                border-radius:16px;
                padding:18px 22px;
                box-shadow:0 12px 35px rgba(54,153,255,0.10);
                border:1px solid #eff2f5;
                display:flex;
                align-items:center;
                justify-content:space-between;
            ">
                <div>
                    <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#7e8299; font-weight:600;">
                        Total Transactions
                    </div>
                    <div style="font-size:22px; font-weight:700; margin-top:4px;">
                        <?php echo (int)$summary['total_transactions']; ?>
                    </div>
                </div>
                <div style="
                    width:38px; height:38px;
                    border-radius:12px;
                    background:rgba(54,153,255,0.08);
                    display:flex; align-items:center; justify-content:center;
                    color:#3699ff;
                ">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>

            <div style="
                flex:1;
                min-width:220px;
                background:#ffffff;
                border-radius:16px;
                padding:18px 22px;
                box-shadow:0 12px 35px rgba(80,205,137,0.10);
                border:1px solid #eff2f5;
                display:flex;
                align-items:center;
                justify-content:space-between;
            ">
                <div>
                    <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:#7e8299; font-weight:600;">
                        Total Payout Volume
                    </div>
                    <div style="font-size:22px; font-weight:700; margin-top:4px;">
                        $<?php echo number_format((float)$summary['total_amount'], 2); ?>
                    </div>
                </div>
                <div style="
                    width:38px; height:38px;
                    border-radius:12px;
                    background:rgba(80,205,137,0.10);
                    display:flex; align-items:center; justify-content:center;
                    color:#16a34a;
                ">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teacher</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions && mysqli_num_rows($transactions) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($transactions)): ?>
                            <tr>
                                <td>#<?php echo (int)$row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['teacher_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($row['teacher_email'] ?? '-'); ?></td>
                                <td>$<?php echo number_format((float)$row['amount'], 2); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:24px; color:#7e8299;">
                                No transactions recorded yet. Once you start recording payouts in the
                                <code>earnings</code> table, they will appear here.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-top:20px; display:flex; gap:8px; flex-wrap:wrap;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a
                        href="?page=<?php echo $i; ?>"
                        class="page-link <?php echo $i === $page ? 'active' : ''; ?>"
                        style="
                            min-width:34px;
                            height:34px;
                            border-radius:999px;
                            border:1px solid #e4e6ef;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            font-size:13px;
                            text-decoration:none;
                            color:<?php echo $i === $page ? '#ffffff' : '#5e6278'; ?>;
                            background:<?php echo $i === $page ? '#3699ff' : '#ffffff'; ?>;
                            box-shadow:<?php echo $i === $page ? '0 4px 12px rgba(54,153,255,0.35)' : 'none'; ?>;
                        "
                    >
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>

