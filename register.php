<?php
session_start();
include 'config.php';
include 'roles.php';
include 'header.php';

$message = "";

if (isset($_POST['register'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $selectedRole = isset($_POST['role']) ? $_POST['role'] : Role::STUDENT;

    if (!in_array($selectedRole, [Role::TEACHER, Role::STUDENT, Role::ADMIN], true)) {
        $selectedRole = Role::STUDENT;
    }

    // Make new accounts active by default; admin is automatically active too
    $status = 'Active';

    // Optional credential fields for future use
    $studentId   = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
    $studentProg = isset($_POST['student_program']) ? trim($_POST['student_program']) : '';
    $teacherId   = isset($_POST['teacher_id']) ? trim($_POST['teacher_id']) : '';
    $teacherSpec = isset($_POST['teacher_specialization']) ? trim($_POST['teacher_specialization']) : '';

    // Check if email already exists
    $checkEmail = mysqli_query($conn, "SELECT email FROM users WHERE email='$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        $message = "<p style='color:red; text-align:center;'>Email already registered!</p>";
    } else {
        $sql = "INSERT INTO users (fullname, email, password, role, status) 
                VALUES ('$fullname', '$email', '$password', '$selectedRole', '$status')";
        if (mysqli_query($conn, $sql)) {
            $message = "<p style='color:green; text-align:center;'>Registered successfully! <a href='login.php'>Login here</a></p>";
        } else {
            $message = "<p style='color:red; text-align:center;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>

<link rel="stylesheet" href="style.css">

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-image">
            <img src="login.jpg" alt="Register Visual">
        </div>

        <div class="login-form-section">
            <form method="POST">
                <h2>Create your account</h2>
                
                <?php if($message != "") echo "<div style='margin-bottom: 15px;'>$message</div>"; ?>
                
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" placeholder="Enter your full name" required>
                </div>

                <div class="input-group">
                    <label>Register as</label>
                    <select name="role" id="roleSelect" required>
                        <option value="<?php echo Role::STUDENT; ?>">Student</option>
                        <option value="<?php echo Role::TEACHER; ?>">Teacher</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Create a password" required>
                </div>

                <!-- Student-specific credentials -->
                <div class="input-group" id="studentCredentials">
                    <label>Student Credentials</label>
                    <input type="text" name="student_id" placeholder="Student ID (optional)">
                    <input type="text" name="student_program" placeholder="Course / Program (optional)" style="margin-top: 10px;">
                </div>

                <!-- Teacher-specific credentials -->
                <div class="input-group" id="teacherCredentials" style="display:none;">
                    <label>Teacher Credentials</label>
                    <input type="text" name="teacher_id" placeholder="Teacher ID (optional)">
                    <input type="text" name="teacher_specialization" placeholder="Specialization / Department (optional)" style="margin-top: 10px;">
                </div>
                
                <button type="submit" name="register" class="login-btn">Sign Up</button>

                <div class="register-link" style="text-align: center; margin-top: 20px;">
                    <p style="color: #636e72;">Already have an account? <a href="login.php" style="color: #54b4f3; text-decoration: none; font-weight: bold;">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const roleSelect = document.getElementById('roleSelect');
        const studentBlock = document.getElementById('studentCredentials');
        const teacherBlock = document.getElementById('teacherCredentials');

        if (!roleSelect || !studentBlock || !teacherBlock) return;

        function toggleCredentialBlocks() {
            const role = roleSelect.value;
            if (role === '<?php echo Role::TEACHER; ?>') {
                teacherBlock.style.display = 'block';
                studentBlock.style.display = 'none';
            } else {
                teacherBlock.style.display = 'none';
                studentBlock.style.display = 'block';
            }
        }

        roleSelect.addEventListener('change', toggleCredentialBlocks);
        toggleCredentialBlocks();
    })();
</script>

<?php include 'footer.php'; ?>