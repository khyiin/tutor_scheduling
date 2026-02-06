<?php
session_start();
include 'config.php';

// ===== ACCESS CONTROL =====
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ===== FETCH USERS =====
$sql = "SELECT id, fullname, email, role, status FROM users ORDER BY fullname ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | Admin Panel</title>
    <link rel="stylesheet" href="admin.css"> <!-- Your glowy CSS -->
</head>
<body class="dashboard">

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2>Manage Users</h2>
        <a href="add_user.php" class="btn add-btn">+ Add User</a>
    </div>

    <!-- USER TABLE -->
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th width="160">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['role'])) ?></td>
                        <td>
                            <span class="status <?= strtolower($row['status']) ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn small edit">Edit</a>
                            <a href="delete_user.php?id=<?= $row['id'] ?>"
                               class="btn small delete"
                               onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
