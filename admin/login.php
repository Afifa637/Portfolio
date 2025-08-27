<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $admin = $res->fetch_assoc();
        if (password_verify($pass, $admin['password'])) {
            $_SESSION['admin'] = $admin['email'];

            // Optional: remember me with cookie
            if (!empty($_POST['remember'])) {
                setcookie("admin_email", $email, time()+60*60*24*30, "/"); // 30 days
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
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
