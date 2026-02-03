<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance and Leave Management</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php if (isset($_SESSION['user'])):?>
    <header>
        <div class="container">
            <h1>Employee</h1>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="attendance.php" class="nav-link">Attendance</a>
                <a href="leave.php" class="nav-link">Leave</a>
                <?php if ($_SESSION['user']['role'] == 'admin'): ?>
                    <a href="employees.php" class="nav-link">Employees</a>
                <?php endif; ?>
                <a href="logout.php" class="btn" id="logout-btn">Logout</a>

            </nav>
        </div>
    </header>
    <?php endif; ?>
    <main>
        <div class="container">