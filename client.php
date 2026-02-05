<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userName = htmlspecialchars($_SESSION['name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TutorFlow | Client Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Main styles -->
    <link rel="stylesheet" href="assets/style.css">

    <!-- Client-only styles -->
    <link rel="stylesheet" href="assets/client.css">
</head>

<body class="client">

<div class="app-viewport">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar">

        <div class="sidebar-logo">
            <div class="logo-glow"><i class="fas fa-bolt"></i></div>
            <span>TutorFlow</span>
        </div>

        <nav class="sidebar-nav">
            <a href="client.php" class="nav-link active">
                <i class="fas fa-home"></i> Dashboard
            </a>

            <a href="#" class="nav-link">
                <i class="fas fa-search"></i> Find Tutor
            </a>

            <a href="#" class="nav-link">
                <i class="fas fa-calendar-check"></i> My Sessions
            </a>

            <a href="#" class="nav-link">
                <i class="fas fa-clock"></i> History
            </a>

            <a href="#" class="nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>

    </aside>
    <!-- ================= END SIDEBAR ================= -->


    <!-- ================= MAIN CONTENT ================= -->
    <main class="main-content">

        <!-- HEADER -->
        <header class="dashboard-header">
            <div class="header-info">
                <h1>Welcome back 👋</h1>
                <p>Hi <?php echo $userName; ?>, ready to learn today?</p>
            </div>

            <div class="user-pill">
                <div class="avatar-glow">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
            </div>
        </header>


        <div class="scroll-area">

            <!-- QUICK ACTION BUTTONS -->
            <div class="quick-actions">

                <a href="#" class="quick-btn glow-blue">
                    <i class="fas fa-search"></i>
                    Book Tutor
                </a>

                <a href="#" class="quick-btn glow-purple">
                    <i class="fas fa-calendar-plus"></i>
                    Schedule Session
                </a>

                <a href="#" class="quick-btn glow-green">
                    <i class="fas fa-comments"></i>
                    Messages
                </a>

            </div>


            <!-- STATS CARDS -->
            <div class="stats-grid">

                <div class="stat-card glow-blue">
                    <div class="stat-icon-wrap bg-blue">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <span class="val">3</span>
                        <span class="lbl">Today’s Sessions</span>
                    </div>
                </div>

                <div class="stat-card glow-purple">
                    <div class="stat-icon-wrap bg-purple">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <span class="val">18</span>
                        <span class="lbl">Total Lessons</span>
                    </div>
                </div>

                <div class="stat-card glow-green">
                    <div class="stat-icon-wrap bg-green">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <span class="val">4.9</span>
                        <span class="lbl">Tutor Rating</span>
                    </div>
                </div>

            </div>


            <!-- UPCOMING SESSIONS TABLE -->
            <div class="client-table glass-effect">

                <div class="table-header">
                    <h3><i class="fas fa-clock"></i> Upcoming Sessions</h3>
                    <button class="btn-sm">View All</button>
                </div>

                <div class="table-scroll">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Tutor</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td><strong>John Smith</strong></td>
                                <td><span class="tag-math">Math</span></td>
                                <td>Feb 10</td>
                                <td>10:00 AM</td>
                                <td><span class="status done">Confirmed</span></td>
                            </tr>

                            <tr>
                                <td><strong>Emily Clark</strong></td>
                                <td><span class="tag-eng">English</span></td>
                                <td>Feb 11</td>
                                <td>02:00 PM</td>
                                <td><span class="status wait">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </main>
    <!-- ================= END MAIN ================= -->

</div>

</body>
</html>
