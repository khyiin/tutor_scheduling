<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "teacher_schedule_db";

// Database connection
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// --- Data retention & housekeeping (runs cheaply on each request) ---

// Ensure audit columns exist for retention logic (safe to run repeatedly)
@mysqli_query(
    $conn,
    "ALTER TABLE users 
     ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"
);

@mysqli_query(
    $conn,
    "ALTER TABLE schedules 
     ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"
);

// Automatically delete data older than 3 years
@mysqli_query(
    $conn,
    "DELETE FROM users 
     WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 YEAR)"
);

@mysqli_query(
    $conn,
    "DELETE FROM schedules 
     WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 YEAR)"
);

?>

