<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "teacher_schedules_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Ensure columns exist in schedules
@mysqli_query($conn, "ALTER TABLE schedules ADD COLUMN IF NOT EXISTS start_time DATETIME NULL");
@mysqli_query($conn, "ALTER TABLE schedules ADD COLUMN IF NOT EXISTS end_time DATETIME NULL");
@mysqli_query($conn, "ALTER TABLE schedules ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

// Ensure users table has created_at
@mysqli_query($conn, "ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

// CREATE session_requests table
@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS session_requests (
      id int(11) NOT NULL AUTO_INCREMENT,
      student_id int(11) NOT NULL,
      schedule_id int(11) NOT NULL,
      status enum('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
      created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// FIX: CREATE earnings table (This stops the "Table doesn't exist" error)
@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS earnings (
      id int(11) NOT NULL AUTO_INCREMENT,
      teacher_id int(11) NOT NULL,
      amount decimal(10,2) NOT NULL,
      created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
?>