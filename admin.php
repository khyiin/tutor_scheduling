<?php
session_start();
include 'config.php'; // Ensure this connects to teacher_schedule_db
include 'roles.php';

// Access Control
if (!isset($_SESSION['user_id']) || strcasecmp($_SESSION['role'], Role::ADMIN) !== 0) {
    header("Location: login.php");
    exit();
}

// Pagination settings
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Count total users for pagination
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$totalRow = $countResult ? mysqli_fetch_assoc($countResult) : ['total' => 0];
$totalUsers = (int)$totalRow['total'];
$totalPages = $totalUsers > 0 ? ceil($totalUsers / $perPage) : 1;

// Fetch Users with pagination
$sql = "SELECT id, fullname, email, role, status 
        FROM users 
        ORDER BY fullname ASC 
        LIMIT $perPage OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | TutorFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="sidebar">
    <div class="nav-container">
        <div class="logo"><i class="fas fa-bolt"></i> TutorFlow</div>
        <button class="nav-btn active"><i class="fas fa-chart-line"></i> Dashboard</button>
        <button class="nav-btn"><i class="fas fa-users"></i> Users</button>
        <button class="nav-btn"><i class="fas fa-book"></i> Courses</button>
    </div>

    <a href="logout.php" class="logout-link">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<main class="main-content">
    <div class="container">
        <div class="header-flex">
            <h2>User Management</h2>
        </div>

            <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr id="row_<?= $row['id'] ?>">
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><span class="badge"><?= $row['role'] ?></span></td>
                        <td><span class="status-pill <?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                        <td>
                            <button class="action-btn edit" onclick='openEditModal(<?= json_encode($row) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" onclick="deleteUser(<?= $row['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a 
                    href="?page=<?= $i ?>" 
                    class="page-link <?= $i === $page ? 'active' : '' ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <div class="btn-container">
            <button class="btn-add" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
    </div>
</main>

<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">User Form</h3>
        <form id="userForm">
            <input type="hidden" name="id" id="form_id">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" id="form_fullname" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="form_email" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="form_role">
                    <option value="<?php echo Role::ADMIN; ?>">Admin</option>
                    <option value="<?php echo Role::TEACHER; ?>">Teacher</option>
                    <option value="<?php echo Role::STUDENT; ?>">Student</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="form_status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    $('#userForm')[0].reset();
    $('#form_id').val('');
    $('#modalTitle').text('Add New User');
    $('#userModal').css('display', 'flex');
}

function openEditModal(user) {
    $('#modalTitle').text('Edit User');
    $('#form_id').val(user.id);
    $('#form_fullname').val(user.fullname);
    $('#form_email').val(user.email);
    $('#form_role').val(user.role);
    $('#form_status').val(user.status);
    $('#userModal').css('display', 'flex');
}

function closeModal() { $('#userModal').hide(); }

// AJAX Save (Add/Edit)
$('#userForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'process_user.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            location.reload(); // Simplest way to show changes
        }
    });
});

// AJAX Delete
function deleteUser(id) {
    if(confirm('Are you sure you want to delete this user?')) {
        $.ajax({
            url: 'process_user.php',
            method: 'POST',
            data: { delete_id: id },
            success: function() {
                $('#row_' + id).fadeOut();
            }
        });
    }
}
</script>
</body>
</html>