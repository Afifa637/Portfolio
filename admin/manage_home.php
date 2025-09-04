<?php
require "config.php";
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$home = $conn->query("SELECT * FROM home_info LIMIT 1")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_home'])) {
    $stmt = $conn->prepare("UPDATE home_info 
        SET name=?, subtitle=?, description=?, location=?, email=?, availability=?, profile_image=?, cv_link=? 
        WHERE id=?");
    $stmt->bind_param(
        "ssssssssi",
        $_POST['name'],
        $_POST['subtitle'],
        $_POST['description'],
        $_POST['location'],
        $_POST['email'],
        $_POST['availability'],
        $_POST['profile_image'],
        $_POST['cv_link'],
        $home['id']
    );
    $stmt->execute();
    header("Location: manage_home.php?updated=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_role'])) {
    $stmt = $conn->prepare("INSERT INTO home_roles (role) VALUES (?)");
    $stmt->bind_param("s", $_POST['role']);
    $stmt->execute();
    header("Location: manage_home.php?role_added=1");
    exit;
}

if (isset($_GET['delete_role'])) {
    $stmt = $conn->prepare("DELETE FROM home_roles WHERE id=?");
    $stmt->bind_param("i", $_GET['delete_role']);
    $stmt->execute();
    header("Location: manage_home.php?role_deleted=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_social'])) {
    $stmt = $conn->prepare("INSERT INTO home_socials (platform, url, icon_class) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['platform'], $_POST['url'], $_POST['icon_class']);
    $stmt->execute();
    header("Location: manage_home.php?social_added=1");
    exit;
}

if (isset($_GET['delete_social'])) {
    $stmt = $conn->prepare("DELETE FROM home_socials WHERE id=?");
    $stmt->bind_param("i", $_GET['delete_social']);
    $stmt->execute();
    header("Location: manage_home.php?social_deleted=1");
    exit;
}

$roles = $conn->query("SELECT * FROM home_roles");
$socials = $conn->query("SELECT * FROM home_socials");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Home</title>
    <link rel="stylesheet" href="css/admin_home.css">
</head>
<body>
<div class="admin-container">
    <a href="index.php" class="btn">Go Back</a>

    <h2>Manage Home Section</h2>

    <?php if (isset($_GET['updated'])) echo "<p class='success'>Home updated successfully!</p>"; ?>
    <?php if (isset($_GET['role_added'])) echo "<p class='success'>Role added successfully!</p>"; ?>
    <?php if (isset($_GET['role_deleted'])) echo "<p class='error'>Role deleted!</p>"; ?>
    <?php if (isset($_GET['social_added'])) echo "<p class='success'>Social link added!</p>"; ?>
    <?php if (isset($_GET['social_deleted'])) echo "<p class='error'>Social link deleted!</p>"; ?>

    <form method="POST" class="form-section">
        <input type="hidden" name="update_home" value="1">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($home['name']) ?>">

        <label>Subtitle:</label>
        <input type="text" name="subtitle" value="<?= htmlspecialchars($home['subtitle']) ?>">

        <label>Description:</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($home['description']) ?></textarea>

        <label>Location:</label>
        <input type="text" name="location" value="<?= htmlspecialchars($home['location']) ?>">

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($home['email']) ?>">

        <label>Availability:</label>
        <input type="text" name="availability" value="<?= htmlspecialchars($home['availability']) ?>">

        <label>Profile Image URL:</label>
        <input type="text" name="profile_image" value="<?= htmlspecialchars($home['profile_image']) ?>">

        <label>CV Link:</label>
        <input type="text" name="cv_link" value="<?= htmlspecialchars($home['cv_link']) ?>">

        <button type="submit" class="btn">Update Home</button>
    </form>

    <h3>Manage Roles</h3>
    <ul class="list">
        <?php while($role = $roles->fetch_assoc()): ?>
            <li><?= htmlspecialchars($role['role']) ?> 
                <a href="?delete_role=<?= $role['id'] ?>" class="btn logout">Delete</a>
            </li>
        <?php endwhile; ?>
    </ul>

    <form method="POST" class="form-inline">
        <input type="hidden" name="add_role" value="1">
        <input type="text" name="role" placeholder="New Role" required>
        <button type="submit" class="btn">Add Role</button>
    </form>

    <h3>Manage Social Links</h3>
    <ul class="list">
        <?php while($social = $socials->fetch_assoc()): ?>
            <li><?= htmlspecialchars($social['platform']) ?> - <?= htmlspecialchars($social['url']) ?>
                <a href="?delete_social=<?= $social['id'] ?>" class="btn logout">Delete</a>
            </li>
        <?php endwhile; ?>
    </ul>

    <form method="POST" class="form-section">
        <input type="hidden" name="add_social" value="1">
        <label>Platform:</label>
        <input type="text" name="platform" placeholder="Platform" required>

        <label>URL:</label>
        <input type="text" name="url" placeholder="URL" required>

        <label>Icon Class:</label>
        <input type="text" name="icon_class" placeholder="Icon Class" required>

        <button type="submit" class="btn">Add Social Link</button>
    </form>
</div>
</body>
</html>
