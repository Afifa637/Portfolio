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
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?'); // base file
    header("Location: " . $redirectUrl);
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Projects</title>
    <link rel="stylesheet" href="css/admin_project.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn btn-primary">Go Back</a>
        <h2>Manage Projects</h2>

        <?php if (!empty($successMsg)): ?>
            <div class="alert-success"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-grid">
            <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

            <input type="text" name="title" value="<?= htmlspecialchars($editData['title'] ?? '') ?>" placeholder="Title" required>
            <input type="text" name="category" value="<?= htmlspecialchars($editData['category'] ?? '') ?>" placeholder="Category" required>
            <input type="text" name="image" value="<?= htmlspecialchars($editData['image'] ?? '') ?>" placeholder="Image URL">
            <input type="text" name="details_title" value="<?= htmlspecialchars($editData['details_title'] ?? '') ?>" placeholder="Details Title">
            <textarea name="description" placeholder="Description"><?= htmlspecialchars($editData['description'] ?? '') ?></textarea>
            <input type="text" name="skills_used" value="<?= htmlspecialchars($editData['skills_used'] ?? '') ?>" placeholder="Skills Used">
            <input type="text" name="role" value="<?= htmlspecialchars($editData['role'] ?? '') ?>" placeholder="Role">
            <input type="url" name="view_link" value="<?= htmlspecialchars($editData['view_link'] ?? '') ?>" placeholder="View Link">
            <input type="date" name="created_at" value="<?= htmlspecialchars($editData['created_at'] ?? '') ?>">

            <div class="form-actions">
                <?php if ($editData): ?>
                    <button type="submit" name="update_project" class="btn-warning">Update Project</button>
                    <a href="manage_project.php" class="btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_project" class="btn-primary btn" >Add Project</button>
                <?php endif; ?>
            </div>
        </form>

        <table class="project-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="Project Image" class="project-img">
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?');" class="btn btn-delete">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
