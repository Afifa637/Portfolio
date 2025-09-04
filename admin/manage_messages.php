<?php
require "config.php";
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_messages.php");
    exit;
}

$result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$counter = $result->num_rows; // start from total count
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Contact Messages</title>
  <link rel="stylesheet" href="css/admin_messages.css">
</head>
<body>
<div class="admin-container">
    <div class="top-bar">
        <a href="index.php" class="btn back-btn">Go Back</a>
        <h2>Contact Messages</h2>
    </div>

    <?php if ($result->num_rows > 0): ?>
    <table class="messages-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Purpose</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $counter-- ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td class="message-cell"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                <td><?= htmlspecialchars($row['purpose']) ?></td>
                <td><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
                <td>
                    <a href="?delete=<?= $row['id'] ?>" class="btn delete-btn" onclick="return confirm('Delete this message?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="info-box">No messages found yet.</div>
    <?php endif; ?>
</div>
</body>
</html>
