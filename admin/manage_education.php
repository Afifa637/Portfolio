<?php
require "config.php";

// Add new
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_edu'])) {
    $stmt = $conn->prepare("INSERT INTO education (degree, major, institution, location, grade, start_year, end_year) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $_POST['degree'], $_POST['major'], $_POST['institution'], $_POST['location'], $_POST['grade'], $_POST['start_year'], $_POST['end_year']);
    $stmt->execute();
    header("Location: manage_education.php");
    exit;
}

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_edu'])) {
    $stmt = $conn->prepare("UPDATE education SET degree=?, major=?, institution=?, location=?, grade=?, start_year=?, end_year=? WHERE id=?");
    $stmt->bind_param("sssssssi", $_POST['degree'], $_POST['major'], $_POST['institution'], $_POST['location'], $_POST['grade'], $_POST['start_year'], $_POST['end_year'], $_POST['id']);
    $stmt->execute();
    header("Location: manage_education.php");
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM education WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_education.php");
    exit;
}

// Fetch all
$result = $conn->query("SELECT * FROM education ORDER BY start_year DESC");

// If editing, fetch record
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <h2>Manage Education</h2>

  <!-- Add / Edit Form -->
  <form method="POST" class="mb-4 row g-2">
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
    <div class="col"><input type="text" name="degree" value="<?= htmlspecialchars($editData['degree'] ?? '') ?>" placeholder="Degree" class="form-control" required></div>
    <div class="col"><input type="text" name="major" value="<?= htmlspecialchars($editData['major'] ?? '') ?>" placeholder="Major" class="form-control"></div>
    <div class="col"><input type="text" name="institution" value="<?= htmlspecialchars($editData['institution'] ?? '') ?>" placeholder="Institution" class="form-control" required></div>
    <div class="col"><input type="text" name="location" value="<?= htmlspecialchars($editData['location'] ?? '') ?>" placeholder="Location" class="form-control"></div>
    <div class="col"><input type="text" name="grade" value="<?= htmlspecialchars($editData['grade'] ?? '') ?>" placeholder="Grade" class="form-control"></div>
    <div class="col"><input type="text" name="start_year" value="<?= htmlspecialchars($editData['start_year'] ?? '') ?>" placeholder="Start Year" class="form-control" required></div>
    <div class="col"><input type="text" name="end_year" value="<?= htmlspecialchars($editData['end_year'] ?? '') ?>" placeholder="End Year" class="form-control" required></div>
    <div class="col">
      <?php if ($editData): ?>
        <button type="submit" name="update_edu" class="btn btn-warning">Update</button>
        <a href="manage_education.php" class="btn btn-secondary">Cancel</a>
      <?php else: ?>
        <button type="submit" name="add_edu" class="btn btn-primary">Add</button>
      <?php endif; ?>
    </div>
  </form>

  <!-- Education List -->
  <table class="table table-bordered table-striped">
    <tr>
      <th>ID</th><th>Degree</th><th>Major</th><th>Institution</th><th>Grade</th><th>Years</th><th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['degree']) ?></td>
        <td><?= htmlspecialchars($row['major']) ?></td>
        <td><?= htmlspecialchars($row['institution']) ?> (<?= htmlspecialchars($row['location']) ?>)</td>
        <td><?= htmlspecialchars($row['grade']) ?></td>
        <td><?= htmlspecialchars($row['start_year']) ?> - <?= htmlspecialchars($row['end_year']) ?></td>
        <td>
          <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
          <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?');" class="btn btn-sm btn-danger">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
