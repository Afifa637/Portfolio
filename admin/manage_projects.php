<?php
require "config.php";

$successMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
  $stmt = $conn->prepare("INSERT INTO projects (title, category, image, description, details_title, skills_used, role, view_link, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssssss", $_POST['title'], $_POST['category'], $_POST['image'], $_POST['description'], $_POST['details_title'], $_POST['skills_used'], $_POST['role'], $_POST['view_link'], $_POST['created_at']);
  $stmt->execute();
  $successMsg = "Project added successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project'])) {
  $stmt = $conn->prepare("UPDATE projects SET title=?, category=?, image=?, description=?, details_title=?, skills_used=?, role=?, view_link=?, created_at=? WHERE id=?");
  $stmt->bind_param("sssssssssi", $_POST['title'], $_POST['category'], $_POST['image'], $_POST['description'], $_POST['details_title'], $_POST['skills_used'], $_POST['role'], $_POST['view_link'], $_POST['created_at'], $_POST['id']);
  $stmt->execute();
  $successMsg = "Project updated successfully!";
}

if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM projects WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $successMsg = "Project deleted successfully!";
  header("Location: manage_project.php");
  exit;
}

$result = $conn->query("SELECT * FROM projects ORDER BY id DESC");

$editData = null;
if (isset($_GET['edit'])) {
  $id = (int)$_GET['edit'];
  $stmt = $conn->prepare("SELECT * FROM projects WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $editData = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Manage Projects</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
<?php endif; ?>

<body class="container py-5">
<a href="index.php" class="btn btn-primary">Go Back</a>
  <h2>Manage Projects</h2>

  <form method="POST" class="row g-2 mb-4">
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

    <div class="col-md-3">
      <input type="text" name="title" value="<?= htmlspecialchars($editData['title'] ?? '') ?>" placeholder="Title" class="form-control" required>
    </div>
    <div class="col-md-2">
      <input type="text" name="category" value="<?= htmlspecialchars($editData['category'] ?? '') ?>" placeholder="Category" class="form-control" required>
    </div>
    <div class="col-md-3">
      <input type="text" name="image" value="<?= htmlspecialchars($editData['image'] ?? '') ?>" placeholder="Image URL" class="form-control">
    </div>
    <div class="col-md-4">
      <input type="text" name="details_title" value="<?= htmlspecialchars($editData['details_title'] ?? '') ?>" placeholder="Details Title" class="form-control">
    </div>
    <div class="col-12">
      <textarea name="description" placeholder="Description" class="form-control"><?= htmlspecialchars($editData['description'] ?? '') ?></textarea>
    </div>
    <div class="col-md-3">
      <input type="text" name="skills_used" value="<?= htmlspecialchars($editData['skills_used'] ?? '') ?>" placeholder="Skills Used" class="form-control">
    </div>
    <div class="col-md-3">
      <input type="text" name="role" value="<?= htmlspecialchars($editData['role'] ?? '') ?>" placeholder="Role" class="form-control">
    </div>
    <div class="col-md-3">
      <input type="url" name="view_link" value="<?= htmlspecialchars($editData['view_link'] ?? '') ?>" placeholder="View Link" class="form-control">
    </div>
    <div class="col-md-3">
      <input type="date" name="created_at" value="<?= htmlspecialchars($editData['created_at'] ?? '') ?>" class="form-control">
    </div>

    <div class="col-12">
      <?php if ($editData): ?>
        <button type="submit" name="update_project" class="btn btn-warning">Update Project</button>
        <a href="manage_project.php" class="btn btn-secondary">Cancel</a>
      <?php else: ?>
        <button type="submit" name="add_project" class="btn btn-primary">Add Project</button>
      <?php endif; ?>
    </div>
  </form>

  <table class="table table-bordered table-striped">
    <tr>
      <th>ID</th>
      <th>Title</th>
      <th>Category</th>
      <th>Image</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= htmlspecialchars($row['category']) ?></td>
        <td>
          <?php if (!empty($row['image'])): ?>
            <img src="<?= htmlspecialchars($row['image']) ?>" alt="" width="80">
          <?php endif; ?>
        </td>
        <td>
          <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
          <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?');" class="btn btn-sm btn-danger">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>

</html>