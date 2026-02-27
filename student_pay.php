<?php
session_start();
include 'config.php';
include 'roles.php';

// Only students can pay
if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::STUDENT) !== 0) {
    header("Location: login.php");
    exit();
}

$studentId = (int)$_SESSION['user_id'];
$sessionRequestId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;

if ($sessionRequestId <= 0) {
    header("Location: client_sessions.php");
    exit();
}

// Load session + schedule details
$srQuery = mysqli_query(
    $conn,
    "SELECT sr.id, sr.status, sr.schedule_id,
            s.teacher_id, s.subject, s.day, s.time_start, s.time_end, s.price,
            u.fullname AS tutor_name
     FROM session_requests sr
     INNER JOIN schedules s ON s.id = sr.schedule_id
     INNER JOIN users u ON u.id = s.teacher_id
     WHERE sr.id = $sessionRequestId AND sr.student_id = $studentId"
);

if (!$srQuery || mysqli_num_rows($srQuery) === 0) {
    header("Location: client_sessions.php");
    exit();
}

$session = mysqli_fetch_assoc($srQuery);

// Simple rule: only confirmed sessions can be paid
if ($session['status'] !== 'confirmed') {
    header("Location: client_sessions.php");
    exit();
}

$teacherId = (int)$session['teacher_id'];
$amount = (float)$session['price'];
if ($amount <= 0) {
    $amount = 30.00; // fallback default
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? 'card';

    // Upsert payment row (one per session_request)
    $payCheck = mysqli_query(
        $conn,
        "SELECT id, status FROM payments WHERE session_request_id = $sessionRequestId"
    );

    if ($payCheck && mysqli_num_rows($payCheck) > 0) {
        $existing = mysqli_fetch_assoc($payCheck);
        $paymentId = (int)$existing['id'];
        mysqli_query(
            $conn,
            "UPDATE payments 
             SET status = 'paid', amount = $amount 
             WHERE id = $paymentId"
        );
    } else {
        mysqli_query(
            $conn,
            "INSERT INTO payments (student_id, session_request_id, amount, status)
             VALUES ($studentId, $sessionRequestId, $amount, 'paid')"
        );
    }

    // Record teacher earnings (include references)
    mysqli_query(
        $conn,
        "INSERT INTO earnings (teacher_id, amount, student_id, session_request_id)
         VALUES ($teacherId, $amount, $studentId, $sessionRequestId)"
    );

    header("Location: client_sessions.php?payment=success");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | Pay for Session</title>
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
            <a href="client_sessions.php" class="nav-link active"><i class="fas fa-calendar-check"></i> My Sessions</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-info">
                <h1>Checkout</h1>
                <p>Confirm your payment method to complete this session.</p>
            </div>
        </header>

        <div class="scroll-area">
            <div class="page-card glow-card">
                <h3 style="margin-bottom:0.75rem;"><i class="fas fa-chalkboard-teacher"></i> Session Summary</h3>
                <p style="font-size:0.9rem; color:#0f172a;">
                    <strong><?php echo htmlspecialchars($session['subject']); ?></strong> with
                    <?php echo htmlspecialchars($session['tutor_name']); ?><br>
                    <?php echo htmlspecialchars($session['day']); ?> ·
                    <?php echo htmlspecialchars(substr($session['time_start'], 0, 5)); ?> –
                    <?php echo htmlspecialchars(substr($session['time_end'], 0, 5)); ?>
                </p>
                <p style="margin-top:0.5rem; font-weight:600; font-size:1rem;">
                    Amount due: <span style="color:#16a34a;">$<?php echo number_format($amount, 2); ?></span>
                </p>
            </div>

            <div class="page-card glass-effect">
                <h3 style="margin-bottom:0.75rem;"><i class="fas fa-credit-card"></i> Choose Payment Method</h3>
                <form method="post">
                    <div class="form-group">
                        <label>Payment Options</label>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px,1fr)); gap:0.75rem; font-size:0.85rem;">
                            <label style="display:flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid #e5e7eb; border-radius:10px; cursor:pointer;">
                                <input type="radio" name="payment_method" value="card" checked>
                                <span><i class="fas fa-credit-card"></i> Credit / Debit Card</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid #e5e7eb; border-radius:10px; cursor:pointer;">
                                <input type="radio" name="payment_method" value="paypal">
                                <span><i class="fab fa-paypal"></i> PayPal</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid #e5e7eb; border-radius:10px; cursor:pointer;">
                                <input type="radio" name="payment_method" value="gcash">
                                <span><i class="fas fa-mobile-alt"></i> GCash / E‑Wallet</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; padding:8px 10px; border:1px solid #e5e7eb; border-radius:10px; cursor:pointer;">
                                <input type="radio" name="payment_method" value="cash">
                                <span><i class="fas fa-money-bill-wave"></i> Cash (record only)</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-glow btn-success" style="padding:10px 22px; font-size:0.95rem; border-radius:999px;">
                        <i class="fas fa-lock"></i> Pay $<?php echo number_format($amount, 2); ?> Now
                    </button>
                    <a href="client_sessions.php" style="margin-left:12px; font-size:0.85rem; color:#6b7280; text-decoration:none;">
                        Cancel and go back
                    </a>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>
