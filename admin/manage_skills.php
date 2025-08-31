<?php
require "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skill'])) {
    $stmt = $conn->prepare("INSERT INTO skills (skill_name, percentage, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $_POST['skill_name'], $_POST['percentage'], $_POST['description']);
    $stmt->execute();
    header("Location: manage_skills.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_skill'])) {
    $stmt = $conn->prepare("UPDATE skills SET skill_name=?, percentage=?, description=? WHERE id=?");
    $stmt->bind_param("sisi", $_POST['skill_name'], $_POST['percentage'], $_POST['description'], $_POST['id']);
    $stmt->execute();
    header("Location: manage_skills.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM skills WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_skills.php");
    exit;
}

$result = $conn->query("SELECT * FROM skills ORDER BY id DESC");

$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM skills WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Skills</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <h2>Manage Skills</h2>

  <form method="POST" class="row g-2 mb-4">
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

    <div class="col-md-4">
      <input type="text" name="skill_name" value="<?= htmlspecialchars($editData['skill_name'] ?? '') ?>" placeholder="Skill Name" class="form-control" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="percentage" value="<?= htmlspecialchars($editData['percentage'] ?? '') ?>" placeholder="%" class="form-control" min="0" max="100" required>
    </div>
    <div class="col-md-6">
      <input type="text" name="description" value="<?= htmlspecialchars($editData['description'] ?? '') ?>" placeholder="Description" class="form-control">
    </div>

    <div class="col-12">
      <?php if ($editData): ?>
        <button type="submit" name="update_skill" class="btn btn-warning">Update Skill</button>
        <a href="manage_skills.php" class="btn btn-secondary">Cancel</a>
      <?php else: ?>
        <button type="submit" name="add_skill" class="btn btn-primary">Add Skill</button>
      <?php endif; ?>
    </div>
  </form>

  <table class="table table-bordered table-striped">
    <tr><th>ID</th><th>Skill</th><th>Percentage</th><th>Description</th><th>Actions</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['skill_name']) ?></td>
        <td><?= $row['percentage'] ?>%</td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td>
          <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
          <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this skill?');">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
