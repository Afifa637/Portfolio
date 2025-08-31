<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "config.php";

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $admin = $res->fetch_assoc();

        if (password_verify($pass, $admin['password'])) {
            // Secure session
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['email'];

            // Remember me cookie
            if (!empty($_POST['remember'])) {
                setcookie("admin_email", $email, time() + (60 * 60 * 24 * 30), "/"); // 30 days
            }

            header("Location: index.php");
            exit;
        }
    }
    $error = "Invalid email or password!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h2>Admin Login</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <input type="email" name="email" placeholder="Email"
                   value="<?= isset($_COOKIE['admin_email']) ? htmlspecialchars($_COOKIE['admin_email']) : '' ?>"
                   autocomplete="off" required>

            <!-- Randomized name attribute to prevent browser autofill -->
            <input type="password" name="password" placeholder="Password" autocomplete="new-password" required>

            <label style="display:block; margin:10px 0;">
                <input type="checkbox" name="remember"> Remember Me
            </label>

            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
