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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
<a href="index.php" class="btn btn-primary">Go Back</a>

  <h2>Manage Home Section</h2>
  <?php if (isset($_GET['updated'])) echo "<p class='text-success'>Home updated successfully!</p>"; ?>
  <?php if (isset($_GET['role_added'])) echo "<p class='text-success'>Role added successfully!</p>"; ?>
  <?php if (isset($_GET['role_deleted'])) echo "<p class='text-danger'>Role deleted!</p>"; ?>
  <?php if (isset($_GET['social_added'])) echo "<p class='text-success'>Social link added!</p>"; ?>
  <?php if (isset($_GET['social_deleted'])) echo "<p class='text-danger'>Social link deleted!</p>"; ?>

  <form method="POST" class="row g-3 mb-5">
    <input type="hidden" name="update_home" value="1">
    <div class="col-md-6"><input type="text" name="name" value="<?= htmlspecialchars($home['name']) ?>" placeholder="Name" class="form-control"></div>
    <div class="col-md-6"><input type="text" name="subtitle" value="<?= htmlspecialchars($home['subtitle']) ?>" placeholder="Subtitle" class="form-control"></div>
    <div class="col-12"><textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($home['description']) ?></textarea></div>
    <div class="col-md-4"><input type="text" name="location" value="<?= htmlspecialchars($home['location']) ?>" placeholder="Location" class="form-control"></div>
    <div class="col-md-4"><input type="email" name="email" value="<?= htmlspecialchars($home['email']) ?>" placeholder="Email" class="form-control"></div>
    <div class="col-md-4"><input type="text" name="availability" value="<?= htmlspecialchars($home['availability']) ?>" placeholder="Availability" class="form-control"></div>
    <div class="col-md-6"><input type="text" name="profile_image" value="<?= htmlspecialchars($home['profile_image']) ?>" placeholder="Profile Image URL" class="form-control"></div>
    <div class="col-md-6"><input type="text" name="cv_link" value="<?= htmlspecialchars($home['cv_link']) ?>" placeholder="CV Link" class="form-control"></div>
    <div class="col-12"><button type="submit" class="btn btn-success">Update Home</button></div>
  </form>

  <h3>Manage Roles</h3>
  <ul class="list-group mb-3">
    <?php while($role = $roles->fetch_assoc()): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= htmlspecialchars($role['role']) ?>
        <a href="?delete_role=<?= $role['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
      </li>
    <?php endwhile; ?>
  </ul>
  <form method="POST" class="d-flex mb-5">
    <input type="hidden" name="add_role" value="1">
    <input type="text" name="role" placeholder="New Role" class="form-control me-2" required>
    <button type="submit" class="btn btn-primary">Add</button>
  </form>

  <h3>Manage Social Links</h3>
  <ul class="list-group mb-3">
    <?php while($social = $socials->fetch_assoc()): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= htmlspecialchars($social['platform']) ?> - <?= htmlspecialchars($social['url']) ?>
        <a href="?delete_social=<?= $social['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
      </li>
    <?php endwhile; ?>
  </ul>
  <form method="POST" class="row g-2">
    <input type="hidden" name="add_social" value="1">
    <div class="col-md-3"><input type="text" name="platform" placeholder="Platform" class="form-control" required></div>
    <div class="col-md-5"><input type="text" name="url" placeholder="URL" class="form-control" required></div>
    <div class="col-md-3"><input type="text" name="icon_class" placeholder="Icon Class" class="form-control" required></div>
    <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Add</button></div>
  </form>

</body>
</html>
