<?php
include "config.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldPass = $_POST['old_password'];
    $newPass = $_POST['new_password'];
    $email   = $_SESSION['admin'];

    $sql = "SELECT password FROM admins WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $admin = $res->fetch_assoc();

    if (password_verify($oldPass, $admin['password'])) {
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);

        $sql = "UPDATE admins SET password=? WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $newHash, $email);
        $stmt->execute();

        $msg = "Password changed successfully!";
    } else {
        $msg = "Old password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Change Password</h2>
    <?php if (!empty($msg)): ?>
        <p><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="old_password" placeholder="Old Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <button type="submit" class="btn">Update Password</button>
    </form>
    <p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>
