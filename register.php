<?php
include 'header.php';
include 'config.php';

$message = "";

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (fullname, email, password)
            VALUES ('$fullname', '$email', '$password')";

    if (mysqli_query($conn, $sql)) {
        $message = "<p class='success'>Registered successfully!
                    <a href='login.php'> Login</a></p>";
    } else {
        $message = "<p class='error'>Error: " . mysqli_error($conn) . "</p>";
    }
}

include 'footer.php';
?>

<div class="container">
    <h2>Register</h2>

    <?php echo $message; ?>

    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="register">Sign Up</button>
    </form>
</div>
