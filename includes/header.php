<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Energy Tracker</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="topbar">
    <a href="index.php" class="logo">
        <span class="brand-mark">⚡</span>
        Smart Energy Tracker
    </a>
    <nav class="topnav">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="tips.php">Energy Tips</a></li>
            <?php if (!empty($_SESSION['user_id'])) : ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else : ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn secondary-btn">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>