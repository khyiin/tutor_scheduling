<?php
include 'config.php';

// Handle Add or Edit
if (isset($_POST['fullname'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($id)) {
        // EDIT existing user
        $query = "UPDATE users SET fullname='$fullname', email='$email', role='$role', status='$status' WHERE id='$id'";
    } else {
        // ADD new user (Password defaults to 123456)
        $hashed_pass = password_hash("123456", PASSWORD_DEFAULT);
        $query = "INSERT INTO users (fullname, email, role, status, password) VALUES ('$fullname', '$email', '$role', '$status', '$hashed_pass')";
    }
    mysqli_query($conn, $query);
}

// Handle Delete
if (isset($_POST['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['delete_id']);
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
}
?>