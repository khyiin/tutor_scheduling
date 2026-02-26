<?php
session_start();
include 'config.php';
include 'roles.php';

if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::ADMIN) !== 0) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Courses</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="sidebar">
    <div class="nav-container">
        <div class="logo"><i class="fas fa-bolt"></i> TutorFlow</div>
        <button class="nav-btn" onclick="window.location.href='admin.php'">
            <i class="fas fa-chart-line"></i> Dashboard
        </button>
        <button class="nav-btn" onclick="window.location.href='admin.php'">
            <i class="fas fa-users"></i> Users
        </button>
        <button class="nav-btn active">
            <i class="fas fa-book"></i> Courses
        </button>
    </div>

    <a href="logout.php" class="logout-link">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<main class="main-content">
    <div class="container">
        <div class="header-flex">
            <h2>Courses (placeholder)</h2>
        </div>
        <div class="table-card">
            <p>
                This page is reserved for future course management. For now, the core admin
                functionality (user management) is available on the main Admin Dashboard.
            </p>
        </div>
    </div>
</main>

</body>
</html>

