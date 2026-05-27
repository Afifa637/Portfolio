<?php
include "config.php";
require_admin();

$counts = [
    'projects' => (int)($conn->query("SELECT COUNT(*) AS total FROM projects")?->fetch_assoc()['total'] ?? 0),
    'skills' => (int)($conn->query("SELECT COUNT(*) AS total FROM skills")?->fetch_assoc()['total'] ?? 0),
    'education' => (int)($conn->query("SELECT COUNT(*) AS total FROM education")?->fetch_assoc()['total'] ?? 0),
    'messages' => (int)($conn->query("SELECT COUNT(*) AS total FROM contact_messages")?->fetch_assoc()['total'] ?? 0),
];

$recentMessages = $conn->query("SELECT name, email, subject, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/futuristic_admin.css">
    <link rel="stylesheet" href="css/admin_home.css">
    <link rel="stylesheet" href="css/admin_about.css">
    <link rel="stylesheet" href="css/admin_contact.css">
    <link rel="stylesheet" href="css/admin_footer.css">
    <link rel="stylesheet" href="css/admin_messages.css">
    <link rel="stylesheet" href="css/admin_skills.css">


</head>

<body>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <h2>Portfolio Admin</h2>
                <p><?= htmlspecialchars($_SESSION['admin']); ?></p>
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="active">Dashboard</a>
                <a href="manage_home.php">Home</a>
                <a href="manage_about.php">About</a>
                <a href="manage_education.php">Education</a>
                <a href="manage_projects.php">Projects</a>
                <a href="manage_skills.php">Skills</a>
                <a href="manage_contact.php">Contact</a>
                <a href="manage_footer.php">Footer</a>
                <a href="manage_messages.php">Messages</a>
            </nav>
            <div class="admin-actions">
                <a href="change_password.php" class="btn">Change Password</a>
                <a href="logout.php" class="btn logout">Logout</a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard Overview</h1>
                <p>Track your portfolio content and recent messages.</p>
            </header>

            <div class="admin-metrics">
                <div class="metric-card">
                    <h3><?= $counts['projects']; ?></h3>
                    <p>Projects</p>
                </div>
                <div class="metric-card">
                    <h3><?= $counts['skills']; ?></h3>
                    <p>Skills</p>
                </div>
                <div class="metric-card">
                    <h3><?= $counts['education']; ?></h3>
                    <p>Education Entries</p>
                </div>
                <div class="metric-card">
                    <h3><?= $counts['messages']; ?></h3>
                    <p>Messages</p>
                </div>
            </div>

            <section class="admin-section">
                <h2>Recent Messages</h2>
                <div class="admin-table">
                    <?php if ($recentMessages && $recentMessages->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($msg = $recentMessages->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($msg['name']); ?></td>
                                        <td><?= htmlspecialchars($msg['email']); ?></td>
                                        <td><?= htmlspecialchars($msg['subject']); ?></td>
                                        <td><?= date("d M Y", strtotime($msg['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="info-box">No recent messages.</div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>

</html>