<?php 
session_start();
include 'config.php';

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'Active') {
            $error = "Your account is inactive. Contact admin.";
        } else {
            // Save user info to session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['fullname'];
            $_SESSION['role'] = $user['role']; // Usually "Admin", "Teacher", etc.

            // Redirect based on role - Using strcasecmp for safety
            if (strcasecmp($user['role'], 'Admin') == 0) {
                header("Location: admin.php");
                exit();
            } elseif (strcasecmp($user['role'], 'Teacher') == 0) {
                header("Location: dashboard.php");
                exit();
            } elseif (strcasecmp($user['role'], 'Client') == 0) {
                header("Location: client.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        }
    } else {
        $error = "Invalid email or password";
    }
}

// Logic is finished, now we start outputting HTML
include 'header.php'; 
?>

<link rel="stylesheet" href="style.css">

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-image">
            <img src="login.jpg" alt="Login Visual">
        </div>

        <div class="login-form-section">
            <form method="POST">
                <h2>Login</h2>
                <?php if(isset($error)) echo "<p style='color:red; text-align:center; margin-bottom: 15px;'>$error</p>"; ?>
                
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" name="login" class="login-btn">Login</button>

                <div class="register-link" style="text-align: center; margin-top: 20px;">
                    <p style="color: #636e72;">Don't have an account? <a href="register.php" style="color: #54b4f3; text-decoration: none; font-weight: bold;">Register</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>