<?php
include "config.php";
if (!isset($_SESSION['admin'])) { 
    header("Location: login.php"); 
    exit; 
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $short = $_POST['short_intro'];
    $long  = $_POST['long_intro'];
    $image = $_POST['profile_image'];

    $stmt = $conn->prepare("INSERT INTO about (short_intro, long_intro, profile_image, updated_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $short, $long, $image);
    $stmt->execute();
    header("Location: manage_about.php?created=1");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id    = $_POST['id'];
    $short = $_POST['short_intro'];
    $long  = $_POST['long_intro'];
    $image = $_POST['profile_image'];

    $stmt = $conn->prepare("UPDATE about SET short_intro=?, long_intro=?, profile_image=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sssi", $short, $long, $image, $id);
    $stmt->execute();
    header("Location: manage_about.php?updated=1");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM about WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_about.php?deleted=1");
    exit;
}

$result = $conn->query("SELECT * FROM about ORDER BY updated_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage About</title>
  <link rel="stylesheet" href="css/admin_about.css">
</head>
<body>
  <div class="admin-container">
    <div class="top-actions">
      <a href="index.php" class="btn">Go Back</a>
    </div>

    <h2>Manage About Section</h2>

    <?php
      if (isset($_GET['created'])) echo "<p class='success'>New about entry created!</p>";
      if (isset($_GET['updated'])) echo "<p class='success'>About entry updated!</p>";
      if (isset($_GET['deleted'])) echo "<p class='success'>About entry deleted!</p>";
    ?>

    <h3>Add New About</h3>
    <form method="POST" class="form-section">
      <input type="hidden" name="create" value="1">
      <label>Short Intro:</label>
      <textarea name="short_intro" rows="3" required></textarea>

      <label>Long Intro:</label>
      <textarea name="long_intro" rows="6" required></textarea>

      <label>Profile Image URL:</label>
      <input type="text" name="profile_image" required>

      <button type="submit" class="btn">Create</button>
    </form>

    <hr>

    <h3>Existing About Records</h3>
    <?php while ($about = $result->fetch_assoc()): ?>
      <div class="card">
        <p><strong>ID:</strong> <?= $about['id']; ?></p>
        <p><strong>Short Intro:</strong> <?= htmlspecialchars($about['short_intro']); ?></p>
        <p><strong>Long Intro:</strong> <?= htmlspecialchars($about['long_intro']); ?></p>
        <p><strong>Profile Image:</strong> <img src="<?= htmlspecialchars($about['profile_image']); ?>" alt="Profile" width="80"></p>
        <p><strong>Updated:</strong> <?= $about['updated_at']; ?></p>

        <form method="POST" class="edit-form">
          <input type="hidden" name="update" value="1">
          <input type="hidden" name="id" value="<?= $about['id']; ?>">

          <label>Edit Short Intro:</label>
          <textarea name="short_intro" rows="2"><?= htmlspecialchars($about['short_intro']); ?></textarea>

          <label>Edit Long Intro:</label>
          <textarea name="long_intro" rows="4"><?= htmlspecialchars($about['long_intro']); ?></textarea>

          <label>Edit Profile Image:</label>
          <input type="text" name="profile_image" value="<?= htmlspecialchars($about['profile_image']); ?>">

          <button type="submit" class="btn">Update</button>
          <a href="manage_about.php?delete=<?= $about['id']; ?>" class="btn logout" onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
        </form>
      </div>
    <?php endwhile; ?>
  </div>
</body>
</html>
