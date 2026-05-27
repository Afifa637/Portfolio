<?php
require "config.php";
require_admin();

if (isset($_POST['add'])) {
  if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    header("Location: manage_footer.php?error=csrf");
    exit;
  }
  $stmt = $conn->prepare("INSERT INTO footer (social_name, social_icon, social_link, footer_text) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $_POST['social_name'], $_POST['social_icon'], $_POST['social_link'], $_POST['footer_text']);
  $stmt->execute();
}

if (isset($_POST['update'])) {
  if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    header("Location: manage_footer.php?error=csrf");
    exit;
  }
  $stmt = $conn->prepare("UPDATE footer SET social_name=?, social_icon=?, social_link=?, footer_text=? WHERE id=?");
  $stmt->bind_param("ssssi", $_POST['social_name'], $_POST['social_icon'], $_POST['social_link'], $_POST['footer_text'], $_POST['id']);
  $stmt->execute();
}

if (isset($_GET['delete'])) {
  if (!csrf_verify($_GET['csrf_token'] ?? '')) {
    header("Location: manage_footer.php?error=csrf");
    exit;
  }
  $stmt = $conn->prepare("DELETE FROM footer WHERE id=?");
  $stmt->bind_param("i", $_GET['delete']);
  $stmt->execute();
}

$result = $conn->query("SELECT * FROM footer");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Manage Footer</title>
  <link rel="stylesheet" href="css/admin_footer.css">
    <link rel="stylesheet" href="css/futuristic_admin.css">
</head>

<body>
  <div class="admin-container">
    <div class="top-bar">
      <a href="index.php" class="btn back-btn">Go Back</a>
      <h2>Manage Footer</h2>
    </div>

    <form method="post" class="footer-form">
      <?= csrf_field(); ?>
      <input type="hidden" name="id" id="id">
      <input type="text" name="social_name" placeholder="Social Name" required>
      <input type="text" name="social_icon" placeholder="FontAwesome Icon (e.g., fab fa-facebook)">
      <input type="url" name="social_link" placeholder="Social Link">
      <input type="text" name="footer_text" placeholder="Footer Text">
      <div class="form-buttons">
        <button type="submit" name="add" class="btn add-btn">Add</button>
        <button type="submit" name="update" class="btn update-btn">Update</button>
      </div>
    </form>

    <table class="footer-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Icon</th>
          <th>Link</th>
          <th>Footer Text</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['social_name']); ?></td>
            <td><i class="<?= htmlspecialchars($row['social_icon']); ?>"></i></td>
            <td><a href="<?= htmlspecialchars($row['social_link']); ?>" target="_blank"><?= htmlspecialchars($row['social_link']); ?></a></td>
            <td><?= htmlspecialchars($row['footer_text']); ?></td>
            <td>
              <a href="?delete=<?= $row['id']; ?>&csrf_token=<?= urlencode(csrf_token()) ?>" class="btn delete-btn" onclick="return confirm('Delete this item?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>

</html>