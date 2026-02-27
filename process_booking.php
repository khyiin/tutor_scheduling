<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::TEACHER) !== 0) {
    header("Location: login.php");
    exit();
}

$request_id = (int)($_POST['request_id'] ?? $_GET['request_id'] ?? 0);
$action = trim($_POST['action'] ?? $_GET['action'] ?? '');

if (!$request_id || !in_array($action, ['confirm', 'reject'], true)) {
    header("Location: students.php");
    exit();
}

$teacher_id = (int)$_SESSION['user_id'];

// Verify this request belongs to a schedule of this teacher
$check = mysqli_query($conn,
    "SELECT sr.id FROM session_requests sr
     INNER JOIN schedules s ON s.id = sr.schedule_id
     WHERE sr.id = $request_id AND s.teacher_id = $teacher_id"
);
if (!($check && mysqli_fetch_assoc($check))) {
    header("Location: students.php?error=invalid");
    exit();
}

$status = $action === 'confirm' ? 'confirmed' : 'rejected';
mysqli_query($conn, "UPDATE session_requests SET status = '$status', updated_at = NOW() WHERE id = $request_id");

header("Location: students.php?updated=" . $action);
exit();
