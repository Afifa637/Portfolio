<?php
include "config.php";
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Welcome, <?= $_SESSION['admin']; ?></h1>
        <a href="logout.php" class="btn logout">Logout</a>

        <div class="admin-grid">
            <a href="manage_home.php" class="card">Manage Home</a>
            <a href="manage_about.php" class="card">Manage About</a>
            <a href="manage_education.php" class="card">Manage Education</a>
            <a href="manage_projects.php" class="card">Manage Projects</a>
            <a href="manage_skills.php" class="card">Manage Skills</a>
            <a href="manage_contact.php" class="card">Manage Contact</a>
            <a href="manage_messages.php" class="card">Manage Messages</a>
        </div>
    </div>
</body>
</html>
