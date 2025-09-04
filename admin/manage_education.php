<?php
require "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_edu'])) {
    $stmt = $conn->prepare("INSERT INTO education (degree, major, institution, location, grade, start_year, end_year) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $_POST['degree'], $_POST['major'], $_POST['institution'], $_POST['location'], $_POST['grade'], $_POST['start_year'], $_POST['end_year']);
    $stmt->execute();
    header("Location: manage_education.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_edu'])) {
    $stmt = $conn->prepare("UPDATE education SET degree=?, major=?, institution=?, location=?, grade=?, start_year=?, end_year=? WHERE id=?");
    $stmt->bind_param("sssssssi", $_POST['degree'], $_POST['major'], $_POST['institution'], $_POST['location'], $_POST['grade'], $_POST['start_year'], $_POST['end_year'], $_POST['id']);
    $stmt->execute();
    header("Location: manage_education.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM education WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_education.php");
    exit;
}

$result = $conn->query("SELECT * FROM education ORDER BY start_year DESC");

$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM education WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Education</title>
  <link rel="stylesheet" href="css/admin_edu.css">
</head>
<body>

<div class="admin-container">
  <a href="index.php" class="btn back-btn">Go Back</a>
  <h2>Manage Education</h2>

  <form method="POST" class="education-form">
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
    <div class="form-row">
      <input type="text" name="degree" value="<?= htmlspecialchars($editData['degree'] ?? '') ?>" placeholder="Degree" required>
      <input type="text" name="major" value="<?= htmlspecialchars($editData['major'] ?? '') ?>" placeholder="Major">
      <input type="text" name="institution" value="<?= htmlspecialchars($editData['institution'] ?? '') ?>" placeholder="Institution" required>
      <input type="text" name="location" value="<?= htmlspecialchars($editData['location'] ?? '') ?>" placeholder="Location">
      <input type="text" name="grade" value="<?= htmlspecialchars($editData['grade'] ?? '') ?>" placeholder="Grade">
      <input type="text" name="start_year" value="<?= htmlspecialchars($editData['start_year'] ?? '') ?>" placeholder="Start Year" required>
      <input type="text" name="end_year" value="<?= htmlspecialchars($editData['end_year'] ?? '') ?>" placeholder="End Year" required>
    </div>
    <div class="form-row">
      <?php if ($editData): ?>
        <button type="submit" name="update_edu" class="btn update-btn">Update</button>
        <a href="manage_education.php" class="btn cancel-btn">Cancel</a>
      <?php else: ?>
        <button type="submit" name="add_edu" class="btn add-btn">Add</button>
      <?php endif; ?>
    </div>
  </form>

  <table class="education-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Degree</th>
        <th>Major</th>
        <th>Institution</th>
        <th>Grade</th>
        <th>Years</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['degree']) ?></td>
          <td><?= htmlspecialchars($row['major']) ?></td>
          <td><?= htmlspecialchars($row['institution']) ?> (<?= htmlspecialchars($row['location']) ?>)</td>
          <td><?= htmlspecialchars($row['grade']) ?></td>
          <td><?= htmlspecialchars($row['start_year']) ?> - <?= htmlspecialchars($row['end_year']) ?></td>
          <td>
            <a href="?edit=<?= $row['id'] ?>" class="btn edit-btn">Edit</a>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?');" class="btn delete-btn">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
